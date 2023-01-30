<?php

namespace Gr4vy\Magento\Plugin\Magento\Checkout\Api;

use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Exception;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class PaymentInformationResetOrderId
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * PaymentInformationResetOrderId constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param Gr4vyLogger $gr4vyLogger
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Gr4vyLogger $gr4vyLogger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->gr4vyLogger = $gr4vyLogger;
    }

    /**
     * @param PaymentInformationManagementInterface $subject
     * @param $cartId
     * @return null
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        $cartId
    ) {
        try {
            $quote = $this->quoteRepository->get($cartId);
            if ($quote->getPayment()->getMethod() == 'gr4vy') {
                $quote->setReservedOrderId(null);
            }
        } catch (Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
        return null;
    }
}
