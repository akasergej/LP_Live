<?php

namespace LP\LPShipping\Model\Config\Source;

use LP\LPShipping\Model\Config;
use Magento\Config\Model\Config\CommentInterface;

class ModuleStatus implements CommentInterface
{
    /**
     * @var Config $_config
     */
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function getCommentText($elementValue): string
    {
        if (!$this->config->getStatus()) {
            return sprintf(
                '<span style="font-size: 16px; color: red;">%s</span>',
                __('Unauthorized, contact api-manosiuntos@post.lt for api credentials.')
            );
        } else {
            return sprintf(
                '<span style="font-size: 16px; color: green;">%s</span>',
                __('Active')
            );
        }
    }
}
