<?php

declare(strict_types=1);

namespace Gr4vy\Magento\Controller\Webhook;

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

/**
 * Gr4vy Payment Webhook Order Controller
 *
 * @package Gr4vy\Magento\Controller\Decision
 */
class Order extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
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
     * @var Gr4vyLogger
     */
    private $gr4vyLogger;

    public function __construct(
        Context $context,
        Gr4vyTransaction $transactionApi,
        TransactionRepositoryInterface $transactionRepositoryInterface,
        \Gr4vy\Magento\Helper\Order $gr4vyOrder,
        Gr4vyLogger $gr4vyLogger
    ) {
        parent::__construct($context);

        $this->transactionApi = $transactionApi;
        $this->transactionRepository = $transactionRepositoryInterface;
        $this->gr4vyOrder = $gr4vyOrder;
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

        if (!property_exists($request, 'resource')
            || !property_exists($request->resource, 'type')
            || $request->resource->type !== 'transaction') {
            $this->gr4vyLogger->logMixed(
                ['request' => $requestBody],
                __('A Webhook request was received with an invalid entity type of "%1"', $request->type)
            );

            $result->setHttpResponseCode(422);
            return $result;
        }


        try {
            $gr4vy_transaction_id = $request->resource->id;
            $statuses = $this->gr4vyOrder->getGr4vyTransactionStatuses();
            if ($gr4vy_status = $this->transactionApi->getStatus($gr4vy_transaction_id)) {
                $dataModel = $this->transactionRepository->getByGr4vyTransactionId($gr4vy_transaction_id);

                if (in_array($gr4vy_status, $statuses['cancel'])) {
                    $this->gr4vyOrder->cancelOrderByGr4vyTransactionId($dataModel->getGr4vyTransactionId());
                }

                if (in_array($gr4vy_status, $statuses['success'])
                    || in_array($gr4vy_status, $statuses['refund'])
                ) {
                    // ignore refunded or succeeded orders
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
