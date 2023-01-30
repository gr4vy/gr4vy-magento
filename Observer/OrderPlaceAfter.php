<?php

namespace Gr4vy\Magento\Observer;

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
     * OrderPlaceAfter constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderHelper $orderHelper
     */
    public function __construct(
        OrderRepositoryInterface    $orderRepository,
        OrderHelper $orderHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderHelper = $orderHelper;
    }

    /**
     * Update order status
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
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
}
