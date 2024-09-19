require([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {
    function handleSelectedClass(timeout, ignoreSelectedFiltersNotExisting = false) {
        setTimeout(function() {
            const selectedFilters = $('span[data-bind="text: preview"]');
            const filterButtons = $(document).find("button.lp-filter-button");
            if (ignoreSelectedFiltersNotExisting && selectedFilters.length === 0) {
                filterButtons.each(function (key, button) {
                    $(button).removeClass('selected');
                });
                $('button[data-index="all_orders"]').addClass('selected');
            }
            if (selectedFilters.length !== 0) {
                filterButtons.each(function (key, button) {
                    $(button).removeClass('selected');
                });
                let selectedLpQuickFilterButton = 'all_orders';
                $.each(selectedFilters, function (k, v) {
                    if ($(v).text() === 'Courier Called') {
                        selectedLpQuickFilterButton = 'courier_called';
                        return false;
                    } else if ($(v).text() === 'Pending') {
                        selectedLpQuickFilterButton = 'new_orders';
                        return false;
                    } else if ($(v).text() === 'Complete') {
                        selectedLpQuickFilterButton = 'completed_orders';
                        return false;
                    } else if ($(v).text() === 'Shipment Created') {
                        selectedLpQuickFilterButton = 'generated_orders';
                        return false;
                    }
                });
                $('button[data-index="' + selectedLpQuickFilterButton + '"]').addClass('selected');
            } else {
                handleSelectedClass(timeout);
            }
        }, timeout);
    }
    $(document).ready(function () {
        function applyFilter(filterValue) {
            $('body').trigger('processStart');
            const linkUrl = urlBuilder.build('action/filter');
            $.ajax({
                url: '../../../../' + linkUrl,
                type: 'POST',
                context: this,
                data: {
                    filter: filterValue,
                    'form_key': FORM_KEY
                },
                dataType: 'JSON',
                success: function () {
                    location.reload();
                }
            });
        }

        $(document).on('click', "button[data-index='all_orders']", function () {
            applyFilter('');
        });
        $(document).on('click', "button[data-index='new_orders']", function () {
            applyFilter('pending');
        });
        $(document).on('click', "button[data-index='courier_called']", function () {
            applyFilter('lp_courier_called');
        });
        $(document).on('click', "button[data-index='generated_orders']", function () {
            applyFilter('lp_shipment_created');
        });
        $(document).on('click', "button[data-index='completed_orders']", function () {
            applyFilter('complete');
        });
    });
    $(document).ready(function () {
        handleSelectedClass(100);
    });
    $(document).on('click', 'button.action-remove', function() {
        handleSelectedClass(100, true);
    })
});
