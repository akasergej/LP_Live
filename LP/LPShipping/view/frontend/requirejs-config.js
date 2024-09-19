var config = {
    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'LP_LPShipping/js/validation-mixin': true
            },
        }
    },
    paths: {
        'select2': 'LP_LPShipping/js/vendor/select2.min'
    },
    'map': {
        '*': {
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender'
                : 'LP_LPShipping/js/shipping-save-processor-payload-extender',
            'select2': 'LP_LPShipping/js/vendor/select2.min'
        }
    }
};
