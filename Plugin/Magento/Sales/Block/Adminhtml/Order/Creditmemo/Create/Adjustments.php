<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Plugin\Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create;

use Gr4vy\Magento\Helper\Data as Gr4vyHelper;

class Adjustments
{
    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @param Gr4vyHelper $gr4vyHelper
     */
    public function __construct(
        Gr4vyHelper $gr4vyHelper
    ) {
        $this->gr4vyHelper = $gr4vyHelper;
    }

    /**
     * modify output of credit memo adjustment fields - prevent partial refund
     *
     * @return string
     */
    public function afterFetchView(
        \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Adjustments $subject,
        $result
    ) {
        if ($this->gr4vyHelper->blockPartialRefund()) {
            $credit_memo = $subject->getParentBlock()->getSource();
            $payment = $credit_memo->getOrder()->getPayment();

            if ($payment && $payment->getMethod() === \Gr4vy\Magento\Model\Payment\Gr4vy::PAYMENT_METHOD_CODE) {
                return str_replace('input', 'input disabled', $result);
            }
        }

        return $result;
    }
}

