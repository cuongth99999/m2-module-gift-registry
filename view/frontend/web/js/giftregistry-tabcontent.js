define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/url',
    'mage/calendar',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
],function ($, Component, ko, url, calendar, alert, $t) {
    "use strict";

    function Item(data) {
        var self = this;
        var desired_qty = data.qty;
        var received_qty = data.received_qty;
        var qty = desired_qty - received_qty;
        if (qty < 0) qty = 0;
        var i;
        var array = [];
        for (i = 1; i <= qty; i++) {
            array.push(i);
            self.option = ko.observableArray(array);
        }
        self.gift_item_id = ko.observable(data.gift_item_id);
        self.product_name = ko.observable(data.product_name);
        self.price = ko.observable(data.price);
        self.qty = ko.observable(qty);
        self.priority = ko.observable(data.priority);
        self.duplicate = ko.observable(data.duplicate);
        self.productImage = ko.observable(data.productImage)
        self.displayQtyMode = ko.observable(data.displayQtyMode);
        self.urlProduct = ko.observable(data.urlProduct);
        self.size = ko.observable(data.size);
        self.color = ko.observable(data.color);

        //Strat Rating
        self.starRate = [];
        for (i = 1; i <= 5; i++) {
            if(i <= data.priority){
                self.starRate.push({starSelected: 'selected'});
            } else {
                self.starRate.push({starSelected: ''});
            }
        }
        //End Rating

        //Click Add To Cart
        self.addToCart = function () {
            var idItem = data.gift_item_id;
            var productId = data.product_id;
            addToCart(idItem, productId);
        };
        return self;
    }

    function addToCart(idItem, productId) {
        var urtCart = url.build("gift_registry/index/addToCart");
        $.ajax({
            type: "POST",
            url: urtCart,
            data: {
                id: idItem,
                productId: productId
            },
            showLoader: true
        }).done(function (response) {
            var hasaddress = response['hasAddress'];
            var stockItem = response['stockItem'];
            if (hasaddress == 0) {
                alert({
                    content: $('#popupAddress').html()
                });
            } else {
                var id = idItem;
                var qty_max = stockItem;
                var qty = $("#" + idItem).attr("value");

                if(parseInt(qty) != qty){
                    alert({
                        content: $t("Quantity Must Be An Integer!")
                    });
                    return;
                }
                if(parseInt(qty) && parseInt(qty)>0){
                    if (qty > qty_max) {
                        alert({
                            content: $t("We don't have enough product stock as you requested!")
                        });
                    } else {
                        $('#popupQty').hide();
                        var addUrl = url.build("gift_registry/cart/add");
                        var formKey = '';
                        var check = true;
                        if (check == true) {
                            $('body').loader('show');
                            $.post(addUrl, {item: id, qty:qty , formKey :formKey}).done(function(respone) {
                                alert({
                                    content: respone.message
                                });
                                $('body').loader('hide');
                            });
                        }
                    }
                } else {
                    alert({
                        content: $t("Please enter a number greater than or equal to 1 in Qty field!")
                    });
                }
            }
        });
    }

    return Component.extend({
        defaults:{
            config: {},
            template: 'Magenest_GiftRegistry/seach',
        },

        initialize: function () {
            this._super().initObservable();
            return this;
        },

        initObservable: function () {
            var self = this;
            var data = self.data;
            this.giftregistries = ko.observableArray();
            var map = $.map(data,function (data) {
                return new Item(data);
            });
            self.giftregistries(map);

            self.clickSort = function () {
                var sort = $('.sorting').val();
                var value = $('.value').val();

                var action = url.build("gift_registry/index/sortData");
                var pathInfo = window.location.href;

                $.ajax({
                    type: "POST",
                    url: action,
                    data: {
                        sort: sort,
                        value: value,
                        pathInfo: pathInfo
                    },
                    showLoader: true
                }).done(function (response) {
                    var registryAddressInfo = response;
                    this.giftregistries = ko.observableArray();
                    var map = $.map(registryAddressInfo, function (data) {
                        return new Item(data);
                    });
                    self.giftregistries(map);
                });
            };
            return self;
        }
    });
});
