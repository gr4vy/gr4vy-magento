<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Plugin\Magento\Payment\Helper;

use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;

class Data
{

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vyLogger;

    /**
     * @param Gr4vyHelper $gr4vyHelper
     * @param OrderHelper $orderHelper
     */
    public function __construct(
        Gr4vyHelper $gr4vyHelper,
        Gr4vyLogger $gr4vyLogger
    ) {
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
    }

    /**
     * Plugin to update getPaymentMethods() results with Gr4vy config values
     *
     * @param \Magento\Payment\Helper\Data $subject
     * @param $result
     * @return array
     */
    public function afterGetPaymentMethods(\Magento\Payment\Helper\Data $subject, $result)
    {
        // modify gr4vy payment config values using custom config keys
        $gr4vy = \Gr4vy\Magento\Model\Payment\Gr4vy::PAYMENT_METHOD_CODE;
        if (isset($result[$gr4vy])) {
            if ($this->gr4vyHelper->isEnabled()) {
                $result[$gr4vy]['title'] = $this->gr4vyHelper->getPaymentTitle();
            }
            else {
                unset($result[$gr4vy]);
            }
        }

        return $result;
    }
}
