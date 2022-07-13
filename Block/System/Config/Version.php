<?php
namespace Gr4vy\Magento\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Class Version
 */
class Version extends Field
{
    /**
     * Module name
     */
    const MODULE_NAME = 'Gr4vy_Magento';

    /**
     * @var string
     */
    protected $_template = 'Gr4vy_Magento::system/config/version.phtml';

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * VersionCompare constructor.
     * @param Context $context
     * @param ReadFactory $readFactory
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(
        Context $context,
        ReadFactory $readFactory,
        ComponentRegistrarInterface $componentRegistrar
    )
    {
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;

        parent::__construct($context);
    }

    /**
     * @return bool|mixed
     */
    public function getCurrentVersion()
    {
        if ($version = $this->getCurrentComposerVersion()) {
            return $version;
        }

        return false;
    }

    /**
     * @return mixed
     */
    protected function getCurrentComposerVersion()
    {
        try {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                self::MODULE_NAME
            );

            $dirReader = $this->readFactory->create($path);
            $composerJsonData = $dirReader->readFile('composer.json');
            $data = json_decode($composerJsonData, true);

            return $data['version'] ?? false;

        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
