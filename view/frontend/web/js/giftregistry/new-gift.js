define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "Magenest_GiftRegistry/js/addressChosen",
    "domReady!",
    "underscore",
    "prototype",
    "Magento_Ui/js/lib/spinner",
    "mage/calendar"
],function ($, modal, addressChosen, domReady, _, prototype) {
    'use strict';
    return function (config) {
        var dateToday = new Date();
        $('input[data-role="date-picker"]').calendar({ dateFormat: 'mm-dd-yy' });
        $(function() {
            $( "#datepicker" ).datepicker({
                numberOfMonths: 1,
                showButtonPanel: true,
                dateFormat: 'dd-mm-yy',
                minDate: dateToday
            });
        });

        // var addressChosen = new addressChosen({ updateOption:true,getAddressUrl:config.address});
        $(document).ready(function() {
            $('select[data-role="defined-selected"]').each(function() {
                var realValue = $(this).data('myvalue');
                $(this).val(realValue);
            }) ;
        })
        $('select[data-role="defined-selected"]').change(function() {
            var primacy = $('option:selected' , this).val();
            if(primacy == 'private')
            {
                var row1 = '<label class="label required">Password</label><input autocomplete="off" type="password" data-validate="{required:true, \'validate-password\':true}" id="password"'+
                            'class="input-text" title="Password" value="" name="password" placeholder="Password">';
                $("#giftregistry-password").append(row1);
                var row2 = '<label class="label required">Confirm Password</label><input autocomplete="off" type="password" data-validate="{equalTo:\'#password\'}" id="re_password"'+
                    'class="input-text" title="Confirm Password" value=""'+
                    'name="re_password" placeholder="Confirm Password">'+
                    '<label style="color:red;" hidden id="check_password_label" class="label">'+
                    'Password and repassword must match!</label>';
                $("#giftregistry-re-password").append(row2);
            }else {
                if(primacy == 'public' || primacy == ''){
                    $("#giftregistry-re-password").empty();
                    $("#giftregistry-password").empty();
                }
            }
        });
        $('#re_password').keyup(function () {
            if($('#re_password').val() != $('#password').val()) {
                $('#check_password_label').show();
            } else {
                $('#check_password_label').hide();
            }
        });
        $('#btn_submit_gift').click(function () {
            var form = $('#giftregistry_add_form');
            form.validate();
            $('#gift-date').empty();
            // $('#check_password_label').hide();
            if($('#re_password').val() != $('#password').val()) {
                // $('#check_password_label').show();
            }
            if (form.valid()) {
                $('#giftregistry_add_form').submit();
            }
        });
        //Shipping Address
        function updateSelector() {
            var activeSelector;
            var existInOption = false;
            var existingOption;
            //get all the address in shipping address
            $.ajax({
                url : config.address_url,
                showLoader: true,
                data: {
                    "type" : true
                },
                type: "POST",
                dataType: "json"
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
        }
        $('select[data-action="add-new-shipping-address"]').change (function() {
            var value  = $(this).val();
            if (value =='new') {
                var options = {
                    type: 'slide',
                    responsive: true,
                    innerScroll: true,
                    buttons: [{
                        click: function () {
                            this.closeModal();
                        }
                    }]
                };
                this.modal = $('[data-role="wrapper-modal-new-email"]').modal(options);
                this.modal.modal('openModal');
            }
            if(value!="" && value!="new"){
                // can show information of address
            }
        });
        $('span[data-role="loading-for-address"]').hide();
        $('span[data-role="result-add-address"]').hide();
        $('#save-address').click(function(event) {
            /* stop form from submitting normally */
            event.preventDefault();
            var form = $('#form-address');
            form.validate();
            if (form.valid()) {
                var $form = $('#form-address'),
                    url = $form.attr( 'action' );

                //show the loading
                $('span[data-role="loading-for-address"]').show();

                /* Send the data using post */
                var posting = $.post( url,  $form.serialize()  );

                /* Alerts the results */
                posting.done(function( data ) {
                    $('span[data-role="loading-for-address"]').hide();
                    updateSelector();
                    $('[data-role="closeBtn"]').click();
                    // $(button).click();
                });
            }
        });
    }
});
