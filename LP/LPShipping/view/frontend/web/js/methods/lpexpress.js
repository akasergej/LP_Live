define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/quote',
    'jquery/validate',
    'mage/translate',
    'mage/storage',
    'select2'
], function ($, ko, Select, quote) {

    $(document).on('change', '#lpexpress-terminal-list', function () {
        $('#co-shipping-method-form').trigger('submit');
    });

    var terminalsSelectInitializedForCountry = null;
    function initSelectOptions($select, selectedCountryCode) {
        $select.empty();
        $select.append('<option value="" disabled selected hidden>' + $.mage.__('Please select terminal..') + '</option>');

        $.each(window.checkoutConfig.terminal.list, function (country, countryTerminals) {
            if (selectedCountryCode && country.toUpperCase() === selectedCountryCode) {
                $.each(countryTerminals, function (city, cityTerminals) {
                    $select.append('<optgroup label="' + city + '"></optgroup>');
                    $.each(cityTerminals, function (id, terminal) {
                        let $optgroup = $select.find('optgroup[label="' + city + '"]');
                        $optgroup.append('<option value="' + id + '">' + terminal + '</option>');
                    })
                })
            }
        });
    }

    function initSelect2(selectedCountryCode) {
        let $lpExpressTerminalList = $('#lpexpress-terminal-list');
        if ($lpExpressTerminalList.length) {
            initSelectOptions($lpExpressTerminalList, selectedCountryCode);
            $lpExpressTerminalList.select2();
        }
    }
    return Select.extend({
        initialize: function () {
            let self = this;
            this._super();

            self.availableTerminals = function (selectedCountryCode, needs) {
                if (needs) {
                    if (terminalsSelectInitializedForCountry === selectedCountryCode) {
                        return;
                    }
                    terminalsSelectInitializedForCountry = selectedCountryCode;
                    initSelect2(selectedCountryCode);
                }
            };

            self.selectTerminalCaption = $.mage.__('Please select terminal..');
        },
        selectedMethod: function () {
            let method = quote.shippingMethod();

            // if is selected method
            if (method) {
                // Hide or show terminal validation error
                $('div[name="shippingAddress.lpexpress_terminal"] .field-error')
                    .css({display: this.selectedMethodNeedsTerminalsList() ? 'block' : 'none'});

                // Add padding bottom to the container
                $('div[name="shippingAddress.lpexpress_terminal"]')
                    .css({paddingBottom: this.selectedMethodNeedsTerminalsList() ? '20px' : '0px'});

                return method.method_code;
            }

            return null;
        },
        getSelectedCountry: function () {
            return quote.shippingAddress().countryId;
        },
        selectedMethodNeedsTerminalsList: function () {
            const terminalsShippingMethods = window.checkoutConfig.terminal_shipping_methods;
            if (quote.shippingMethod()) {
                let selectedMethod = quote.shippingMethod().method_code;

                return terminalsShippingMethods.includes(selectedMethod);
            }

            return false;
        },
        getDeliveryTime: function () {
            // Get data from configProvider
            let deliveryTimeObj = window.checkoutConfig.lp_delivery_time;

            if (deliveryTimeObj.hasOwnProperty(this.selectedMethod())) {
                return deliveryTimeObj[this.selectedMethod()];
            }

            return null;
        }
    });
});