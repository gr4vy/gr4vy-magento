<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Client;

use Gr4vy\Magento\Helper\Data as Gr4vyHelper;
use Gr4vy\Magento\Helper\Logger as Gr4vyLogger;
use Gr4vy\Magento\Model\Source\PrivateKey;

class Base
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
     * @var PrivateKey
     */
    protected $source_privatekey;

    /**
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Gr4vyHelper $gr4vyHelper,
        Gr4vyLogger $gr4vyLogger,
        PrivateKey $source_privatekey,
        array $data = []
    ) {
        $this->gr4vyHelper = $gr4vyHelper;
        $this->gr4vyLogger = $gr4vyLogger;
        $this->source_privatekey = $source_privatekey;
    }

    /**
     * prepare configuration values and assign to gr4vy configuration
     *
     * @return \Gr4vy\Gr4vyConfig
     */
    protected function getGr4vyConfig()
    {
        $gr4vy_id = $this->gr4vyHelper->getGr4vyId();
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
            .$this->gr4vyHelper->getPrivateKey();
    }
}
