define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.rewardSubmit', {
        options: {},
        /** @inheritdoc */
        _create: function () {

            this.rewardForm = $(this.options.easyRewardFormSelector);
            this.removeReward = $(this.options.easyRemoveRewardSelector);

            console.log(this.rewardForm);

            $(this.options.easyRemoveRewardButton).on('click', $.proxy(function () {
                this.removeReward.attr('value', '1');
                this.rewardForm.trigger('submit');
            }, this));

            $(this.options.easyApplyRewardButton).on('click', $.proxy(function () {
                this.removeReward.attr('value', '0');
                this.rewardForm.trigger('submit');
            }, this));
        }
    });

    return $.mage.rewardSubmit;
});
