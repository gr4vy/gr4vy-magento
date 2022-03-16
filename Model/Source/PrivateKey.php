<?php
/**
 * Copyright ©  All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Gr4vy\Magento\Model\Source;

use Magento\Framework\App\Filesystem\DirectoryList;

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

    /**
     * Retrieve upload directory path
     *
     * @param string $uploadDir
     * @return string
     * @since 100.1.0
     */
    protected function getUploadDirPath($uploadDir)
    {
        $this->_mediaDirectory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        return $this->_mediaDirectory->getAbsolutePath($uploadDir);
    }
}
