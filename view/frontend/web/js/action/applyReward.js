/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'EasyMage_Rewards/js/model/reward-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'EasyMage_Rewards/js/model/payment/rewardMessages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/recollect-shipping-rates'
], function (
    ko,
    $,
    quote,
    rewardUrlManager,
    errorProcessor,
    messageContainer,
    storage,
    $t,
    getPaymentInformationAction,
    totals,
    fullScreenLoader,
    recollectShippingRates
) {
    'use strict';

    var dataModifiers = [],
        successCallbacks = [],
        failCallbacks = [],
        action;

    /**
     * Apply provided coupon.
     *
     * @param {Boolean}isApplied
     * @returns {Deferred}
     */
    // action = function (couponCode, isApplied) {
    action = function (isApplied) {
        var quoteId = quote.getQuoteId(),
            message = $t('Your coupon was successfully applied.'),
            data = {},
            headers = {};

        try {
            var url = rewardUrlManager.getApplyRewardUrl(quoteId);

            dataModifiers.forEach(function (modifier) {
                modifier(headers, data);
            });
            fullScreenLoader.startLoader();

            return storage.put(
                url,
                data,
                false,
                null,
                headers
            ).done(function (response) {
                var deferred;

                if (response) {
                    deferred = $.Deferred();

                    isApplied(true);
                    totals.isLoading(true);
                    recollectShippingRates();
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        fullScreenLoader.stopLoader();
                        totals.isLoading(false);
                    });
                    messageContainer.addSuccessMessage({
                        'message': message
                    });
                    //Allowing to tap into apply-coupon process.
                    successCallbacks.forEach(function (callback) {
                        callback(response);
                    });
                }
            }).fail(function (response) {
                fullScreenLoader.stopLoader();
                totals.isLoading(false);
                errorProcessor.process(response, messageContainer);
                failCallbacks.forEach(function (callback) {
                    callback(response);
                });
            });
        } catch (error) {
            errorProcessor.process(error.message, messageContainer);
        }
    };

    /**
     * Modifying data to be sent.
     *
     * @param {Function} modifier
     */
    action.registerDataModifier = function (modifier) {
        dataModifiers.push(modifier);
    };

    /**
     * When successfully added a coupon.
     *
     * @param {Function} callback
     */
    action.registerSuccessCallback = function (callback) {
        successCallbacks.push(callback);
    };

    /**
     * When failed to add a coupon.
     *
     * @param {Function} callback
     */
    action.registerFailCallback = function (callback) {
        failCallbacks.push(callback);
    };

    return action;
});
