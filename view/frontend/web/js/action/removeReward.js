/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'EasyMage_Rewards/js/model/reward-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'EasyMage_Rewards/js/model/payment/rewardMessages',
    'mage/storage',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/recollect-shipping-rates'
], function (
    $,
    quote,
    rewardUrlManager,
    errorProcessor,
    messageContainer,
    storage,
    getPaymentInformationAction,
    totals,
    $t,
    fullScreenLoader,
    recollectShippingRates
) {
    'use strict';

    var successCallbacks = [],
        action,
        callSuccessCallbacks;

    /**
     * Execute callbacks when a coupon is successfully canceled.
     */
    callSuccessCallbacks = function () {
        successCallbacks.forEach(function (callback) {
            callback();
        });
    };

    /**
     * Cancel applied coupon.
     *
     * @param {Boolean} isApplied
     * @returns {Deferred}
     */
    action = function (isApplied) {
        console.log('hello action');
        var quoteId = quote.getQuoteId(),
            message = $t('Your coupon was successfully removed.');


        try {
            var url = rewardUrlManager.getCancelRewardUrl(quoteId);
            messageContainer.clear();
            fullScreenLoader.startLoader();

            return storage.delete(
                url,
                false
            ).done(function () {
                var deferred = $.Deferred();

                totals.isLoading(true);
                recollectShippingRates();
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    isApplied(false);
                    totals.isLoading(false);
                    fullScreenLoader.stopLoader();
                    callSuccessCallbacks();
                });
                messageContainer.addSuccessMessage({
                    'message': message
                });
            }).fail(function (response) {
                totals.isLoading(false);
                fullScreenLoader.stopLoader();
                errorProcessor.process(response, messageContainer);
            });
        } catch (error) {
            errorProcessor.process(error.message, messageContainer);
        }
    };

    /**
     * Callback for when the cancel-coupon process is finished.
     *
     * @param {Function} callback
     */
    action.registerSuccessCallback = function (callback) {
        successCallbacks.push(callback);
    };

    return action;
});
