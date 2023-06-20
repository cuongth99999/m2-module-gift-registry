define([
    "jquery",
    "ko",
    "uiClass",
    "Magento_Ui/js/modal/modal",
    "underscore",
    "validation",
],function ($, ko, Class, modal, _) {
    function Model(data) {
        var self = this;
        if(data.id.slice(0, 6) === 'field_') {
            self.id = ko.observable(data.id);
        }else{
            self.id = ko.observable('field_' + data.id);
        }
        self.type = ko.observable(data.type);
        self.label = ko.observable(data.label);
        self.group = ko.observable(data.group);
        self.require = ko.observable(data.require);
    }
    function TemplateModel(config) {
        var self = this;
        self.aboutEvent = ko.observableArray([]);
        // self.email_template = ko.observableArray([]);
        var about_event = config.about_event;

        var map = $.map(about_event, function (data) {
           if(data.id != "" || data.id != undefined){
               return new Model(data);
           }
        });
        self.aboutEvent(map);
        self.addNewElement = function () {
            var optionIds = $.map(self.aboutEvent(), function (template) {
                return template.id().substr(6);
            });
            var maxId = 0;
            if (optionIds != "" || optionIds.lengh > 0) {
                maxId = Math.max.apply(this, optionIds);
                maxId++;
            };
            self.aboutEvent.push(new Model({
                id: 'field_' + maxId,
                type: '',
                label: 'Title',
                group: '',
                require: '0'
            }));
        };
        self.deleteElement = function (aboutEvent) {
            ko.utils.arrayForEach(self.aboutEvent(), function (template) {
                if (aboutEvent.id() == template.id()) {
                    self.aboutEvent.destroy(template);
                }
            });
        };
    }
    return Class.extend({
        defaults: {
            template: "Magenest_GiftRegistry/eventtype/aboutevent"
        },
        initObservable: function () {
            this._super();
            return this;
        },
        initialize: function (config) {
            this._super;
            var self = this;
            this.initConfig(config);
            this.bindAction(self);
            return this;
        },
        bindAction: function (self) {
            var config = self;
            ko.cleanNode(document.getElementById("giftregistry_aboutevent"));
            ko.applyBindings(new TemplateModel(config), document.getElementById("giftregistry_aboutevent"));
        },
    });
});
