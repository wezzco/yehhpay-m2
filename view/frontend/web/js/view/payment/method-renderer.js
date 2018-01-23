define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'yehhpay',
                component: 'Wezz_Yehhpay/js/view/payment/method-renderer/yehhpay'
            }
        );

        return Component.extend({});
    }
);



