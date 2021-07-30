<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Gr4vy;

use Gr4vy\Payment\Api\Data\BuyerInterface;
use Gr4vy\Payment\Api\Data\BuyerInterfaceFactory;
use Gr4vy\Payment\Helper\Data as Gr4vyHelper;
use Gr4vy\Payment\Model\Source\PrivateKey;
use Magento\Framework\Api\DataObjectHelper;

class Base
{
    /**
     * @var Gr4vyHelper
     */
    protected $gr4vy_helper;

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
        BuyerInterfaceFactory $buyerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Gr4vy\Payment\Model\ResourceModel\Buyer $resource,
        \Gr4vy\Payment\Model\ResourceModel\Buyer\Collection $resourceCollection,
        Gr4vyHelper $gr4vy_helper,
        PrivateKey $source_privatekey,
        array $data = []
    ) {
        $this->buyerDataFactory = $buyerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->gr4vy_helper = $gr4vy_helper;
        $this->source_privatekey = $source_privatekey;
    }

    /**
     * generate token for current quote
     */
    public function generateToken()
    {
    }

    /**
     * prepare configuration values and assign to gr4vy configuration
     */
    private function initializeConfig()
    {
    }
    
    /**
     * retrieve private key
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->source_privatekey->getPrivateKeyDirAbsolutePath().DIRECTORY_SEPARATOR.$this->gr4vy_helper->getPrivateKey()
    }
}
