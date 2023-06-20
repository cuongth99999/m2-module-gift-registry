define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore'
],function ($, Component, ko, _) {
    "use strict";

    return Component.extend({
        defaults:{
            config: {},
            template: 'Magenest_GiftRegistry/quickorder',
            divListItem: ko.observable(false)
        },

        initialize: function () {
            var self = this;
            self._super().initObservable();

            $('body').on('keyup', '#myInput', _.debounce(function () {
                self.getProductCollection();
            }, 1000));

            return this;
        },

        initObservable: function () {
            var self = this;
            this.productData = ko.observableArray();
            return self;
        },

        getData: function()
        {
            return this.productData;
        },

        getProductCollection: function () {
            var self = this,
                char = $('#myInput').val(),
                addToCart = self.addToCart;

            if (char.length >= 3) {
                var giftId = self.giftId;
                $.ajax({
                    url: self.url,
                    data: {
                        textInput: char,
                        giftId: giftId
                    },
                    showLoader: true,
                    type: "GET",
                    dataType: "json"
                }).done(function (response) {
                    if (response.product_data.length > 0) {
                        var registryAddressInfo = response.product_data;
                        var map = $.map(registryAddressInfo, function (data) {
                            return new Item(data, addToCart);
                        });
                        self.productData(map);
                        self.divListItem(true);
                    } else {
                        self.divListItem(false);
                    }
                    return self;
                });
            } else {
                self.divListItem(false);
            }
        },
    });

    function Item(data, addToCart) {
        var self = this;
        self.name = ko.observable(data.name);
        self.price = ko.observable(data.price);
        self.product_url = ko.observable(data.product_url);
        self.sku = ko.observable(data.sku);
        self.product_img = ko.observable(data.product_img);
        self.pathname = window.location.pathname.concat('/add/', data.entity_id);
        return self;
    }
});
