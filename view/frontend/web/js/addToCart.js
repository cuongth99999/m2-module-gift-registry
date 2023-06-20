define([
    "jquery",
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    "jquery/ui",
    'mage/mage',
    'Magento_Catalog/product/view/validation',
], function($ , modal, alert,$t) {
    "use strict";

    var check = true;
    $.widget('magenest.addToCart', {
        options: {
            successMessage: '',
            errorMessage: '',
            cartUrl: 'checkout/cart/index',
            addUrl :'gift_registry/add',
            formKey:''
        },

        _create: function () {
            this._bind();
        },

        _bind : function() {
            var  self = this;
            $('.close').click(function () {
                $('#popupAddress').hide();
                $('#popupLogin').hide();
                $('#popupQty').hide();
                $('#popupQtyMin').hide();
            });
            $('#popupLogin').click(function () {
                $(this).hide();
            });
            $('#popupAddress').click(function () {
                $(this).hide();
            });
            $('#popupQty').click(function () {
                $(this).hide();
            });
        }
    });

    return $.magenest.addToCart;
});
