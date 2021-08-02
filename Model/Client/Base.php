<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Client;

use Gr4vy\Payment\Helper\Data as Gr4vyHelper;
use Gr4vy\Payment\Helper\Logger as Gr4vyLogger;
use Gr4vy\Payment\Model\Source\PrivateKey;

class Base
{
    /**
     * @var Gr4vyHelper
     */
    protected $gr4vy_helper;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vy_logger;

    /**
     * @var PrivateKey
     */
    protected $source_privatekey;

    /**
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Gr4vyHelper $gr4vy_helper,
        Gr4vyLogger $gr4vy_logger,
        PrivateKey $source_privatekey,
        array $data = []
    ) {
        $this->gr4vy_helper = $gr4vy_helper;
        $this->gr4vy_logger = $gr4vy_logger;
        $this->source_privatekey = $source_privatekey;
    }

    /**
     * prepare configuration values and assign to gr4vy configuration
     *
     * @return \Gr4vy\Gr4vyConfig
     */
    protected function getGr4vyConfig()
    {
        $gr4vy_id = $this->gr4vy_helper->getGr4vyId();
        $private_key = $this->getPrivateKey();

        return new \Gr4vy\Gr4vyConfig($gr4vy_id, $private_key);
    }
    
    /**
     * retrieve private key
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->source_privatekey->getPrivateKeyDirAbsolutePath()
            .DIRECTORY_SEPARATOR
            .$this->gr4vy_helper->getPrivateKey();
    }
}
