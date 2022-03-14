<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Psr\Log\LoggerInterface;

class Logger extends AbstractHelper
{
    private $expected_error_msgs = array(
        'No such entity with cartId' => 'Quote not available',
        '409 Conflict' => 'Request conflicts with existing record'
    );
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

            $original_message = $e->getMessage();
            if ($translated_message = $this->translateErrorMsg($original_message)) {
                $this->logger->error(__('Recognized Error :'), ['msg' => $translated_message]);
            }
            else {
                $this->logger->error(
                    $original_message,
                    ['detail' => $e->getTraceAsString()]
                );
            }

            if (method_exists($e, 'getResponseBody')) {
                $this->logger->info(
                    __('Response Body'),
                    ['json' => $e->getResponseBody()]
                );
            }
        }
    }

    /**
     * check to see if error message is expected or not
     *
     * @param string
     * @return boolean
     */
    public function translateErrorMsg($error_message)
    {
        foreach ($this->expected_error_msgs as $msg => $meaning ){
            if (strpos($error_message, $msg) !== false) {
                return $meaning;
            }
        }

        return null;
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
            $this->logger->info(
                __('Info Array'),
                $mixed_data
            );
        }
    }
}

