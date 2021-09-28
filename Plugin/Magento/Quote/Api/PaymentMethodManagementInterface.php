<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Plugin\Magento\Quote\Api;

use Gr4vy\Magento\Model\Client\Embed as Gr4vyEmbed;
use Gr4vy\Magento\Helper\Data as Gr4vyHelper;

class PaymentMethodManagementInterface
{
    /**
     * @var Gr4vyEmbed
     */
    protected $embedApi;

    /**
     * @var Gr4vyHelper
     */
    protected $gr4vyHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        Gr4vyEmbed $embedApi,
        Gr4vyHelper $gr4vyHelper
    ) {
        $this->embedApi = $embedApi;
        $this->gr4vyHelper = $gr4vyHelper;
    }

    /**
     * prepare gr4vy checkout embedded form 
     *
     * @return string redirect url or error message.
     */
    public function aroundSet(
        \Magento\Quote\Api\PaymentMethodManagementInterface $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $method
    ) {
        $cartId = $this->gr4vyHelper->getQuoteIdFromMask($cartId);
        $result = $proceed($cartId, $method);

        return $result;
    }
}

