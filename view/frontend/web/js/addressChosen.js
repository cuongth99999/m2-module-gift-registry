define([
    "jquery",
    "uiClass",
    'Magento_Ui/js/lib/spinner',
    "Magento_Ui/js/modal/modal",
    "Magenest_GiftRegistry/js/addressChosen",
    "underscore"
], function ($, Class,loader,modal, addressChosen, _) {
    "use strict";
    return Class.extend({
        defaults: {
            /**
             * Initialized solutions
             */
            updateOption: true,
            getAddressUrl:'gift_registry/customer/address',
            config : {
                updateAddress :true
            }
        },
        /**
         * Constructor
         */
        initialize: function (config) {
            var self = this;
            this.initConfig(config);

            this.bindAction(self);

        },
        bindAction:function(self) {
            self._addressSelect(self);

        },

        updateSelector: function(self) {
            var activeSelector;

            var existInOption = false;

            var existingOption;

            //get all the address in shipping address
            $.ajax({
                url : self.getAddressUrl,
                async: false,
                showLoader: true

            }).done(function (data) {
                if (data.length > 0) {

                    var newOptionHtml ;
                    var i = 0;
                    for ( i; i < data.length; i++)  {
                        jQuery('select[data-roles="shipping-add"]').each(function() {
                            newOptionHtml = newOptionHtml + '<option value="' + data[i]['id'] + '">' + data[i]['label'] + '</option>';

                            activeSelector = this;
                             var oldVal =  $(activeSelector).val();
                            $(activeSelector).html(newOptionHtml);
                            $(activeSelector).val(oldVal);
                        }) ;
                    }
                    newOptionHtml = newOptionHtml + '<option value="new" data-action="add-new-shipping-address">Add new address</option>';
                    $(activeSelector).html(newOptionHtml);
                    $("#shipping_address").val(data[i-1]['id']);
                }

            });


        },
        _addressSelect:function(self) {

            $('select[data-action="add-new-shipping-address"]').change (function() {
                var value  = $(this).val();

                if (value =='new') {
                    this.modal = $('[data-role="wrapper-modal-new-email"]').modal({
                        modalClass: 'modal-followup-email-slide',
                        type: 'slide',
                        transitionEvent : false,
                        trigger:['closed'],
                        buttons: [],
                        closed:function () {
                            self.updateSelector(self);
                        }
                    });

                    this.modal.modal('openModal');
                }
                if(value!="" && value!="new"){
                    // can show information of address

                }

            });
        }
    })
});
