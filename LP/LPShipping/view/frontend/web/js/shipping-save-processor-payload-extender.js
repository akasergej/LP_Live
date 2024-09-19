define(['jquery'], function ($) {
    'use strict';

    return function (payload) {

        if (payload.addressInformation['extension_attributes'] !== undefined) {
            payload.addressInformation['extension_attributes']['lpexpress_terminal'] = $('#lpexpress-terminal-list').val();
        } else {
            payload.addressInformation['extension_attributes'] = {
                lpexpress_terminal: $('#lpexpress-terminal-list').val(),
            }
        }

        return payload;
    };
});
