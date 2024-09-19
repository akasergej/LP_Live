define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/lib/validation/utils'
], function ($, quote, utils) {
    "use strict";
    let lpcarrierAddressFormValid = false;
    let validLpcarrierAddressFormData = null;

    $(document).on('click', '#co-shipping-method-form button[data-role="opc-continue"]', function (e) {
        let method = quote.shippingMethod();
        // if empty or shipping method is not lpcarrier, we don't care
        if (method === null || method.carrier_code !== 'lpcarrier') {
            return true;
        }
        let shippingAddress = quote.shippingAddress();
        let postData = {
            'firstname': shippingAddress.firstname,
            'city': shippingAddress.city,
            'address': shippingAddress.street[0],
            'postcode': shippingAddress.postcode,
            'country_id': shippingAddress.countryId,
        };
        let serializedData = JSON.stringify(postData);

        if (validLpcarrierAddressFormData === serializedData) {
            return true;
        }

        e.preventDefault();
        $('body').trigger('processStart');
        var errorMessageText = '';
        let $errorMessage = $('#lpcarrier-address-error');
        $errorMessage.hide();
        $.post('/lpshipping/address/validate', postData, function (data) {
            if (data.status !== true) {
                lpcarrierAddressFormValid = false;
                errorMessageText = data.errorMessage;
            } else {
                validLpcarrierAddressFormData = serializedData;
                lpcarrierAddressFormValid = true;
            }
        }).then(function() {
            $('body').trigger('processStop');
            if (!lpcarrierAddressFormValid) {
                let container = $('#onepage-checkout-shipping-method-additional-load');
                let errorMessage = '<div id="lpcarrier-address-error" class="field-error"><span>' + errorMessageText + '</span></div>';
                if ($errorMessage.length === 0) {
                    container.append(errorMessage);

                    return true;
                }

                if ($errorMessage.is(':hidden')) {
                    $errorMessage.show();
                }
            } else {
                $errorMessage.hide();
                $('#co-shipping-method-form button[data-role="opc-continue"]').trigger('click');
            }
            return true;
        });
    });

    return function (validator) {
        validator.addRule('select-terminal-required', function () {
            let $shipHereModalButton = $('.modal-footer .action-save-address');
            if ($shipHereModalButton.is(':visible')) {
                return true;
            }
            var method = quote.shippingMethod();
            const terminalsShippingMethods = window.checkoutConfig.terminal_shipping_methods;
            var invalid = method && terminalsShippingMethods.includes(method.method_code) && utils.isEmpty($('.select-terminal-required').val());

            return !invalid;
        }, $.mage.__('This is a required field.'));

        validator.addRule('validate-phone-number', function (value) {
            let $select = $('#co-shipping-form select[name="country_id"]');
            let method = quote.shippingMethod();
            // if empty or shipping method is not lpcarrier, we don't care
            if (value.length === 0 || method === null || method.carrier_code !== 'lpcarrier') {
                return true;
            }
            let selectedCountry = $select.find(":selected").val();
            let regex;
            switch (selectedCountry) {
                case 'LV':
                    regex = /^(\+3712)\d{7}$/;
                    this.validationMessage = '+3712XXXXXXX';
                    break;
                case 'EE':
                    regex = /^(\+3725)\d{6}|(\+3725)\d{7}$/;
                    this.validationMessage = '+3725XXXXXX or +3725XXXXXXX';
                    break;
                default:
                    regex = /^(\+370)\d{8}$/;
                    this.validationMessage = '+370XXXXXXXX';
            }

            return regex.test(value);
        }, function () {
            return $.mage.__('Invalid phone number. Supported format: ' + this.validationMessage)
        });

        return validator;
    }
});
