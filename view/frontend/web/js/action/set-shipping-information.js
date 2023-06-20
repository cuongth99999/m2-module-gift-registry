define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
],function ($, wrapper, quote) {
    'use strict';
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            var message = $('[name="gr-guest-message"]').val();
            var checkbox = $('[name="checkbox"]').val();
            shippingAddress['extension_attributes'] = {'giftregistry_message':message, 'giftregistry_checkbox':checkbox};
            return originalAction();
        })
    }
});
