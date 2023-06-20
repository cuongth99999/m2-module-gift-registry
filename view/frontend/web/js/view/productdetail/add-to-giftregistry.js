define([
    "jquery",
    "ko",
    'mage/url',
    'mage/mage',
    'Magento_Catalog/product/view/validation',
    "domReady!",
    'mage/translate'
],function ($,ko,urlbuild, domReady,$t) {
    "use strict";
    return function (config) {
        var table = $("#add-giftregistry-table");
        $("#add-gift-link").on('click', function () {
            $('div[class="page messages"]').empty();
            var form = $('#product_addtocart_form');
            if(form.validate()){
                $.ajax({
                    showLoader: true,
                    url: config.actionAddUrl,
                    data: {
                        "type" : true
                    },
                    type: "POST",
                    dataType: "json"
                }).done(function (response) {
                    if(response.messageType){
                        var message = $('<div class="message-error message error" ><div>'+response.messageType+'</div></div>');
                        $('div[class="page messages"]').append(message);
                    }else{
                        if(response.showGift){
                            var dataGift = response.data;
                            dataGift.forEach(function (item, index) {
                                $('.list-gift').append("<div class='field-choose'><label> <input type='radio' value="+item['gift_id']+" name='event_type'>"+item['title']+"</label></div>")
                            });
                            table.show();
                        } else{
                            var tmp_postData = $('#product_addtocart_form').serialize();
                            var tmp_action = response.urlAdd;
                            var loader = $('body');
                            loader.loader('show');
                            var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                                window.location.reload();
                            });
                        }
                    }
                });
            }
            $('.giftregistry-close').click(function () {
                table.hide();
                $('.field-choose').remove();
            })
        });
        $("#add-gift-button").on('click', function () {
            $("#giftregistry-message-error").empty();
            var form = $('#product_addtocart_form');
            if(form.validate()){
                // var giftRegistryId = $('.list-gift').val();
                var tmp_postData = $('#product_addtocart_form').serialize();
                var giftRegistryId = '';
                var type = '';
                var radios = document.getElementsByName('event_type');
                for (var i = 0, length = radios.length; i < length; i++) {
                    if (radios[i].checked) {
                        giftRegistryId = radios[i].value;
                        break;
                    }
                }
                if(giftRegistryId == ''){
                    var message = $("<div>Please choose a Gift Registry!</div>");
                    $("#giftregistry-message-error").append(message);
                }else{
                    var tmp_action = urlbuild.build("gift_registry/index/add/giftregistry/"+giftRegistryId);
                    var loader = $('body');
                    loader.loader('show');
                    var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                        window.location.reload();
                    });
                }
            }
        });

        $("#remove-gift-link").on('click', function () {
            $("#remove-giftregistry-table").show();
            $('.giftregistry-close').click(function () {
                $("#remove-giftregistry-table").hide();
            });
        });
        $("#remove-gift-button").on('click',function () {
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
            }
            var product_id = document.getElementById('giftregistry_product_id').value;
            var tmp_postData = {
                'gift_id': giftRegistryId,
                'gift_item': gift_item,
                'product_id': product_id
            };

            var tmp_action = urlbuild.build("gift_registry/index/remove");
            var loader = $('body');
            loader.loader('show');
            var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                window.location.reload();
            });
        });
        $('#set-priority').on('click', function () {
            $('#set-priority-giftregistry-table').show();
            $('.giftregistry-close').click(function () {
                $('#set-priority-giftregistry-table').hide();
            })
        });
        $('#priority-gift-button').on('click', function () {
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
                var product_id = document.getElementById('giftregistry_product_id').value;
                var tmp_postData = {
                    'gift_id': giftRegistryId,
                    'gift_item_data': gift_item_data,
                    'product_id': product_id
                };
                var tmp_action = urlbuild.build("gift_registry/index/priority");
                var loader = $('body');
                loader.loader('show');
                var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                    window.location.reload();
                });
            }
        });
    }
});
