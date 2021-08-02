<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Logger extends AbstractHelper
{

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * custom logger to log exception content to log file
     *
     * @return void
     */
    public function logException(\Exception $e)
    {
        // debug start
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/gr4vy.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($e->getMessage());
    }

    /**
     * custom logger to log exception content to log file
     *
     * @return void
     */
    public function logMixed($mixed_data)
    {
        // debug start
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/gr4vy.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($mixed_data);
    }
}

