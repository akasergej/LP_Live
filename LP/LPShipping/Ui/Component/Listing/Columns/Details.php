<?php

declare(strict_types=1);

namespace LP\LPShipping\Ui\Component\Listing\Columns;

use LP\LPShipping\Helper\ShippingHelper;
use Magento\Ui\Component\Listing\Columns\Column;

class Details extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['details'] = html_entity_decode(
                    '<div>' . __('Date: ') . ($item['shipment_created_at'] ?? '-') . '
                                    <div>' . __('Size: ') . ($item['lp_shipping_size'] ?? "XS") . '</div>
                                    <div>' . __('Weight: ') . ($item['lp_package_weight'] ?? 0) . ' g' . '</div>
                               </div>'
                );
            }
        }

        return $dataSource;
    }
}
