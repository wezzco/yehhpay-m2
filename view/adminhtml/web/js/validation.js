require(
    [
        'jquery',
        'mage/translate',
        'jquery/validate'],
    function ($) {
        $.validator.addMethod(
            'validate-order-max',
            function (v) {
                var f = document.getElementById('yehhpay_yehhpay_min_order_total').getAttribute('value') * 1;
                var v = v * 1;

                if (f > v) {
                    return false;
                } else {
                    return true;
                }

            },
            $.mage.__('Maximum order total can not be less then minimum order total')
        );
        $.validator.addMethod(
            'validate-order-min',
            function (v) {
                var f = document.getElementById('yehhpay_yehhpay_max_order_total').getAttribute('value') * 1;
                var v = v * 1;

                if (v > f) {
                    return false;
                } else {
                    return true;
                }

            },
            $.mage.__('Minimum order total can not be more then maximum order total')
        );
        $.validator.addMethod(
            'validate-length-custom',
            function (v,elm) {
                var reMax = new RegExp(/^maximum-length-[0-9]+$/),
                    reMin = new RegExp(/^minimum-length-[0-9]+$/),
                    validator = this,
                    result = true,
                    length = 0;
                $.each(elm.className.split(' '), function (index, name) {
                    if (name.match(reMax) && result) {
                        length = name.split('-')[2];
                        /* Need to change message for maximum length validation*/
                        validator.attrLength = 'Maximum length of this field must be equal or less than '+ length +' symbols.';
                        result = (v.length <= length);
                    }
                    if (name.match(reMin) && result && !$.mage.isEmpty(v)) {
                        length = name.split('-')[2];
                        /* Need to change message for minimum length validation*/
                        validator.attrLength = 'Minimum length of this field must be equal or greater than '+ length +' symbols.';
                        result = v.length >= length;
                    }
                });
                return result;
            },
            $.mage.__('Length of this field must be at least 2 and not more then 16 symbols')
        );

    }
);