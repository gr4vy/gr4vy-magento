<?php

declare(strict_types=1);

namespace Gr4vy\Magento\Controller\Webhook;

use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Gr4vy\Magento\Model\Client\Transaction as Gr4vyTransaction;
use Gr4vy\Magento\Api\TransactionRepositoryInterface;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Api\Data\TransactionInterface;

/**
 * Gr4vy Payment Webhook Order Controller
 *
 * @package Gr4vy\Magento\Controller\Decision
 */
class Order extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * Order statuses
     */
    const ORDER_STATUS_PENDING = 'pending';
    const ORDER_STATUS_PROCESSING = 'processing';

    /**
     * @var Gr4vyTransaction
     */
    public $transactionApi;

    /**
     * @var TransactionRepositoryInterface
     */
    public $transactionRepository;

    /**
     * @var \Gr4vy\Magento\Helper\Order
     */
    public $gr4vyOrder;

    /**
     * @var Gr4vyHelper
     */
    private $gr4vyHelper;

    /**
     * @var Gr4vyLogger
     */
    private $gr4vyLogger;

    /**
     * @var TransactionInterface
     */
    private $transactionInterface;

    public function __construct(
        Context $context,
        Gr4vyTransaction $transactionApi,
        TransactionRepositoryInterface $transactionRepositoryInterface,
        TransactionInterface $transactionInterface,
        \Gr4vy\Magento\Helper\Order $gr4vyOrder,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyLogger $gr4vyLogger
    ) {
        parent::__construct($context);

        $this->transactionApi = $transactionApi;
        $this->transactionRepository = $transactionRepositoryInterface;
        $this->transactionInterface = $transactionInterface;
        $this->gr4vyOrder = $gr4vyOrder;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\ResultInterface $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $requestBody = $this->getRequest()->getContent();
        $request = json_decode($requestBody);

        // validate request body
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->gr4vyLogger->logMixed(
                ['request' => $requestBody],
                __('A Webhook request was received with a malformed body. Error: %1', json_last_error_msg())
            );

            $result->setHttpResponseCode(400);
            return $result;
        }

        if (!property_exists($request, 'type')
            || $request->type !== 'event') {
            $this->gr4vyLogger->logMixed(
                ['request' => $requestBody],
                __('A Webhook request was received with an invalid entity type of "%1"', $request->type)
            );

            $result->setHttpResponseCode(422);
            return $result;
        }

        if (!property_exists($request, 'target')
            || !property_exists($request->target, 'type')
            || $request->target->type !== 'transaction') {
            $this->gr4vyLogger->logMixed(
                ['request' => $requestBody],
                __('A Webhook request was received with an invalid entity target type of "%1"', $request->target->type)
            );

            $result->setHttpResponseCode(422);
            return $result;
        }


        try {
            $gr4vy_transaction_id = $request->target->id;
            $statuses = $this->gr4vyOrder->getGr4vyTransactionStatuses();

            $transactionDetail = $this->transactionApi->getTransactionDetail($gr4vy_transaction_id);

            if ($gr4vy_status = $transactionDetail["status"]) {

                $dataModel = $this->transactionRepository->getByGr4vyTransactionId($gr4vy_transaction_id);

                if (!isset($dataModel)) {

                    $externalIdentifier = $transactionDetail["external_identifier"];

                    $order = $this->transactionRepository->getOrderByIncrementId($externalIdentifier);
                    if ($order) {
                        $this->transactionRepository->updateOrderPayment($order, $gr4vy_transaction_id);
                        $this->transactionInterface->setGr4vyTransactionId($gr4vy_transaction_id);
                        $this->transactionInterface->setMethodId($transactionDetail["payment_method"]["id"]);
                        $this->transactionInterface->setBuyerId($transactionDetail["buyer"]["id"]);
                        $this->transactionInterface->setServiceId($transactionDetail["payment_service"]["id"]);
                        $this->transactionInterface->setStatus($transactionDetail["status"]);
                        $this->transactionInterface->setAmount($transactionDetail["amount"]);
                        $this->transactionInterface->setCapturedAmount($transactionDetail["captured_amount"]);
                        $this->transactionInterface->setRefundedAmount($transactionDetail["refunded_amount"]);
                        $this->transactionInterface->setCurrency($transactionDetail["currency"]);
                        $this->transactionRepository->save($this->transactionInterface);
                    }

                    else {
                        $this->gr4vyLogger->logMixed(
                            [],
                            __('Cannot find transaction "%1"', $gr4vy_transaction_id)
                        );
                        return $result;
                    }
                    $dataModel = $this->transactionRepository->getByGr4vyTransactionId($gr4vy_transaction_id);
                }

                    if (in_array($gr4vy_status, $statuses['cancel'])) {
                        $this->gr4vyOrder->cancelOrderByGr4vyTransactionId($dataModel->getGr4vyTransactionId());
                    }


                if (in_array($gr4vy_status, $statuses['success'])
                    || in_array($gr4vy_status, $statuses['refund'])
                ) {
                    // ignore refunded or succeeded orders
                }

                $order = $this->gr4vyOrder->getOrderByGr4vyTransactionId($gr4vy_transaction_id);
                /** @var \Magento\Sales\Model\Order $order */
                if ($order) {
                    $orderStatus = $order->getStatus();
                    if ($gr4vy_status == 'authorization_succeeded') {
                        if ($orderStatus !== self::ORDER_STATUS_PENDING) {
                            $this->gr4vyOrder->updateOrderStatus($order, self::ORDER_STATUS_PENDING);
                        }
                    }
                    elseif ($gr4vy_status == 'capture_succeeded') {
                        if (!$order->hasInvoices()){
                            $this->gr4vyOrder->generatePaidInvoice($order, $gr4vy_transaction_id);
                        }
                        if ($orderStatus !== self::ORDER_STATUS_PROCESSING) {
                            $msg = __(
                                "Captured amount of %1 online. Transaction ID: '%2'.",
                                $this->gr4vyHelper->formatCurrency($dataModel->getAmount()/100),
                                strval($dataModel->getGr4vyTransactionId())
                            );
                            $this->gr4vyOrder->updateOrderStatus($order, self::ORDER_STATUS_PROCESSING);
                            $this->gr4vyOrder->updateOrderHistoryData(
                                $order->getEntityId(),
                            self::ORDER_STATUS_PROCESSING,
                                $msg,
                            true);
                        }
                    }
                }

                $dataModel->setStatus($gr4vy_status);
                $this->transactionRepository->save($dataModel);
            }
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }

        $result->setHttpResponseCode(200);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
