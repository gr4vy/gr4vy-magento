<?php
namespace Gr4vy\Magento\Model\Payment;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Locale\Resolver;
use Gr4vy\Magento\Model\Client\Embed as Gr4vyEmbed;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Customer as CustomerHelper;

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
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var Gr4vyEmbed
     */
    protected $embedApi;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @param CurrentCustomer $currentCustomer
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        UrlInterface $urlBuilder,
        Cart $cart,
        Gr4vyLogger $gr4vyLogger,
        Gr4vyHelper $gr4vyHelper,
        CustomerHelper $customerHelper,
        Gr4vyEmbed $embedApi,
        CategoryRepositoryInterface $categoryRepository,
        Resolver $resolver
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->urlBuilder = $urlBuilder;
        $this->cart = $cart;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->gr4vyHelper = $gr4vyHelper;
        $this->customerHelper = $customerHelper;
        $this->embedApi = $embedApi;
        $this->categoryRepository = $categoryRepository;
        $this->resolver = $resolver;
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

        $quote = $this->cart->getQuote();
        $quote_total = $this->roundNumber($quote->getGrandTotal());
        $currency = $quote->getStore()->getCurrentCurrency()->getCode();
        if (!$quote->getData('gr4vy_buyer_id')) {
            $this->customerHelper->connectQuoteWithGr4vy($quote);
            $quote->save();
        }
        else {
            $this->customerHelper->updateGr4vyBuyerAddressFromQuote($quote);
        }
        $token = $this->embedApi->getEmbedToken($quote_total, $currency, $buyer_id);
        $buyer_id = $quote->getData('gr4vy_buyer_id');
        // NOTE: $quote->getGrandTotal after shipping method specified contains calculated shipping amount
        $this->gr4vyLogger->logMixed([$token], "Embed Token");

        $config = [
            'payment' => [
                'gr4vy' => [
                    'method' => __('Gr4vy Payment'),
                    'is_enabled' => $this->gr4vyHelper->isEnabled(),
                    'gr4vy_id' => $this->gr4vyHelper->getGr4vyId(),
                    'environment' => $this->gr4vyHelper->getGr4vyEnvironment(),
                    'buyer_id' => $buyer_id,
                    'store' => $store,
                    'external_identifier' => $external_identifier,
                    'description' => $this->gr4vyHelper->getPaymentInstructions(),
                    'title' => $this->gr4vyHelper->getPaymentTitle(),
                    'intent' => $this->gr4vyHelper->getGr4vyIntent(),
                    'isActive' => $this->gr4vyHelper->isEnabled(),
                    'custom_data' => $this->gr4vyHelper->getGr4vyCustomData(),
                    'payment_source' => $this->gr4vyHelper->getPaymentSource(),
                    'require_security_code' => boolval($this->gr4vyHelper->getRequireSecurityCode()),
                    'theme' => $this->gr4vyHelper->buildThemeConfig(),
                    'statement_descriptor' => $this->gr4vyHelper->buildStatementDescriptor(),
                    'token' => $token,
                    'total_amount' => $quote_total,
                    'items' => $this->getCartItemsData($quote, $quote_total),
                    'locale' => $this->getLocaleCode(),
                    'reload_config_url' => $this->urlBuilder->getUrl('gr4vy/checkout/config')
                ]
            ]
        ];
        $this->gr4vyLogger->logMixed($config);

        return $config;
    }

    /**
     * multiply by 100 and round input number
     *
     * @param float
     * @return integer
     */
    public function roundNumber($input)
    {
        return intval(round(floatval($input) * 100));
    }

    /**
     * NOTE: allowed product types are
     * 'physical', 'discount', 'shipping_fee', 'sales_tax', 'digital', 'gift_card', 'store_credit', 'surcharge'
     *
     * @param Magento\Quote\Model\Quote
     * @param integer
     * @return Array
     */
    public function getCartItemsData($quote, $totalAmount)
    {
        $items = [];
        $itemsTotal = 0;
        foreach ($quote->getAllVisibleItems() as $item){
            $product = $item->getProduct();
            $categories = $product->getCategoryIds();

            $gr4vyCategories = [];
            foreach ($categories as $categoryId) {
                $category = $this->categoryRepository->get($categoryId, $quote->getStore()->getId());
                if ($category) {
                    $gr4vyCategories[] = $category->getName();    
                }
            }
            
            $productUrl = $product->getUrlModel()->getUrl($product);
            $itemAmount = $this->roundNumber($item->getPriceInclTax());
            $itemsTotal += $itemAmount;
            $items[] = [
                'name' => $item->getName(),
                'quantity' => $item->getQty(),
                'unitAmount' => $itemAmount,
                'sku' => $item->getSku(),
                'productUrl' => $productUrl,
                'productType' => 'physical',
                'categories' => $gr4vyCategories
            ];
        }

        // calculate shipping fee as cart item
        $shippingAddress = $quote->getShippingAddress();
        $shippingAmount = $this->roundNumber($shippingAddress->getShippingInclTax());
        $itemsTotal += $shippingAmount;
        $items[] = [
            'name' => $shippingAddress->getShippingMethod() ?? 'n/a',
            'quantity' => 1,
            'unitAmount' => $shippingAmount,
            'sku' => $shippingAddress->getShippingMethod() ?? 'n/a',
            'productUrl' => $quote->getStore()->getUrl(),
            'productType' => 'shipping_fee',
            'categories' => ['shipping']
        ];

        if ($totalAmount != $itemsTotal) {
            return [];
        }

        return $items;
    }

    /**
     * Get the current locale code
     *
     * @return string
     */
    public function getLocaleCode(): string
    {
        $result = $this->resolver->getLocale();
        if ($result === null) {
            return '';
        }
        $parts = explode('_', $result);
        $result = $parts[0].'-'.strtoupper($parts[1]);

        return $result;
    }
}
