define(
    [
        'jquery',
        "Magento_Customer/js/customer-data",
        'ko',
        'mage/url',
        'uiComponent'
    ],
    function ($, Customer, ko, url, Component) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                this.giftregistry = ko.observable();
                this.customer = Customer.get('customer');
                var link = url.build('gift_registry/index/GiftRegistryCustomer');
                var param = [];
                return this;

            },

            checkGiftRegistry: function () {
                // return this.giftregistry.check;
                return true;
            },

            getGiftRegistry: function(){
                var param = [];
                $.ajax({
                    showLoader: true,
                    url: link,
                    data: param,
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    return data;
                });
            }
        });

    }
);
