define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'mage/url'
],function ($, ko, Component, quote, url) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Magenest_GiftRegistry/shipping-information/messageinfo',
        },
        getGRGuestMessage: function() {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] !== undefined) {
                var extensionAttributes = shippingAddress['extension_attributes'];
                if(extensionAttributes.giftregistry_message !== undefined && extensionAttributes.giftregistry_message !== ""){
                    var message = extensionAttributes.giftregistry_message;
                    return message;
                }
            }
            return false;
        },
        isModuleEnabled: function () {
            this.isForRegistry = ko.observable(true);
            return this;
        }
    });
});
