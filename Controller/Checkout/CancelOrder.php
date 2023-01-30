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
 * Cancel magento order if gr4vy transaction is failed
 */
class CancelOrder implements ActionInterface
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
     * Cancel order if gr4vy transaction fails
     *
     * @return Json
     */
    public function execute()
    {
        $params = $this->context->getRequest()->getParams();
        $orderId = isset($params['orderId']) ? $params['orderId'] : null;
        if ($orderId) {
            $this->orderHelper->cancelMagentoOrder($orderId);
            $this->orderHelper->updateOrderHistoryData(
                $orderId,
                Order::STATE_CANCELED,
                __('Order has been cancelled by gr4vy payment response.')
            );
        }

        return $this->resultJsonFactory->create();
    }
}
