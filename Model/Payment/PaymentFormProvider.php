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
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    private $storemanager;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param UrlInterface $urlBuilder
     * @param Cart $cart
     * @param Gr4vyLogger $gr4vyLogger
     * @param Gr4vyHelper $gr4vyHelper
     * @param CustomerHelper $customerHelper
     * @param Gr4vyEmbed $embedApi
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Resolver $resolver
     * @param StoreManagerInterface $storemanager
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
        Resolver $resolver,
        StoreManagerInterface $storemanager
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
        $this->storemanager = $storemanager;
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
        $cartItems = $this->getCartItemsData($quote, $quote_total);
        $customData = $this->gr4vyHelper->getGr4vyCustomData();
        $token = $this->embedApi->getEmbedToken($quote_total, $currency, $buyer_id, $cartItems, $customData);
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
                    'custom_data' => $customData,
                    'payment_source' => $this->gr4vyHelper->getPaymentSource(),
                    'require_security_code' => boolval($this->gr4vyHelper->getRequireSecurityCode()),
                    'theme' => $this->gr4vyHelper->buildThemeConfig(),
                    'statement_descriptor' => $this->gr4vyHelper->buildStatementDescriptor(),
                    'token' => $token,
                    'total_amount' => $quote_total,
                    'items' => $cartItems,
                    'locale' => $this->getLocaleCode(),
                    'reload_config_url' => $this->urlBuilder->getUrl('gr4vy/checkout/config'),
                    'rendered' => false
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
        $store = $this->storemanager->getStore();
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
            $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product' .$product->getSmallImage();
            $itemAmount = $this->roundNumber($item->getPrice());
            $itemTaxAmount = $this->roundNumber($item->getTaxAmount());

            $itemsTotal += $itemAmount * $item->getQty();
            $items[] = [
                'name' => $item->getName(),
                'quantity' => $item->getQty(),
                'unit_amount' => $itemAmount,
                'tax_amount'=> $itemTaxAmount,
                'sku' => $item->getSku(),
                'product_url' => $productUrl,
                'image_url' => $productImageUrl,
                'product_type' => 'physical',
                'categories' => $gr4vyCategories
            ];
        }
        // calculate shipping fee as cart item
        $shippingAddress = $quote->getShippingAddress();
        $shippingAmount = $this->roundNumber($shippingAddress->getBaseShippingAmount());
        $discountAmount = $this->roundNumber($shippingAddress->getBaseDiscountAmount());
        $taxAmount = $this->roundNumber($shippingAddress->getTaxAmount());
        $baseShippingTaxAmount = $this->roundNumber($shippingAddress->getBaseShippingTaxAmount());

        $itemsTotal += $shippingAmount + $discountAmount + $taxAmount;
        $items[] = [
            'name' => $shippingAddress->getShippingMethod() ?? 'n/a',
            'quantity' => 1,
            'unit_amount' => $shippingAmount,
            'tax_amount' => $baseShippingTaxAmount,
            'sku' => $shippingAddress->getShippingMethod() ?? 'n/a',
            'product_url' => $quote->getStore()->getUrl(),
            'product_type' => 'shipping_fee',
            'categories' => ['shipping']
        ];
        if ($discountAmount < 0) {
            $items[] = [
                'name' => 'Discount',
                'quantity' => 1,
                'unit_amount' => 0,
                'discount_amount'=> $discountAmount * -1,
                'tax_amount'=> 0,
                'sku' => 'discount',
                'product_url' => $quote->getStore()->getUrl(),
                'product_type' => 'discount',
                'categories' => ['discount']
            ];
        }

        if ($totalAmount != $itemsTotal) {
            $this->gr4vyLogger->logMixed(['totalAmount' => $totalAmount, 'itemsTotal' => $itemsTotal], "Item to Total mismatch");
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
