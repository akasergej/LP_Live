<?php

declare(strict_types=1);

namespace LP\LPShipping\Ui\DataProvider\Shipping\Listing;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    protected function _initSelect()
    {
        $this->getSelect()
            ->joinLeft(['shipment' => 'sales_shipment'],
                'shipment.order_id = main_table.entity_id',
                [
                    'packages',
                    'shipment_id' => 'shipment.entity_id',
                    'shipment_created_at' => 'shipment.created_at',
                    'shipping_label' => new \Zend_Db_Expr('CASE
                         WHEN shipment.shipping_label IS NOT NULL THEN 1
                         ELSE 0
                    END')
                ])
            ->join(
                ['order' => 'sales_order'],
                'order.entity_id = main_table.entity_id',
                ['shipping_method', 'lp_manifest_created', 'lp_package_weight', 'lp_shipping_size']
            )
            ->joinLeft(
                ['track' => 'sales_shipment_track'],
                'track.order_id = main_table.entity_id',
                'track_number'
            )
            ->group('main_table.entity_id')
            ->where('order.shipping_method LIKE ?', 'lpcarrier_%');

        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('increment_id', 'main_table.increment_id');
        $this->addFilterToMap('client', 'main_table.customer_name');
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('status', 'main_table.status');

        parent::_initSelect();
    }
}
