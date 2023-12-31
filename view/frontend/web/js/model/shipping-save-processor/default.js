define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/shipping-save-processor/payload-extender'
], function (
    $,
    _,
    ko,
    quote,
    resourceUrlManager,
    storage,
    paymentService,
    methodConverter,
    errorProcessor,
    fullScreenLoader,
    selectBillingAddressAction,
    payloadExtender
) {
    'use strict';
    return {
        /**
         * @return {jQuery.Deferred}
         */
        saveShippingInformation: function () {
            var payload;

            if (!quote.billingAddress() && quote.shippingAddress().canUseForBilling()) {
                selectBillingAddressAction(quote.shippingAddress());
            }

            payload = {
                addressInformation: {
                    'shipping_address': quote.shippingAddress(),
                    'billing_address': quote.billingAddress(),
                    'shipping_method_code': quote.shippingMethod()['method_code'],
                    'shipping_carrier_code': quote.shippingMethod()['carrier_code']
                }
            };

            if (window.checkoutConfig.quoteData.work_id !== null) {
                payload.addressInformation.extension_attributes = {
                    work_id: window.checkoutConfig.quoteData.work_id
                }
            }

            // this.extendPayload(payload);

            fullScreenLoader.startLoader();

            return storage.post(
                resourceUrlManager.getUrlForSetShippingInformation(quote),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    quote.setTotals(response.totals);
                    paymentService.setPaymentMethods(methodConverter(response['payment_methods']));
                    fullScreenLoader.stopLoader();
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    fullScreenLoader.stopLoader();
                }
            );
        },
        extendPayload: function (payload) {
            quote.giftregistry = [];
            quote.giftregistry.message = $('[name="gr-guest-message"]').val();
            quote.giftregistry.checkbox = $('[name="checkbox"]').val();
            quote.giftregistry.sender = $('[name="sender"]').val();

            var giftregistryData = {
                giftregistry_message: quote.giftregistry.message,
                giftregistry_checkbox: quote.giftregistry.checkbox,
                giftregistry_sender: quote.giftregistry.sender
            };

            if (!payload.addressInformation.hasOwnProperty('extension_attributes')) {
                payload.addressInformation.extension_attributes = {};
            }

            payload.addressInformation.extension_attributes = _.extend(
                payload.addressInformation.extension_attributes,
                giftregistryData
            );
        }
    }
});
