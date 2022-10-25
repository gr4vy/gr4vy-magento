<?php
declare(strict_types=1);

namespace Gr4vy\Magento\Controller\Checkout;

use Gr4vy\Magento\Model\JsonResult;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;

class Config extends Action implements HttpPostActionInterface
{
    /**
     * @var CompositeConfigProvider
     */
    private CompositeConfigProvider $compositeConfigProvider;
    /**
     * @var JsonResult
     */
    private JsonResult $jsonResult;
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @param Context $context
     * @param CompositeConfigProvider $compositeConfigProvider
     * @param JsonResult $jsonResult
     * @param Session $session
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        CompositeConfigProvider $compositeConfigProvider,
        JsonResult $jsonResult,
        Session $session
    ) {
        parent::__construct($context);

        $this->compositeConfigProvider = $compositeConfigProvider;
        $this->jsonResult = $jsonResult;
        $this->session = $session;
    }

    /**
     * Setting the shipping country.
     * recalculating the checkout configuration and returning it with new Gr4vy Token
     */
    public function execute()
    {
        $rawParameter = $this->getRequest()->getContent();
        $parameter = json_decode($rawParameter, true);
        $quote = $this->session->getQuote();

        if (isset($parameter['shipping_country_id'])) {
            $quote->getShippingAddress()
                ->setCountryId($parameter['shipping_country_id']);
        }
        if (isset($parameter['shipping_company'])) {
            $quote->getShippingAddress()
                ->setCompany($parameter['shipping_company']);
        }
        if (isset($parameter['billing_country_id'])) {
            $quote->getBillingAddress()
                ->setCountryId($parameter['billing_country_id']);
        }
        if (isset($parameter['billing_company'])) {
            $quote->getBillingAddress()
                ->setCompany($parameter['billing_company']);
        }

        $result = $this->compositeConfigProvider->getConfig();
        return $this->jsonResult->getJsonResult(200, $result);
    }
}
