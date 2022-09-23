<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Plugin\Magento\Customer\Api;

use Gr4vy\Magento\Helper\Customer as CustomerHelper;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;

class AddressRepositoryInterface
{
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @param CustomerHelper $customerHelper
     * @param Gr4vyHelper $gr4vyHelper
     */
    public function __construct(
        CustomerHelper $customerHelper,
        Gr4vyHelper $gr4vyHelper
    ) {
        $this->customerHelper = $customerHelper;
        $this->gr4vyHelper = $gr4vyHelper;
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
        if ($this->gr4vyHelper->checkGr4vyReady()) {
            $this->customerHelper->checkGr4vyAddress($address);
        }

        return $result;
    }
}
