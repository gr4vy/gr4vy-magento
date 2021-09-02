<?php
namespace Gr4vy\Payment\Model\Payment;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Cart;
use Gr4vy\Payment\Model\Client\Embed as Gr4vyEmbed;
use Gr4vy\Payment\Helper\Data as Gr4vyHelper;

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
     * @var Gr4vyEmbed
     */
    protected $embedApi;

    /**
     * @param CurrentCustomer $currentCustomer
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        UrlInterface $urlBuilder,
        Cart $cart,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyEmbed $embedApi
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->urlBuilder = $urlBuilder;
        $this->cart = $cart;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->embedApi = $embedApi;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $quote_total = $this->cart->getQuote()->getGrandTotal();

        if ($shipping_address = $this->getShippingAddress()) {
            $quote_total += $shipping_address->getShippingAmount();
        }

        $currency = $this->cart->getQuote()->getStore()->getCurrentCurrency()->getCode();
        $buyer_id = $this->cart->getQuote()->getData('gr4vy_buyer_id');
        $config = [
            'payment' => [
                'gr4vy' => [
                    'method' => 'Gr4vy Payment',
                    'gr4vy_id' => $this->gr4vyHelper->getGr4vyId(),
                    'environment' => $this->gr4vyHelper->getGr4vyEnvironment(),
                    'buyer_id' => $buyer_id,
                    'description' => $this->gr4vyHelper->getPaymentInstructions(),
                    'token' => $this->embedApi->getEmbedToken($quote_total, $currency, $buyer_id),
                    'intent' => $this->gr4vyHelper->getGr4vyIntent(),
                    'isActive' => $this->gr4vyHelper->isEnabled()
                ]
            ]
        ];

        return $config;
    }

    /**
     * retrieve shipping_address for current quote, return null if there is no shipping address (virtual or downloadable products)
     *
     * @return Magento\Quote\Model\Quote\Address|null
     */
    public function getShippingAddress()
    {
        if ($this->cart->getQuote()->getShippingAddress()) {
            return $this->cart->getQuote()->getShippingAddress();
        }

        return null;
    }
}
