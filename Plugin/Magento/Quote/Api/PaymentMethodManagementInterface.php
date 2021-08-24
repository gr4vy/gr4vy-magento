<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Plugin\Magento\Quote\Api;

use Gr4vy\Payment\Model\Client\Embed as Gr4vyEmbed;
use Gr4vy\Payment\Helper\Logger as Gr4vyLogger;

class PaymentMethodManagementInterface
{
    /**
     * @var Gr4vyEmbed
     */
    protected $embedApi;

    /**
     * @var Gr4vyLogger
     */
    protected $gr4vy_logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        Gr4vyEmbed $embedApi,
        Gr4vyLogger $gr4vy_logger
    ) {
        $this->embedApi = $embedApi;
        $this->gr4vy_logger = $gr4vy_logger;
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
        //var_dump($method->getData());
        $result = $proceed($cartId, $method);
        //var_dump($result); die;

        return $result;
    }
}

