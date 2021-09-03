<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Payment\Model\Client;

class Embed extends Base
{
    /**
     * retrieve embed token for frotend checkout form
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
                "amount" => intval($amount*100), // amount must be integer , so we multiply float by 100 and cast type to integer
                "currency" => $currency,
                "environment" => $this->gr4vyHelper->getGr4vyEnvironment(),
                "buyer_id" => $buyer_id
            );
            $token = $this->getGr4vyConfig()->getEmbedToken($embed_params);
            $this->gr4vyLogger->logMixed($embed_params);
            return $token->toString();
        }
        catch (\Exception $e) {
            $this->gr4vyLogger->logException($e);
        }
    }
}
