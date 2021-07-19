<?php
namespace Gr4vy\Payment\Model\Payment;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'gr4vy' => [
                    'method' => 'Gr4vy Payment',
                    'description' => $this->scopeConfig->getValue('payment/gr4vy/instructions'),
                    'isActive' => $this->scopeConfig->getValue('payment/gr4vy/active')
                ]
            ]
        ];

        return $config;
    }
}
