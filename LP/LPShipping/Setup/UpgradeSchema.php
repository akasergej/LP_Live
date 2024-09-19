<?php

namespace LP\LPShipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('lp_table_rates'),
                'country',
                [
                    'type' => Table::TYPE_TEXT,
                    'size' => 5,
                    'nullable' => true,
                    'comment' => 'Country code'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('lpexpress_table_rates'),
                'country',
                [
                    'type' => Table::TYPE_TEXT,
                    'size' => 5,
                    'nullable' => true,
                    'comment' => 'Country code'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            //delete scoped config data, as scoped config is no longer available
            $setup->getConnection()->delete(
                $setup->getTable('core_config_data'),
                "`path` LIKE 'carriers/lpcarrier/%' AND scope != 'default'"
            );
        }
    }
}
