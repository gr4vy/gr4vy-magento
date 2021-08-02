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
            $embed = array(
                "amount" => $amount,
                "currency" => $currency,
                "buyer_id" => $buyer_id
            );
            return strval($this->getGr4vyConfig()->getEmbedToken($embed));
        }
        catch (\Exception $e) {
            $this->gr4vy_logger->logException($e);
        }
    }
}
