define([
    'jquery',
    'Magenest_GiftRegistry/js/addToCart',
    'Magenest_GiftRegistry/js/jquery.plugin',
    'Magenest_GiftRegistry/js/jquery.countdown',
    'Magento_Ui/js/modal/prompt'

], function ($, addToCart) {
    'use strict';
    return function (config) {
        $(document).ready(function () {
            addToCart({
                cartUrl: config.cartUrl,
                addUrl: config.addUrl,
                formKey: config.formKey
            });
            var austDay = new Date();
            /*austDay = new Date(austDay.getFullYear() + 1, 1 - 1, 10);*/
            austDay = new Date(config.registry.year * 1, config.registry.month * 1, config.registry.date * 1);
            if ($.isFunction($.fn.countdown)) {
                $('#countdown-gift').countdown({until: austDay});
            }

            $('#year').text(austDay.getFullYear());

            function getCookie(cname) {
                var name = cname + "=";
                var decodedCookie = decodeURIComponent(document.cookie);
                var ca = decodedCookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return null;
            }

            if (document.cookie.match(window.location.pathname)) {
                $("aside").hide();
            } else {
                $("aside").show();
            }

            var pass2 = $("#password-temp").val();
            if (pass2 != '') {
                $('button[data-role="action"]').click(function () {
                    var pass1 = $("#prompt-field").val();
                    $.ajax({
                        type: "POST",
                        url: config.checkpass,
                        data: {
                            pass1: pass1,
                            pass2: pass2
                        },
                        showLoader: true
                    }).done(function (response) {
                        if (response['check'] == true) {
                            var expires = "; expires=0";
                            var url_gr = getCookie('url_giftregistry') || "";
                            url_gr += window.location.pathname + ',';
                            document.cookie = 'url_giftregistry=' + url_gr + expires + ';path=/';
                            $("aside").fadeOut(350);
                            $("#registry-list").show();
                        } else {
                            $(".message-error").show();
                        }
                    });
                })
            }
        });
    }
});
