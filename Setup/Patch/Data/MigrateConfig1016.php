<?php

namespace Gr4vy\Magento\Setup\Patch\Data;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateConfig1016 implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): MigrateConfig1016
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->migrateConfigValues();
        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    /**
     * Mark all quotes as inactive so that switch over to new payments endpoint happens
     */
    private function migrateConfigValues()
    {
        $table = $this->moduleDataSetup->getTable('core_config_data');
        $manipulation = new \Zend_Db_Expr("REPLACE(`path`, 'payment/gr4vy', 'payment/gr4vy_section/api')");

        $this->moduleDataSetup->getConnection()->update($table, ['path' => $manipulation], "`path` REGEXP '^payment/gr4vy'");
    }
}
