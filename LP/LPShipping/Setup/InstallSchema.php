<?php

namespace LP\LPShipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->updateMagentoTables($setup);

        $this->createApiTokenTable($setup);
        $this->createTableRatesTable($setup);
        $this->createCountryListTable($setup);
        $this->createLPExpressTableRatesTable($setup);
        $this->createLPExpressTerminalsTable($setup);
        $this->createCN22Table($setup);
        $this->createCN23Table($setup);
        $this->createSenderTable($setup);
        $this->createTrackingEventsTable($setup);

        $setup->endSetup();
    }

    private function updateMagentoTables(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();

        $con->addColumn(
            $setup->getTable('quote'),
            'lpexpress_terminal',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'Selected LPExpress terminal'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lpexpress_terminal',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'Selected LPExpress terminal'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_shipping_type',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'LP Shipping Type'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_shipping_size',
            [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'LP Shipping Size'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_request_id',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => true,
                'comment'  => 'LP Shipping Request ID'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_shipping_item_id',
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment'  => 'LP Shipping Item ID'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_shipment_tracking_updated',
            [
                'type'     => Table::TYPE_DATETIME,
                'nullable' => true,
                'comment'  => 'LP Shipment Track Events Updated'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_cod',
            [
                'type'     => Table::TYPE_DECIMAL,
                'length'   => '20,4',
                'nullable' => true,
                'comment'  => 'LP COD Value'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_manifest_created',
            [
                'type' => Table::TYPE_DATETIME,
                'nullable' => true,
                'comment' => 'LP Manifest created date'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_shipping_package_quantity',
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment'  => 'LP Shipping Package Quantity'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_unique_id',
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => true,
                'comment'  => 'LP unique id'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_package_weight',
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment'  => 'Lp Package Weight'
            ]
        );

        $con->addColumn(
            $setup->getTable('sales_order'),
            'lp_return_parcel_id',
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment'  => 'Lp return parcel id'
            ]
        );
    }

    private function createApiTokenTable(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();
        $con->dropTable($setup->getTable('lp_api_token'));
        $lpApiTokenTable = $con->newTable($setup->getTable('lp_api_token'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID')
            ->addColumn(
                'access_token',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'refresh_token',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'expires',
                Table::TYPE_DATETIME,
                ['nullable' => false]
            )->addColumn(
                'updated',
                Table::TYPE_DATETIME,
                ['nullable' => true]
            )->addIndex(
                $setup->getIdxName('lp_api_token', ['id']),
                ['id']
            )->setComment('API Tokens');
        $con->createTable($lpApiTokenTable);
    }

    private function createTableRatesTable(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();
        $con->dropTable($setup->getTable('lp_table_rates'));
        $lp_table_rates = $con->newTable($setup->getTable('lp_table_rates'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'weight_to',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'p2h_untracked_price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'p2h_tracked_price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'p2h_signed_price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addIndex(
                $setup->getIdxName('lp_table_rates', ['weight_to']),
                ['weight_to']
            )->setComment('Table rates for LP');
        $con->createTable($lp_table_rates);
    }

    private function createCountryListTable(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();
        $con->dropTable($setup->getTable('lp_country_list'));
        $lp_country_list = $con->newTable(
            $setup->getTable('lp_country_list')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'country_id',
            Table::TYPE_INTEGER,
            255,
            ['nullable' => false]
        )->addColumn(
            'country',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        )->addColumn(
            'country_code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        )->addIndex(
            $setup->getIdxName('lp_country_list', ['country_id']),
            ['country_id']
        )->setComment('LP available country list');

        $con->createTable($lp_country_list);
    }

    private function createLPExpressTableRatesTable(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();
        $con->dropTable($setup->getTable('lpexpress_table_rates'));
        $lpexpress_table_rates = $con->newTable($setup->getTable('lpexpress_table_rates'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'weight_to',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'h2h_hands_price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                't2h_hands_price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                't2t_terminal_price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'h2t_terminal_price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                't2s_terminal_price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'h2p_tracked_signed_price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addIndex(
                $setup->getIdxName('lpexpress_table_rates', ['weight_to']),
                ['weight_to']
            )->setComment('Table rates for LP Express');

        $con->createTable($lpexpress_table_rates);
    }

    private function createLPExpressTerminalsTable(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();
        $con->dropTable($setup->getTable('lpexpress_terminal_list'));
        $lpexpress_terminal_list = $con->newTable($setup->getTable('lpexpress_terminal_list'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'terminal_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'LP Express terminal ID'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'address',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'city',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'country_code',
                Table::TYPE_TEXT,
                5,
                ['nullable' => false]
            )->addIndex(
                $setup->getIdxName('lpexpress_terminal_item', ['terminal_id']),
                ['terminal_id']
            )->setComment('LP Express terminal list');

        $con->createTable($lpexpress_terminal_list);
    }

    private function createCN22Table(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();
        $con->dropTable($setup->getTable('lp_cn22_form_data'));
        $lp_cn22_form_data = $con->newTable($setup->getTable('lp_cn22_form_data'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )->addColumn(
                'parcel_type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'parcel_description',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'cn_parts',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )->addIndex(
                $setup->getIdxName('lp_cn22_form_data', ['order_id']),
                ['order_id']
            )->setComment('LP CN22 form data');

        $con->createTable($lp_cn22_form_data);
    }

    private function createCN23Table(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();
        $con->dropTable($setup->getTable('lp_cn23_form_data'));
        $lp_cn23_form_data = $con->newTable($setup->getTable('lp_cn23_form_data'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )->addColumn(
                'cn_parts',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )->addColumn(
                'exporter_customs_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'parcel_type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'license',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'certificate',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'invoice',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'notes',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )->addColumn(
                'failure_instruction',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'importer_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'importer_customs_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'importer_email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'importer_fax',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'importer_phone',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'importer_tax_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'importer_vat_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'description',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )->addIndex(
                $setup->getIdxName('lp_cn23_form_data', ['order_id']),
                ['order_id']
            )->setComment('LP CN23 form data');

        $con->createTable($lp_cn23_form_data);
    }

    private function createSenderTable(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();
        $con->dropTable($setup->getTable('lp_sender_data'));
        $lp_sender_data = $con->newTable($setup->getTable('lp_sender_data'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'phone',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )->addColumn(
                'email',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )->addColumn(
                'country_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false]
            )->addColumn(
                'city',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )->addColumn(
                'street',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )->addColumn(
                'building_number',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )->addColumn(
                'apartment',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )->addColumn(
                'postcode',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )->addColumn(
                'address_line_1',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )->addColumn(
                'address_line_2',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )->addIndex(
                $setup->getIdxName('lp_sender_data', ['order_id']),
                ['order_id']
            )->setComment('LP tracking events');

        $con->createTable($lp_sender_data);
    }

    private function createTrackingEventsTable(SchemaSetupInterface $setup)
    {
        $con = $setup->getConnection();
        $con->dropTable($setup->getTable('lp_tracking_events'));
        $lp_tracking_events = $con->newTable($setup->getTable('lp_tracking_events'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'tracking_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'state',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'events',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )->addIndex(
                $setup->getIdxName('lp_tracking_events', ['tracking_code']),
                ['tracking_code']
            )->setComment('LP tracking events');

        $con->createTable($lp_tracking_events);
    }
}
