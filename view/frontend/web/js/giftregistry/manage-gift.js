define([
    "jquery",
    "Magento_Ui/js/modal/confirm",
    "Magento_Ui/js/modal/modal",
    "mage/calendar",
    "domReady!",
    "jquery/ui",
    "mage/mage",
    "mage/translate"
],function ($,confirmation,domReady) {
    'use strict';
    return function (config) {
        //Form edit information: add address, calendar
        var dateToday = new Date();
        var mbtob = 1048576;
        $('input[data-role="date-picker"]').calendar({ dateFormat: 'mm-dd-yy' });
        $(function() {
            $( "#datepicker" ).datepicker({
                numberOfMonths: 1,
                showButtonPanel: true,
                dateFormat: 'dd-mm-yy',
                minDate: dateToday
            });
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
                        $('select[data-roles="shipping-add"]').each(function() {
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
                    window.location.reload();
                    $('[data-role="closeBtn"]').click();
                    // $(button).click();
                });
            }
        });

        $(document).ready(function () {
            $('select[data-role="defined-selected"]').each(function () {
                var realValue = $(this).data('myvalue');
                $(this).val(realValue);
            });
        });

        //Delete registry
        $('#delete-registry').on('click', function (event) {
            event.preventDefault;
            confirmation({
                title: $.mage.__('Are You Sure'),
                content: $.mage.__('Your gift will be delete.'),
                actions: {
                    confirm: function () {
                        var gift_id = $('#delete-registry').data('giftid');
                        var url = $('#delete-registry').data('url');
                        var list_gift_url = $("#list_gift_url").val();
                        $.ajax(
                            {
                                type: "POST",
                                url: url,
                                data: {
                                    gift_id: gift_id
                                },
                                showLoader: true,
                                success: function (response) {
                                    window.location.href = list_gift_url;
                                },

                            });
                    },
                    cancel: function () {
                        return false;
                    },
                    always: function () {
                    }
                }
            });
        });

        var dataForm = $('#registry-infor-form');
        dataForm.mage('validation', {});

        var privacySelectInput = $("#privacy-select-input");
        var changePasswordBtn = $("#change-password-btn");

        privacySelectInput.change(function () {
            if(this.value == 'public'){
                changePasswordBtn.hide(100);
                $('.password-field').hide(100);
            }else {
                changePasswordBtn.show(100);
            }
        });

        /////////////////
        $('.close').click(function () {
            $('#popupLogin').hide();
        });
        $("#url-share").click(function () {
            $("#popupLogin").show();
        });
        $("#btn-copy").click(function() {
            $('#url-share-txt').select();
            document.execCommand("copy");
            $('#url-share-txt').blur();
            document.getElementById("rl-noti-copied").style.display = "block";
        });
        $("#url-share-txt").click(function() {
            $('#url-share-txt').select();
            document.execCommand("copy");
            $('#url-share-txt').blur();
            document.getElementById("rl-noti-copied").style.display = "block";
        });

        $('div[data-click="0"]').css('color', 'white');
        $('div[data-click="0"]').css('background-color', '#333333');

        //Click page order list
        $('div[data-function="tab"]').click(function () {
            var page = $(this).data('click');
            $('div[data-function="tab"]').each(function () {
                if ($(this).data('click') == page) {
                    $(this).css('color', 'white');
                    $(this).css('background-color', '#333333');
                } else {
                    $(this).css('color', '#333333');
                    $(this).css('background-color', 'white');
                }
            });
            $('tr[data-function="page"]').each(function () {
                if ($(this).data('page') == page) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        //Update item list
        $('#btnUpdate').on('click', function () {
            $('#list_item_form').validate();
            $('#list_item_form').valid();
            $('#list_item_form').submit();
        });

        //Save information button click
        $('#save_btn').click(function () {
            var banner = $('#giftregistry-banner').val(),
                location = $('#giftregistry-location').val(),
                joinus = $('#giftregistry-joinus').val();
            if(banner != '' ){
                var ext_banner = banner.split(".");
                ext_banner = ext_banner[ext_banner.length - 1].toLowerCase();
                var arrayExtensions = ["jpg", "jpeg", "png"];
                if (arrayExtensions.lastIndexOf(ext_banner) == -1) {
                    showNoti(config.error_type_2);
                }else {
                    var input = document.getElementById('giftregistry-banner').files[0];
                    if(input && input.size > (config.max_upload_size*mbtob)){
                        var message = $.mage.__("Make sure your file isn't more than {size}M.").replace('{size}', config.max_upload_size);
                        showNoti(message);
                    }else{
                        submitForm();
                    }
                }
            } else if(location != ''){
                var ext_location = location.split(".");
                ext_location = ext_location[ext_location.length - 1].toLowerCase();
                var arrayExtensions = ["jpg", "jpeg", "png"];
                if (arrayExtensions.lastIndexOf(ext_location) == -1) {
                    showNoti(config.error_type_2);
                }else{
                    var input = document.getElementById('giftregistry-location').files[0];
                    if(input && input.size > (config.max_upload_size*mbtob)){
                        var message = $.mage.__("Make sure your file isn't more than {size}M.").replace('{size}', config.max_upload_size);
                        showNoti(message);
                    }else {
                        submitForm();
                    }
                }
            } else if(joinus != ''){
                var ext_joinus = joinus.split(".");
                ext_joinus = ext_joinus[ext_joinus.length - 1].toLowerCase();
                var arrayExtensions = ["jpg", "jpeg", "png"];
                if (arrayExtensions.lastIndexOf(ext_joinus) == -1) {
                    showNoti(config.error_type_2);
                }else{
                    var input = document.getElementById('giftregistry-joinus').files[0];
                    if(input && input.size > (config.max_upload_size*mbtob)){
                        var message = $.mage.__("Make sure your file isn't more than {size}M.").replace('{size}', config.max_upload_size);
                        showNoti(message);
                    }else {
                        submitForm();
                    }
                }
            } else {
                submitForm();
            }
        });
        function showNoti(message) {
            $('#error-type-file').empty();
            $('#error-type-file').append(message);
            $("#image").val("");
            $('#error-type-file').show();
        }
        function submitForm(){
            $('#error-type-file').empty();
            if ($('#re_password').val() != $('#password').val() && $(".password-field").css('display') !== 'none') {
                $('#check_password_label').show();
            } else {
                var form = $('#registry-infor-form');
                form.validate();
                if (form.valid()) {
                    $('#registry-infor-form').submit();
                }
            }
        }


        //Check enter right password
        $('#re_password').keyup(function () {
            if ($('#re_password').val() != $('#password').val()) {
                $('#check_password_label').show();
            } else {
                $('#check_password_label').hide();
            }
        });
        $('#change_pass').click(function () {
            $('.password-field').show();
            $('#change-password-btn').hide();
        });

        function showLoader() {
            $('#loader_loadpage').hide();
        }

        $('#change-background-btn').click(function () {
            $('#change-background').toggle();
        });
        //Popup confirm delete

        //Add items
        $("#btnAddItems").click(function(){
            confirmation({
                title: $.mage.__('Add Items'),
                content: $.mage.__('To add a product to your registry, open the product page and click the "Add To Gift Registry" button.'),
                actions: {
                    confirm: function () {
                        window.location.href = $("#btnAddItems").attr('post-url');
                    },
                    cancel: function () {
                        return false;
                    },
                    always: function () {
                    }
                }
            });

        });
        //Delete all item in item-list
        $("#btnDellAll").click(function () {
            var count = 0;
            $(".checkdelete").each(function () {
                if ($(this).is(":checked")) {
                    count++;
                }});
            if(count>0) {
                $('#confirmDelete').show();
                $('#okDelete').click(function () {
                    $('#confirmDelete').hide();
                    var listDelete = new Array();
                    var action = $("#btnDellAll").attr('post-url');
                    var idGift = $("#btnDellAll").attr('idGift');
                    count = 0;
                    $(".checkdelete").each(function () {
                        if ($(this).is(":checked")) {
                            count++;
                            listDelete.push($(this).val());
                        }
                    });
                    if (count) {
                        var posting = $.post(action, {listdelete: listDelete, type: "massdelete", id: idGift});
                        $('body').loader('show');
                        posting.done(function (data) {
                            window.location.href = $(location).attr('href');
                            $('body').loader('hide');
                        });
                    } else {
                        if ($(".message-error")) {
                            $(".message-error").remove();
                            $(".message-success").remove();
                        }
                        var html = '<div  class="message-error error message" ><div>You must select item!!</div></div>';
                        $('.my_message').append(html);
                    }
                });
                $('#noDelete').click(function () {
                    $('#confirmDelete').hide();
                });
            }else {
                $('#noItemDelete').show();
                $('#ok').click(function () {
                    $('#noItemDelete').hide();
                });
            }
        });
        $('#select-delete').on('change', function () {
            if ($('#select-delete:checked').length > 0) {
                $(".checkdelete").attr('checked', true);

            } else {
                $(".checkdelete").attr('checked', false);
            }
        });

        $('#unselect').on('click', function () {
            $(".checkdelete").each(function () {
                if ($(this).is(":checked")) {
                    $(this).attr('checked', false);
                }
            })
        });

        //Share mail onclick
        $('#share-email').click(function () {
            $('.popup-share-email').show();
            $('#recipient').focus();
        });
        $('.share-email-close').click(function () {
            $('.popup-share-email').hide();
        });
        //Share registry via mail
        $('#form_email_submit_btn').click(function () {
                var recipient = $("#recipient").val();
                var email_subject = $("#email_subject").val();
                var message = $("#message").val();
                var formKey = $("#form-key").attr("value");
                var guestId = $("#guest-id").attr("value");
                var giftregistryId = $("#giftregistry-id").attr("value");
                var recipient_array = recipient.split(",");
                var check = false;
                var recipient_error = 'Email: ';
                for (var i = 0; i < recipient_array.length; i++) {
                    if (!validateEmail(recipient_array[i])) {
                        check = true;
                        recipient_error += recipient_array[i];
                        if (i < recipient_array.length -1){
                            recipient_error = " " + recipient_error + ", ";
                        }
                    }
                }
                if (check == false) {
                    $('#email-validate-error').hide();
                    $("#recipient").css('border', '');
                    $('body').loader('show');
                    var url = config.send_mail_url;
                    $.ajax(
                        {
                            type: "POST",
                            url: url,
                            data: {
                                recipient: recipient,
                                email_subject: email_subject,
                                message: message,
                                form_key: formKey,
                                guest_id: guestId,
                                giftregistryId: giftregistryId
                            },
                            showLoader: true,
                            success: function (response) {
                                window.location.href = $(location).attr('href');
                                $('body').loader('hide');
                                $('.popup-share-email').hide();
                                if (response['error']) {
                                    console.log("");
                                } else {
                                    window.location.reload();
                                }
                            }
                        }
                    );
                } else {
                    $('#email-validate-error').html("<p>" + "<span id='email_error'></span> is not validate. Please check!</p>");
                    $('#email_error').text(recipient_error);
                    $('#email-validate-error').show();
                    $("#recipient").css('border', 'solid 1px red');
                }
            }
        );
        //Validate mail
        function validateEmail(email) {
            var reg = /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/;
            if (reg.test(email) == false) {
                return false;
            }
            return true;
        }

        //Select action view page
        $('#select-action-view').on('change', function () {
            var action = $('#select-action-view option:checked').val();
            if ( action== 'view-guest-page') {
                window.location.href = config.data_view_giftregistry;
            }else {
                $('#change-background').show();
            }
        });
        $('#preview-registry').click(function () {
            var banner = $('#giftregistry-banner').val(),
                location = $('#giftregistry-location').val(),
                joinus = $('#giftregistry-joinus').val();
            if(banner != '' ){
                var ext_banner = banner.split(".");
                ext_banner = ext_banner[ext_banner.length - 1].toLowerCase();
                var arrayExtensions = ["jpg", "jpeg", "png"];
                if (arrayExtensions.lastIndexOf(ext_banner) == -1) {
                    showNoti(config.error_type_2);
                }else {
                    var input = document.getElementById('giftregistry-banner').files[0];
                    if(input && input.size > (config.max_upload_size*mbtob)){
                        var message = $.mage.__("Make sure your file isn't more than {size}M.").replace('{size}', config.max_upload_size);
                        showNoti(message);
                    }else{
                        submitPreview();
                    }
                }
            } else if(location != ''){
                var ext_location = location.split(".");
                ext_location = ext_location[ext_location.length - 1].toLowerCase();
                var arrayExtensions = ["jpg", "jpeg", "png"];
                if (arrayExtensions.lastIndexOf(ext_location) == -1) {
                    showNoti(config.error_type_2);
                }else{
                    var input = document.getElementById('giftregistry-location').files[0];
                    if(input && input.size > (config.max_upload_size*mbtob)){
                        var message = $.mage.__("Make sure your file isn't more than {size}M.").replace('{size}', config.max_upload_size);
                        showNoti(message);
                    }else {
                        submitPreview();
                    }
                }
            } else if(joinus != ''){
                var ext_joinus = joinus.split(".");
                ext_joinus = ext_joinus[ext_joinus.length - 1].toLowerCase();
                var arrayExtensions = ["jpg", "jpeg", "png"];
                if (arrayExtensions.lastIndexOf(ext_joinus) == -1) {
                    showNoti(config.error_type_2);
                }else{
                    var input = document.getElementById('giftregistry-joinus').files[0];
                    if(input && input.size > (config.max_upload_size*mbtob)){
                        var message = $.mage.__("Make sure your file isn't more than {size}M.").replace('{size}', config.max_upload_size);
                        showNoti(message);
                    }else {
                        submitPreview();
                    }
                }
            } else {
                submitPreview();
            }
        });
        function submitPreview() {
            var ckdata = CKEDITOR.instances['description'].getData();
            $("textarea#description").val(ckdata);
            var data = $("#registry-infor-form").get(0);
            $.ajax(
                {
                    type: "POST",
                    url: config.preview_url,
                    data: new FormData(data),
                    processData: false,
                    contentType: false,
                    showLoader: true,
                    success: function (response) {
                        if (response.error) {
                            console.log("");
                        } else {
                            $('body').loader('hide');
                            window.open(response.redirect_url , "_blank");
                            // window.location.href = response.redirect_url;
                        }
                    }
                }
            );
        }

        //Delete guest
        $("#btnDellAllGuest").click(function () {
            var count = 0;
            $(".checkdeleteguest").each(function () {
                if ($(this).is(":checked")) {
                    count++;
                }});
            if(count>0) {
                $('#confirmDeleteGuest').show();
                $('#okDeleteGuest').click(function () {
                    $('#confirmDeleteGuest').hide();
                    var listDelete = new Array();
                    var action = $("#btnDellAllGuest").attr('post-url');
                    var idGift = $("#btnDellAllGuest").attr('idGift');
                    count = 0;
                    $(".checkdeleteguest").each(function () {
                        if ($(this).is(":checked")) {
                            count++;
                            listDelete.push($(this).val());
                        }
                    });
                    if (count) {
                        var posting = $.post(action, {listdelete: listDelete, type: "massdelete", id: idGift});
                        $('body').loader('show');
                        posting.done(function (data) {
                            window.location.href = $(location).attr('href');
                            $('body').loader('hide');
                            window.location.reload();
                        });
                    } else {
                        if ($(".message-error")) {
                            $(".message-error").remove();
                            $(".message-success").remove();
                        }
                        var html = '<div  class="message-error error message" ><div>You must select guest!!</div></div>';
                        $('.my_message').append(html);
                    }
                });
                $('#noDeleteGuest').click(function () {
                    $('#confirmDeleteGuest').hide();
                });
            }else {
                $('#noItemDelete').show();
                $('#ok').click(function () {
                    $('#noItemDelete').hide();
                });
            }
        });
        $('#unselect').on('click', function () {
            $(".checkdeleteguest").each(function () {
                if ($(this).is(":checked")) {
                    $(this).attr('checked', false);
                }
            })
        });
        $('#select-delete-guest').on('change', function () {
            if ($('#select-delete-guest:checked').length > 0) {
                $(".checkdeleteguest").attr('checked', true);

            } else {
                $(".checkdeleteguest").attr('checked', false);
            }
        });
        //end delete guest

        $(document).ready(function(){
            $("#myInput").on("keyup", function() {
                $("#div").show();
                var value = $(this).val().toLowerCase();
                if (value == '') {
                    $('#div').hide();
                } else {
                    $("#myTable tr").filter(function () {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                }
            });
        });

        //Add rating
        $(document).ready(function(){
            $('#stars li').on('mouseover', function(){
                var priority = parseInt($(this).data('value'), 10); // The star currently mouse on
                $(this).parent().children('li.star').each(function(e){
                    if (e < priority) {
                        $(this).addClass('hover');
                    }
                    else {
                        $(this).removeClass('hover');
                    }
                });

            }).on('mouseout', function(){
                $(this).parent().children('li.star').each(function(e){
                    $(this).removeClass('hover');
                });
            });

            //
            $('#stars li').on('click', function(){
                var priority = parseInt($(this).data('value'), 10);
                var stars = $(this).parent().children('li.star');
                var giftItemId = parseInt($(this).data('action'), 10);
                var url = config.rating;
                var i = 0;
                for (i = 0; i < stars.length; i++) {
                    $(stars[i]).removeClass('selected');
                }

                for (i = 0; i < priority; i++) {
                    $(stars[i]).addClass('selected');
                }
                $.ajax(
                    {
                        type: "POST",
                        url: url,
                        data: {
                            giftItemId: giftItemId,
                            priority: priority,
                        },
                        showLoader: true,
                        success: function (response) {
                            window.location.href = $(location).attr('href');
                            if (response['error']) {
                                console.log("");
                            } else {
                                $('body').loader('hide');
                            }
                        }
                    }
                );
            });
        });

        //Start Send thank you emails
        $('.send-email').click(function () {
            $('.popup-share-email').show();
            $('#recipient').focus();
            var tranId = $(this).data('action');
            var recipient = $(this).data('email');
            var guestId = $(this).data('guestId');

            $('#submit_from_email').click(function () {
                var email_subject = $("#subject").val();
                var message = $("#message_email").val();
                var formKey = $("#form-key").attr("value");
                var recipient_array = recipient.split(",");
                var check = false;
                var recipient_error = 'Email:';
                for (var i = 0; i < recipient_array.length; i++) {
                    if (!validateEmail(recipient_array[i])) {
                        check = true;
                        recipient_error += recipient_array[i] + " ";
                    }
                }
                if (check == false) {
                    $('#email-validate-error').hide();
                    $("#email").css('border', '');
                    $('body').loader('show');
                    var url = config.send_mail_url;
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            recipient: recipient,
                            email_subject: email_subject,
                            message: message,
                            form_key: formKey,
                            guest_id: guestId,
                            tranId: tranId,
                        },
                        showLoader: true,
                        success: function (response) {
                            window.location.href = $(location).attr('href');
                            $('body').loader('hide');
                            $('.popup-share-email').hide();
                            if (response['error']) {
                                console.log("");
                            } else {
                                window.location.reload();
                            }
                        }
                    })
                }
            });
        });
        //End Send thank you emails
    }
});
