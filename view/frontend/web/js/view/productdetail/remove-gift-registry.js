define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/url',
    'mage/calendar'
],function ($, Component, ko, url) {
    "use strict";
    function GiftRegistry(data) {
        var self = this;
        self.gift_id = ko.observable(data.gift_id);
        self.title = ko.observable(data.title);
        self.product_type = ko.observable(data.product_type);
        self.option = ko.observableArray();
        var map = $.map(data.options,function (item) {
            return new Item(item);
        });
        self.option(map);
        return self;
    }
    function Options(data) {
        var self = this;
        self.lable = ko.observable(data.label);
        self.option_id = ko.observable(data.option_id);
        self.option_value = ko.observable(data.option_value);
        self.value = ko.observable(data.value);
        return self;
    }
    function Item(data) {
        var self = this;
        self.gift_item_id = ko.observable(data.gift_item_id);
        self.option = ko.observableArray();
        var map = $.map(data.option,function (item) {
            return new Options(item);
        });
        self.option(map);
        return self;
    }
    return Component.extend({
        defaults:{
            options: {},
            template: 'Magenest_GiftRegistry/productdetail/removebutton',
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
            var registryUrl = self.options.check_item_url;
            this.isshow = ko.observable(false);
            this.giftregistries = ko.observableArray();
            this.type = ko.observable();
            $.ajax(registryUrl).done(function (response) {
                var registryAddressInfo = response;
                self.isshow(registryAddressInfo.isShow);
                if(registryAddressInfo.isShow){
                    var map = $.map(registryAddressInfo.data, function (data) {
                        return new GiftRegistry(data);
                    });
                    self.giftregistries(map);
                    self.type(registryAddressInfo.product_type);
                }
            });
            self.showPopup = function () {
                $("#remove-giftregistry-table").show();
                $('.giftregistry-close').click(function () {
                    $("#remove-giftregistry-table").hide();
                });
            };
            self.removeItem = function () {
                var giftRegistryId = '';
                var radios = document.getElementsByName('event_type');
                for (var i = 0, length = radios.length; i < length; i++) {
                    if (radios[i].checked) {
                        giftRegistryId = radios[i].value;
                        break;
                    }
                }
                var gift_item = [];
                if(giftRegistryId != ''){
                    var checkbox = $('input[name="gift_item['+giftRegistryId+'][]"]');
                    for (var i = 0; i < checkbox.length; i++){
                        if(checkbox[i].checked){
                            gift_item.push(checkbox[i].value);
                        }
                    }
                    var product_id = document.getElementById('giftregistry_product_id').value;
                    var tmp_postData = {
                        'gift_id': giftRegistryId,
                        'gift_item': gift_item,
                        'product_id': product_id
                    };

                    var tmp_action = url.build("gift_registry/index/remove");
                    var loader = $('body');
                    loader.loader('show');
                    var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                        window.location.reload();
                    });
                }else{
                    var message = $("<div>Please choose a Gift Registry!</div>");
                    $("#giftregistry-remove-error").append(message);
                }

            };
            return this;
        }
    });
});
