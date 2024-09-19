require([
        'jquery',
        'mage/translate',
        'jquery/validate'],
    function($){
        $.validator.addMethod(
            'validate-phone-number', function (value, element) {
                if (value.length === 0) {
                    return true;
                }
                let $table = $(element).closest('table');
                let $countryElement = $table.find('#carriers_lpcarrier_lpcarriersender_sender_country');
                if ($countryElement.length === 0) {
                    $countryElement = $table.find('#carriers_lpcarrier_warehouse_country');
                }
                let selectedCountry = $countryElement.find(":selected").val();
                let regex;
                switch(selectedCountry) {
                    //Latvia
                    case '229':
                        regex = /^(\+371)\d{8}$/;
                        break;
                    //Estonia
                    case '169':
                        regex = /^(\+372)\d{7}|(\+372)\d{8}$/;
                        break;
                    default:
                        //Lithuania
                        regex = /^(\+370)\d{8}$/;
                }
                return regex.test(value);
            }, $.mage.__('Wrong phone format'));
    }
);
