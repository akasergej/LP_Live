<?php

namespace LP\LPShipping\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

class Export extends AbstractElement
{
    /**
     * Backend url /admin
     *
     * @var UrlInterface
     */
    protected $_backendUrl;

    public function __construct(
        Factory           $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper           $escaper,
        UrlInterface      $backendUrl,
        array             $data = []
    )
    {
        $this->_backendUrl = $backendUrl;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Return html for the export button
     *
     * @return string
     */
    public function getElementHtml(): string
    {
        /** @var Button $buttonBlock */
        $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock(Button::class);

        $params = ['website' => $buttonBlock->getRequest()->getParam('website')];

        // Select particular controller for import/export
        switch ($this->getHtmlId()) {
            case 'carriers_lpcarrier_lpcarriershipping_lp_export_rates':
                $fileName = 'lp-tables';
                $routePath = '*/*/exportLpTableRates';
                break;
            case 'carriers_lpcarrier_lpcarriershipping_lpexpress_export_rates':
                $fileName = 'lpexpress-tables';
                $routePath = '*/*/exportLpExpressTableRates';
                break;
        }

        $url = $this->_backendUrl->getUrl($routePath, $params);
        $data = [
            'label'   => __('Export CSV'),
            'onclick' => "setLocation('" .
                $url .
                $fileName . ".csv' )",
            'class'   => '',
        ];

        return $buttonBlock->setData($data)->toHtml();
    }
}
