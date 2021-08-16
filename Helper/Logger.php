<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Psr\Log\LoggerInterface;

class Logger extends AbstractHelper
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $gr4vyHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LoggerInterface $logger,
        Data $gr4vyHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->gr4vyHelper = $gr4vyHelper;
    }

    /**
     * custom logger to log exception content to log file
     *
     * @param Exception
     * @return void
     */
    public function logException(\Exception $e)
    {
        if ($this->gr4vyHelper->isDebugOn()) {
            $this->logger->error($e->getMessage());
            if (method_exists($e, 'getResponseBody')) {
                $this->logger->info($e->getResponseBody());
            }
        }
    }

    /**
     * custom logger to log exception content to log file
     *
     * @param array
     * @return void
     */
    public function logMixed($mixed_data)
    {
        if ($this->gr4vyHelper->isDebugOn()) {
            $this->logger->info(serialize($mixed_data));
        }
    }
}

