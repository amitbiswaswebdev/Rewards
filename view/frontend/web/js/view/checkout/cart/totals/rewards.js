define(
    [
        'EasyMage_Rewards/js/view/checkout/summary/rewards',
        'Magento_Customer/js/model/customer'
    ],
    function (Component, customer) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'EasyMage_Rewards/checkout/cart/totals/rewards'
            },

            /**
             * @override
             *
             * @returns {Boolean}
             */
            isDisplayed: function () {
                var isEnabled = window.checkoutConfig.reward.isEnabled;
                return customer.isLoggedIn() && isEnabled && this.getPureValue() != 0; //eslint-disable-line eqeqeq
            }
        });
    }
);