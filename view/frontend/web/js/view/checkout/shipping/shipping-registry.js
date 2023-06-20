/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'jquery',
    'ko',
    'mageUtils',
    'uiComponent',
    'uiLayout',
    'Magento_Checkout/js/view/shipping',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/checkout-data',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/model/messageList',
    'mage/storage',
], function ($, ko, utils, Component, layout,
             $shipping, createShippingAddress, selectShippingAddress,
             setShippingInformationAction,
             checkoutData,
             url,
             quote,
             messageList,
             storage) {
    'use strict';
    var defaultRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'Magento_Checkout/js/view/shipping-information/address-renderer/default'
    };

    return Component.extend({

        defaults: {
            template: 'Magenest_GiftRegistry/shipping-information/registry',
            rendererTemplates: {}
        },
        /**
         * Extends instance with default config, calls initialize of parent
         * class, calls initChildren method.
         */

        /**
         * Initializes model instance.
         *
         * @returns {Element} Chainable.
         */
        initialize: function () {
            this._super()
                .initObservable()
                .initModules()
                .initStatefull()
                .initLinks()
                .initUnique()

            ;

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
                self.addressObj = registryAddressInfo.registryAddress;
                self.isForRegistry(registryAddressInfo.is_for_registry);
                self.registryId(registryAddressInfo.registryId);
                if(registryAddressInfo.registryAddressId !== null){
                    self.ishaveAddress(true);
                }
            });

            return this;
        },

        initializes: function () {
            var self = this;
            storage.post(
                url.build('rest/V1/magenest/giftregistry/shipping'),
            ).done(function (response) {
                var data = JSON.parse(response);
                var registryAddressInfo = data.registryInCart;
                self.addressObj = registryAddressInfo.registryAddress;
            });
            return this;
        },

        openPopUp: function () {
            var self = this;
            if(self.ishaveAddress() == false){
                messageList.addErrorMessage({ message: 'Unable to find your friend address.' });
                return this;
            }
            var addressData = this.addressObj;
            var newShippingAddress = createShippingAddress(addressData);
            newShippingAddress.regionId = addressData.regionId;
            newShippingAddress.regionCode = addressData.regionCode;
            newShippingAddress.email = addressData.email;
            selectShippingAddress(newShippingAddress);
            checkoutData.setNewCustomerShippingAddress(addressData);
            checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());

            $("input[name$='firstname']").each(function (i, el) {
                $(this).val(addressData.firstname).change();
                //It'll be an array of elements
            });

            $('input[name="lastname"]').val(addressData.lastname).change();
            $('input[name="company"]').val(addressData.company).change();
            $('input[name="city"]').val(addressData.city).change();
            $('select[name="country_id"]').val(addressData.countryId).change();
            $('select[name="region_id"] option').each(function () {
                if ($(this).val() == addressData.regionId) {
                    $(this).attr("selected", "selected").change();
                }
            })
            $('input[name="region"]').val(addressData.region).change();
            $('input[name="region_code"]').val(addressData.regionCode).change();
            $('input[name="telephone"]').val(addressData.telephone).change();
            $('input[name="postcode"]').val(addressData.postcode).change();
            $('#shipping-save-in-address-book').prop("checked", false).change();

            var street0 = addressData.street[0];
            var street1 = addressData.street[1];

            $("input[name*='street']").each(function (i, el) {
                //It'll be an array of elements
                if (i == 0) $(this).val(street0).change();
                if (i == 1) $(this).val(street1).change();
            });
            return this;
        }
    });
});
