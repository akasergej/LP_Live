<?php

namespace LP\LPShipping\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @inheritDoc
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $data = [];

            $statuses = [
                'lp_courier_called'         => __('Courier Called'),
                'lp_courier_not_called'     => __('Courier Not Called'),
                'lp_shipment_created'       => __('Shipment Created'),
                'lp_shipment_not_created'   => __('Shipment Not Created'),
                'lp_shipment_canceled'      => __('Shipment Canceled')
            ];

            foreach ($statuses as $code => $info) {
                $data [] = [ 'status' => $code, 'label' => $info ];
            }

            $select = $setup->getConnection()->select()
                ->from($setup->getTable('sales_order_status'), 'status')
                ->where('status = :status');

            $bind = [':status' => 'lp_courier_called'];

            $exists = $setup->getConnection()->fetchOne($select, $bind);

            if (!$exists) {
                $setup->getConnection()
                    ->insertArray(
                        $setup->getTable('sales_order_status'),
                        [ 'status', 'label' ],
                        $data
                    );
            }
        }
    }
}
