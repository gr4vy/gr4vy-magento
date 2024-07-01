<?php
namespace Gr4vy\Magento\Plugin;

class OrderEmailPlugin
{
    public function aroundSend(
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order $order,
        $forceSyncMode = false
    ) {
        // Check some condition if needed
        if ($order->getCustomerEmail() === "fake@email.com") {
            return false; // Prevent email from being sent
        }

        // Proceed with the original method if needed
        return $proceed($order, $forceSyncMode);
    }
}
