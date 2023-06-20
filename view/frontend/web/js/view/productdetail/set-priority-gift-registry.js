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
        if(data.product_type === 'simple'){
            self.gift_item_id = ko.observable(data.options[0].gift_item_id);
            self.priority = ko.observableArray([
                {
                    value: "0" + " " + self.gift_item_id(),
                    label: 'None'
                },
                {
                    value: "1" + " " + self.gift_item_id(),
                    label: 'Low'
                },
                {
                    value: "2" + " " + self.gift_item_id(),
                    label: 'Below Average'
                },
                {
                    value: "3" + " " + self.gift_item_id(),
                    label: 'Average'
                },
                {
                    value: "4" + " " + self.gift_item_id(),
                    label: 'Above Average'
                },
                {
                    value: "5" + " " + self.gift_item_id(),
                    label: 'High'
                },
            ]);
            self.selected_priority = ko.observable(data.options[0].priority.toString() + " " + self.gift_item_id());
        } else {
            self.option = ko.observableArray();
            var map = $.map(data.options,function (item) {
                return new Item(item);
            });
            self.option(map);
        }

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
        self.selected_priority = ko.observable(data.priority);
        self.priority = ko.observableArray([
            {
                value: 0,
                label: 'None'
            },
            {
                value: 1,
                label: 'Low'
            },
            {
                value: 2,
                label: 'Below Average'
            },
            {
                value: 3,
                label: 'Average'
            },
            {
                value: 4,
                label: 'Above Average'
            },
            {
                value: 5,
                label: 'High'
            },
        ]);
        var map = $.map(data.option,function (item) {
            return new Options(item);
        });
        self.option(map);
        return self;
    }
    return Component.extend({
        defaults:{
            options: {},
            template: 'Magenest_GiftRegistry/productdetail/setpriority',
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
            this.priority_value = ko.observable();
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
                $("#set-priority-giftregistry-table").show();
                $('.giftregistry-close').click(function () {
                    $("#set-priority-giftregistry-table").hide();
                });
            };
            self.setPriority = function () {
                var giftRegistryId = '';
                var radios = document.getElementsByName('event_type');
                for (var i = 0, length = radios.length; i < length; i++) {
                    if (radios[i].checked) {
                        giftRegistryId = radios[i].value;
                        break;
                    }
                }
                var gift_item_data = [];
                if(giftRegistryId != ''){
                    var checkbox = $('input[name="gift_item['+giftRegistryId+'][]"]');
                    if (checkbox.length <= 0) {
                        var chose = 'select[name="gr_priority['+giftRegistryId+'][]"]';
                        var combination = $(chose).find("option:selected").val().split(" ");
                        var priority = combination[0];
                        var giftItemId = combination[1];
                        var arr = {
                            'gift_item':giftItemId,
                            'priority': priority
                        };
                        gift_item_data.push(arr);
                    } else {
                        for (var i = 0; i < checkbox.length; i++){
                            if(checkbox[i].checked){
                                var giftItemId = checkbox[i].value;
                                var chose = 'select[name="gr_priority['+giftItemId+'][]"]';
                                var priority = $(chose).find("option:selected").val();
                                var arr = {
                                    'gift_item':giftItemId,
                                    'priority': priority
                                };
                                gift_item_data.push(arr);
                            }
                        }
                    }
                    var product_id = document.getElementById('giftregistry_product_id').value;
                    var tmp_postData = {
                        'gift_id': giftRegistryId,
                        'gift_item_data': gift_item_data,
                        'product_id': product_id
                    };
                    var tmp_action = url.build("gift_registry/index/priority");
                    var loader = $('body');
                    loader.loader('show');
                    var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                        window.location.reload();
                    });
                }else{
                    var message = $("<div>Please choose a Gift Registry!</div>");
                    $("#giftregistry-priority-error").append(message);
                }
            };
            return this;
        }
    });
});
