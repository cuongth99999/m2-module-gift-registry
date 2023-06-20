/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    'Magento_Ui/js/modal/modal',
    'mage/url',
    "jquery/ui",
    'mage/mage',
    'Magento_Catalog/product/view/validation',
], function ($, modal, urlbuild) {
    "use strict";

    $.widget('magenest.addToGiftRegistry', {
        options: {
            successMessage: 'Success',
            errorMessage: 'Not success',
            qtyInfo: '#qty'
        },
        _create: function () {
            this._bind();
        },
        _bind: function () {
            this.bindFormSubmit();
        },
        addGiftButton: function () {
            var tmp_giftId = $("#list-gift option:selected").data('giftid');
            var url = $("#list-gift option:selected").attr('url');
            var tmp_params = $("#list-gift option:selected").data('postgift');
            var loader = $('body');
            var tmp_action = tmp_params.action;
            if (tmp_params.data.uenc) {
                tmp_action += 'uenc/' + tmp_params.data.uenc;
            }
            tmp_action += '/giftregistry/' + tmp_giftId;
            loader.loader('show');
            if (tmp_giftId != undefined) {
                var tmp_postData = $('#product_addtocart_form').serialize();
                var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                    window.location.href = url;
                    loader.loader('hide');
                });
            }
        },
        bindFormSubmit: function () {
            var self = this;

            $('a[data-action="add-to-gift-registry"]').on('click', function (event) {
                event.stopPropagation();
                event.preventDefault();
                form = $('#product_addtocart_form');
                form.validate();
                var cd = form.valid();
                if (form.valid()) {
                    //
                    var element = $('input[type=file]' + self.options.customOptionsInfo);
                    var params = $(event.currentTarget).data('post');
                    var giftId = $(event.currentTarget).data('giftid');
                    var form = $(element).closest('form');
                    var action = params.action;
                    if (params.data.uenc) {
                        action += 'uenc/' + params.data.uenc;
                    }
                    if (params.data.giftregistry) {
                        action += '/giftregistry/' + giftId;
                    }
                    var giftRegistryCount = $(this).data('gift-count');
                    if (giftRegistryCount == 0) {
                        var options = {
                            type: 'popup',
                            responsive: true,
                            innerScroll: true,
                            title: '',
                            buttons: [{
                                text: $.mage.__('Continue'),
                                class: '',
                                click: function () {
                                    this.closeModal();
                                }
                            }]
                        };

                        var popup = modal(options, $('#popup-mopdal-gift'));

                        $('#popup-mopdal-gift').modal('openModal');

                    } else if (giftRegistryCount == 1) {
                        var tmp_params = $(event.currentTarget).data('post');
                        var tmp_giftId = $(event.currentTarget).data('giftid');
                        var url = $(this).attr('url');
                        var tmp_action = params.action;
                        var loader = $('body');

                        loader.loader('show');
                        if (tmp_params.data.uenc) {
                            tmp_action += 'uenc/' + tmp_params.data.uenc;
                        }

                        if (tmp_params.data.giftregistry) {
                            tmp_action += '/giftregistry/' + tmp_giftId;
                        }

                        var tmp_postData = $('#product_addtocart_form').serialize();


                        var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                            jQuery('div[class="page messages"]').append($('<div class="message success" >The item is added to your gift registry </div>'));
                            window.location.href = url;
                            loader.loader('hide');
                        });


                    } else if (giftRegistryCount > 1) {
                        $('div[data-role="giftregistry-table"]').show();
                        var link = urlbuild.build('gift_registry/index/giftRegistryCustomer');
                        var param = [];
                        $.ajax({
                            showLoader: true,
                            url: link,
                            data: param,
                            type: "POST",
                            dataType: 'json'
                        }).done(function (data) {
                            var html = " <select id=\"list-gift\" class=\"list-gift\">\n";

                                Object.keys(data).forEach(function(key) {
                                    var datapost = $("#add-gift-link").data("post");
                                    datapost.data.giftregistry= key;
                                    var a = JSON.stringify(datapost);
                                    var urlGift = urlbuild.build("gift_registry/index/manageregistry");
                                    html =   html +   "<option class=\"each-gift\"\n" +
                                    "                    data-action=\"add-item-gift\"\n" +
                                    "                    data-postgift="+a+"\n" +
                                    "                    data-giftid="+key+"\n" +
                                    "                    url='"+urlGift+"/type/"+data[key]+"/event_id/"+key+"'"+"\n"+
                                    "                <span class=\"gift-title\">Add to your "+data[key]+"</span>\n"+
                                    "            </option>\n";
                                });
                                    html = html +"</select>\n" + "<button id=\"add-gift-button\" data-bind='click: addGiftButton' type=\"button\" class=\"add-gift-button\">Add</button>";
                                $('#giftregistry-table').html(html);
                            $("#add-gift-button").on('click', function () {
                                var tmp_giftId = $("#list-gift option:selected").data('giftid');
                                var url = $("#list-gift option:selected").attr('url');
                                var tmp_params = $("#list-gift option:selected").data('postgift');
                                var loader = $('body');
                                var tmp_action = tmp_params.action;
                                if (tmp_params.data.uenc) {
                                    tmp_action += 'uenc/' + tmp_params.data.uenc;
                                }
                                tmp_action += '/giftregistry/' + tmp_giftId;
                                loader.loader('show');
                                if (tmp_giftId != undefined) {
                                    var tmp_postData = $('#product_addtocart_form').serialize();
                                    var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                                        window.location.href = url;
                                        loader.loader('hide');
                                    });
                                }
                            });
                        });
                        $("#add-gift-button").on('click', function () {
                            var tmp_giftId = $("#list-gift option:selected").data('giftid');
                            var url = $("#list-gift option:selected").attr('url');
                            var tmp_params = $("#list-gift option:selected").data('postgift');
                            var loader = $('body');
                            var tmp_action = tmp_params.action;
                            if (tmp_params.data.uenc) {
                                tmp_action += 'uenc/' + tmp_params.data.uenc;
                            }
                            tmp_action += '/giftregistry/' + tmp_giftId;
                            loader.loader('show');
                            if (tmp_giftId != undefined) {
                                var tmp_postData = $('#product_addtocart_form').serialize();
                                var tmp_posting = $.post(tmp_action, tmp_postData).done(function (data) {
                                    window.location.href = url;
                                    loader.loader('hide');
                                });
                            }
                        });
                    }
                }
            });
        }
    });
    return $.magenest.addToGiftRegistry;
});

