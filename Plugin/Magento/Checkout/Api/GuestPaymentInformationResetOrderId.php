<?php
namespace Gr4vy\Magento\Plugin\Magento\Checkout\Api;

use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Exception;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestPaymentInformationResetOrderId
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
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * GuestPaymentInformationResetOrderId constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param Gr4vyLogger $gr4vyLogger
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Gr4vyLogger $gr4vyLogger,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param GuestPaymentInformationManagementInterface $subject
     * @param $cartId
     * @return null
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
        $cartId
    ) {
        try {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $quoteId = $quoteIdMask->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            if ($quote->getPayment()->getMethod() == 'gr4vy') {
                $quote->setReservedOrderId(null);
            }
        } catch (Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
        return null;
    }
}
