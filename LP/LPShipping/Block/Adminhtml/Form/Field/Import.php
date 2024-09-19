<?php

namespace LP\LPShipping\Block\Adminhtml\Form\Field;

class Import extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setType('file');
    }

    /**
     *
     * @return string
     */
    public function getElementHtml()
    {
        return parent::getElementHtml();
    }
}
