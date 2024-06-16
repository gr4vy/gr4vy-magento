<?php

namespace Gr4vy\Magento\Helper;

use Gr4vy\Magento\Api\NonceProviderInterface;

class Magento246NonceProvider {

    public function generateNonce() {
        return bin2hex(random_bytes(16));
    }
}