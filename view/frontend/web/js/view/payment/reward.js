/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'EasyMage_Rewards/js/action/applyReward',
    'EasyMage_Rewards/js/action/removeReward',
    'EasyMage_Rewards/js/model/reward'
], function ($, ko, Component, quote, applyRewardAction, removeRewardAction, reward) {
    'use strict';

    var totals = quote.getTotals(),
        isApplied = reward.getIsApplied(),
        applied = false;
    if (totals()) {
        applied = (totals()['reward_amount'] != 0)
    }
    isApplied(applied);

    return Component.extend({
        defaults: {
            template: 'EasyMage_Rewards/payment/reward'
        },

        /**
         * Applied flag
         */
        isApplied: isApplied,

        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                applyRewardAction(isApplied);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                console.log('hello');
                removeRewardAction(isApplied);
            }
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#reward-form';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
