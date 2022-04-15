<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Plugin\Magento\Customer\Api;

use Gr4vy\Magento\Helper\Customer as CustomerHelper;

class AddressRepositoryInterface
{

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        CustomerHelper $customerHelper
    ) {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Push data to gr4vy after customer save adress
     *
     * @return string redirect url or error message.
     */
    public function aroundSave(
        \Magento\Customer\Api\AddressRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Customer\Api\Data\AddressInterface $address
    ) {
        $result = $proceed($address);
        $this->customerHelper->checkGr4vyAddress($address);

        return $result;
    }
}
