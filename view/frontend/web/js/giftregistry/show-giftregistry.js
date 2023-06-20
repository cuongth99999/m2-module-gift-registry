define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/modal',
    'Magenest_GiftRegistry/js/owl.carousel',
    'Magenest_GiftRegistry/js/addToCart'
],function ($) {
    'use strict';

    return function (config) {
        $('#redirect_login').click(function () {
            $.ajax ({
                type : "POST",
                url : config.actionUrl.registry,
                showLoader : true,
                data : {
                    request : 'registry_login',
                    type : config.eventType,
                    registryId : config.registryId
                },
                success : function () {
                    window.location.href = config.actionUrl.customerLogin;
                }
            });
        });
        $('#filter-selected').on('change', function (e) {
            var optionSelected = $("option:selected", this);
            if(this.value == 1) {
                var row = '<div class="field">' +
                    '   <input id="title" name="t" class="search_input" data-validate="{required:true}" type="text" placeholder="Title">' +
                    '</div>';
                $("#search-title-wrapper").append(row);
                $("#search-name-wrapper").empty();
            }else {
                var row = '<div class="field">' +
                    '            <input id="event_fn" name="fn" class="search_input" data-validate="{required:true}" type="text" placeholder="First Name">' +
                    '         </div>' +
                    '         <div class="field">' +
                    '            <input id="event_ln" name="ln" class="search_input" data-validate="{required:true}" type="text" placeholder="Last Name">' +
                    '         </div>';
                $("#search-name-wrapper").append(row);
                // $("#search-name-wrapper").show();
                $("#search-title-wrapper").empty();
            }
        });
        $('#search_registry_submit_btn').click(function () {
            $('#gift_search_form').submit();
        });
        $(".gift-type-description").text(function(index, currentText) {
            return currentText.substr(0, 150);
        });
    }
});