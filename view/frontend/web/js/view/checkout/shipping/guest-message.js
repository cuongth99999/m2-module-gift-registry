define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Checkout/js/model/quote',
    'ko',
    'mage/url',
    'mage/storage',
    'mage/calendar'
],function ($, Component, quote, ko, url,storage) {
    'use strict';

    return Component.extend({
        defaults:{
            formSelector: '#checkout-step-shipping_method button',
            template: 'Magenest_GiftRegistry/shipping-information/message',
        },
        initialize: function () {
            this._super().initObservable();
            return this;
        },
        /**
         * Initializes observable properties.
         *
         * @returns {Element} Chainable.
         */
        initObservable: function () {
            var self = this;
            this.isForRegistry = ko.observable(true);
            this.registryId = ko.observable(1);
            this.ishaveAddress = ko.observable(false);
            storage.post(
                url.build('rest/V1/magenest/giftregistry/shipping'),
            ).done(function (response) {
                var data = JSON.parse(response);
                var registryAddressInfo = data.registryInCart;
                self.isForRegistry(registryAddressInfo.is_for_registry);
                self.registryId(registryAddressInfo.registryId);
                if(registryAddressInfo.registryAddressId !== null){
                    self.ishaveAddress(true);
                }
            });
            return this;
        }
    });
});
