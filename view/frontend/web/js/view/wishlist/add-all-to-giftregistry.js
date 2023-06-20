define([
    "jquery",
    'mage/url',
    'mage/mage'
],function ($, urlbuild) {
    "use strict";

    return function (config) {
        var table = $("#add-giftregistry-table");

        function getUrl() {
            let url = config.actionAddUrl + '?type= true';
            $('.products-grid.wishlist').find('[data-role=qty]').each(function (index, element) {
                url += '&' + $(element).prop('name') + '=' + encodeURIComponent($(element).val());
            });
            return url;
        }

        $("#add-all-to-giftregistry").on('click', function () {
            $('div[class="page messages"]').empty();
            $.ajax({
                showLoader: true,
                url: getUrl(),
                type: "POST",
                dataType: "json"
            }).done(function (response) {
                if(response.messageType){
                    var message = $('<div class="message-error message error" ><div>'+response.messageType+'</div></div>');
                    $('div[class="page messages"]').append(message);
                }else{
                    if(response.showGift){
                        var dataGift = response.giftData;

                        dataGift.forEach(function (item, index) {
                            $('.list-gift').append("<div class='field-choose'><label><input type='radio' value="+item['gift_id']+" name='event_type'>"+item['title']+"</label></div>")
                        });
                        $('.giftregistry-modal-content').append("<input type='hidden' id='addToGiftRegistryData' value='" + JSON.stringify(response.data) + "'/>")
                        table.show();
                    } else{
                        var tmp_postData = response.data;
                        var tmp_action = response.urlAdd;
                        var loader = $('body');

                        loader.loader('show');
                        $.post(tmp_action, tmp_postData).done(function (data) {
                            window.location.reload();
                        });
                    }
                }
            });
            $('.giftregistry-close').click(function () {
                table.hide();
                $('.field-choose').remove();
            })
        });

        $("#add-gift-button").on('click', function () {
            var giftRegistryMessageErrorElement = $("#giftregistry-message-error"),
                giftRegistryId = '',
                radios = $('[name="event_type"]');

            giftRegistryMessageErrorElement.empty();
            for (var i = 0, length = radios.length; i < length; i++) {
                if (radios[i].checked) {
                    giftRegistryId = radios[i].value;
                    break;
                }
            }
            if(giftRegistryId === ''){
                var message = $("<div>Please choose a Gift Registry!</div>");

                giftRegistryMessageErrorElement.append(message);
            }else{
                var tmp_postData = JSON.parse($('#addToGiftRegistryData').val()),
                    tmp_action = urlbuild.build("gift_registry/wishlist/addalltogiftregistry"),
                    loader = $('body');

                tmp_postData['giftregistry'] = giftRegistryId;
                loader.loader('show');
                $.post(tmp_action, tmp_postData).done(function (data) {
                    window.location.reload();
                });
            }
        });
    }
});
