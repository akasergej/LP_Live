<?php

declare(strict_types=1);

namespace LP\LPShipping\Model\Config\Source;

use Magento\Config\Model\Config\CommentInterface;

class PhoneNumberComment implements CommentInterface
{

    public function getCommentText($elementValue): string
    {
        return sprintf('
            %s +370XXXXXXXX <br>
            %s +371X XXXXXXX <br>
            %s +372X XXXXXXX %s +372X XXXXXX;<br>
        ',
            __('Lithuania'),
            __('Latvia'),
            __('Estonia'),
            __('or'),
        );
    }
}
