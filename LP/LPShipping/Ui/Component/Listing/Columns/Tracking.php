<?php

declare(strict_types=1);

namespace LP\LPShipping\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Tracking extends Column
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
                if (isset($item['entity_id'])) {
                    if (empty($item['track_number'])) {
                        $item[$this->getData('name')] = __('The shipment has not been formed');

                        continue;
                    }
                    $item[$this->getData('name')] = html_entity_decode(
                        "<a href='https://www.post.lt/siuntu-sekimas?parcels={$item['track_number']}' target='_blank' rel='noopener noreferrer'> {$item['track_number']} </a>"
                    );
                }
            }
        }

        return $dataSource;
    }
}
