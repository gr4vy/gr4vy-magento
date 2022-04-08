<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model;

use Gr4vy\Magento\Api\Data\TransactionInterface;
use Gr4vy\Magento\Api\Data\TransactionInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Transaction extends \Magento\Framework\Model\AbstractModel
{
    const TYPE_TRANSACTION = 'transaction';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSING_FAILED = 'processing_failed';
    const STATUS_CAPTURE_SUCCEEDED = 'capture_succeeded';
    const STATUS_CAPTURE_PENDING = 'capture_pending';
    const STATUS_CAPTURE_DECLINED = 'capture_declined';
    const STATUS_CAPTURE_FAILED = 'capture_failed';
    const STATUS_AUTHORIZATION_SUCCEEDED = 'authorization_succeeded';
    const STATUS_AUTHORIZATION_PENDING = 'authorization_pending';
    const STATUS_AUTHORIZATION_DECLINED = 'authorization_declined';
    const STATUS_AUTHORIZATION_FAILED = 'authorization_failed';
    const STATUS_AUTHORIZATION_EXPIRED = 'authorization_expired';
    const STATUS_AUTHORIZATION_VOIDED = 'authorization_voided';
    const STATUS_AUTHORIZATION_VOID_PENDING = 'authorization_void_pending';
    const STATUS_AUTHORIZATION_VOID_DECLINED = 'authorization_void_declined';
    const STATUS_AUTHORIZATION_VOID_FAILED = 'authorization_void_failed';
    const STATUS_REFUND_SUCCEEDED = 'refund_succeeded';
    const STATUS_REFUND_PENDING = 'refund_pending';
    const STATUS_REFUND_DECLINED = 'refund_declined';
    const STATUS_REFUND_FAILED = 'refund_failed';
    const STATUS_BUYER_APPROVAL_SUCCEEDED = 'buyer_approval_succeeded';
    const STATUS_BUYER_APPROVAL_PENDING = 'buyer_approval_pending';
    const STATUS_BUYER_APPROVAL_DECLINED = 'buyer_approval_declined';
    const STATUS_BUYER_APPROVAL_FAILED = 'buyer_approval_failed';
    const STATUS_BUYER_APPROVAL_TIMEDOUT = 'buyer_approval_timedout';

    protected $transactionDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'gr4vy_transactions';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param TransactionInterfaceFactory $transactionDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Gr4vy\Magento\Model\ResourceModel\Transaction $resource
     * @param \Gr4vy\Magento\Model\ResourceModel\Transaction\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        TransactionInterfaceFactory $transactionDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Gr4vy\Magento\Model\ResourceModel\Transaction $resource,
        \Gr4vy\Magento\Model\ResourceModel\Transaction\Collection $resourceCollection,
        array $data = []
    ) {
        $this->transactionDataFactory = $transactionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve transaction model with transaction data
     * @return TransactionInterface
     */
    public function getDataModel()
    {
        $transactionData = $this->getData();
        
        $transactionDataObject = $this->transactionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $transactionDataObject,
            $transactionData,
            TransactionInterface::class
        );
        
        return $transactionDataObject;
    }
}

