/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/resource-url-manager'
], function (customer, urlManager) {
    'use strict';

    return {

        /**
         * @param {String} couponCode
         * @param {String} quoteId
         * @return {*}
         */
        getApplyRewardUrl: function (quoteId) {
            if (urlManager.getCheckoutMethod() === 'customer') {
                var urls = { customer: '/easy/reward/apply/:cartId' };
                var params = { cartId: quoteId };

                return urlManager.getUrl(urls, params);
            }

            throw new Error('You have to login in order to apply reward.');
        },

        /**
         * @param {String} quoteId
         * @return {*}
         */
        getCancelRewardUrl: function (quoteId) {
            if (urlManager.getCheckoutMethod() === 'customer') {
                var urls = { customer: '/easy/reward/cancel/:cartId' };
                var params = { cartId: quoteId };

                return urlManager.getUrl(urls, params);
            }

            throw new Error('You have to login in order to remove reward.');
        },
    };
}
);
