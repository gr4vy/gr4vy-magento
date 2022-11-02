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
     * retrieve embed token for frotend checkout form
     * $amount must be integer and mulitplied by 100 before calling this function
     *
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public function getEmbedToken($amount, $currency, $buyer_id)
    {
        try {
            $embed_params = array(
                "amount" => $amount,
                "currency" => $currency,
                "environment" => $this->gr4vyHelper->getGr4vyEnvironment(),
                "buyer_id" => $buyer_id
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
