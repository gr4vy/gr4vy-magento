<?php
declare(strict_types=1);

namespace Gr4vy\Magento\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Gr4vy\Magento\Helper\Order as OrderHelper;
use Magento\Sales\Model\Order;

/**
 * Get order details
 */
class OrderDetails implements ActionInterface
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * DisableQuote constructor.
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     * @param OrderHelper $orderHelper
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        OrderHelper $orderHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->context = $context;
        $this->orderHelper = $orderHelper;
    }

    /**
     * Return incrementId of an order
     *
     * @return Json
     */
    public function execute()
    {
        $jsonData = ['incrementId' => ''];
        $params = $this->context->getRequest()->getParams();
        $orderId = isset($params['orderId']) ? $params['orderId'] : null;
        if ($orderId) {
            $incrementId = $this->orderHelper->getIncrementId($orderId);
            $jsonData = ['incrementId' => $incrementId];
        }

        return $this->resultJsonFactory->create()->setData($jsonData);
    }
}
