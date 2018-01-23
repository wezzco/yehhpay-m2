define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (Component, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Wezz_Yehhpay/payment/yehhpay',
                redirectAfterPlaceOrder: false
            },
            /** Returns send check to info */
            getMailingAddress: function () {
                return true;
            },
            getPayableTo: function () {
                return true;
            },
            afterPlaceOrder: function (data, event) {
                window.location.replace(url.build('yehhpay/transaction/create'));
            }
        });
    }
);