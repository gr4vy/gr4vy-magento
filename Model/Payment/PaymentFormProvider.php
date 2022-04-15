<?php
namespace Gr4vy\Magento\Model\Payment;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Cart;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;

/**
 * Class BillingAgreementConfigProvider
 */
class PaymentFormProvider implements ConfigProviderInterface
{
    const CODE = 'gr4vy';
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @param CurrentCustomer $currentCustomer
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        UrlInterface $urlBuilder,
        Cart $cart,
        Gr4vyHelper $gr4vyHelper
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->urlBuilder = $urlBuilder;
        $this->cart = $cart;
        $this->gr4vyHelper = $gr4vyHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $buyer_id = $this->cart->getQuote()->getData('gr4vy_buyer_id');
        $external_identifier = $this->cart->getQuote()->getData('entity_id');
        $store = $this->gr4vyHelper->getGr4vyPaymentStore() === \Gr4vy\Magento\Model\Payment\Gr4vy::PAYMENT_STORE_ASK
            ? $this->gr4vyHelper->getGr4vyPaymentStore()
            : boolval($this->gr4vyHelper->getGr4vyPaymentStore());

        $config = [
            'payment' => [
                'gr4vy' => [
                    'method' => __('Gr4vy Payment'),
                    'gr4vy_id' => $this->gr4vyHelper->getGr4vyId(),
                    'environment' => $this->gr4vyHelper->getGr4vyEnvironment(),
                    'buyer_id' => $buyer_id,
                    'store' => $store,
                    'external_identifier' => $external_identifier,
                    'description' => $this->gr4vyHelper->getPaymentInstructions(),
                    'intent' => $this->gr4vyHelper->getGr4vyIntent(),
                    'isActive' => $this->gr4vyHelper->isEnabled(),
                    'custom_data' => $this->gr4vyHelper->getGr4vyCustomData()
                ]
            ]
        ];

        return $config;
    }
}
