<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Client;

class Embed extends Base
{
    /**
     * retrieve embed token for frontend checkout form
     * $amount must be integer and multiplied by 100 before calling this function
     *
     * @param $amount
     * @param $currency
     * @param $buyer_id
     * @param $cartItems
     * @param $metadata
     * @return string
     */
    public function getEmbedToken($amount, $currency, $buyer_id, $cartItems, $metadata)
    {
        try {
            $embed_params = array(
                "amount" => $amount,
                "currency" => $currency,
                "environment" => $this->gr4vyHelper->getGr4vyEnvironment(),
                "buyer_id" => $buyer_id,
                "cart_items" => $cartItems,
                "metadata" => ['magento_custom_data' => $metadata]
            );
            $token = $this->getGr4vyConfig()->getEmbedToken($embed_params);
            $this->gr4vyLogger->logMixed($embed_params);

            if (is_object($token)) {
                $token_str = $token->toString();
            }
            else {
                $token_str = (string) $token;
            }

            return $token_str;
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }
}
