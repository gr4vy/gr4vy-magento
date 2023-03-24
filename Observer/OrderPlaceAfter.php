<?php

namespace Gr4vy\Magento\Observer;

use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Helper\Order as OrderHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Update order status after place order
 */
class OrderPlaceAfter implements ObserverInterface
{
    const NEW_ORDER_STATUS = Order::STATE_PENDING_PAYMENT;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * OrderPlaceAfter constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderHelper $orderHelper
     * @param Gr4vyLogger $gr4vyLogger
     * @param Gr4vyHelper $gr4vyHelper
     */
    public function __construct(
        OrderRepositoryInterface    $orderRepository,
        OrderHelper $orderHelper,
        Gr4vyLogger $gr4vyLogger,
        Gr4vyHelper $gr4vyHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderHelper = $orderHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->gr4vyHelper = $gr4vyHelper;
    }

    /**
     * Update order status
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->gr4vyHelper->checkGr4vyReady()) {
            try {
                /** @var Order $order */
                $order = $observer->getEvent()->getData('order');
                $order->setState(self::NEW_ORDER_STATUS)->setStatus(self::NEW_ORDER_STATUS);
                $order->setIsCustomerNotified(false);
                $this->orderHelper->updateOrderHistoryData(
                    $order->getEntityId(),
                    self::NEW_ORDER_STATUS,
                    __('Order has been placed by Magento.')
                );
                $this->orderRepository->save($order);
            }
            catch (\Exception $e) {
                $this->gr4vyLogger->logException($e);
            }
        }
    }
}
