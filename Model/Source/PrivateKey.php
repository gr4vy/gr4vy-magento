<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Source;

class PrivateKey extends \Magento\Config\Model\Config\Backend\File
{
    const UPLOAD_DIR = 'gr4vy';

    /**
     * @return string[]
     */
    public function getAllowedExtensions() {
        return ['pem'];
    }

    /**
     * get default upload directory under pub/media
     * see etc/adminhtml/system.xml line 21
     *
     * @return string
     */
    public function getUploadDir()
    {
        return self::UPLOAD_DIR;
    }

    /**
     * retrieve absolute path to private key
     *
     * @return string
     */
    public function getPrivateKeyDirAbsolutePath()
    {
        return $this->getUploadDirPath($this->getUploadDir());
    }
}
