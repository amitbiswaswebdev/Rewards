/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Coupon model.
 */
define([
    'ko',
    'domReady!'
], function (ko) {
    'use strict';

    var isApplied = ko.observable(null);

    return {
        isApplied: isApplied,

        /**
         * @return {Boolean}
         */
        getIsApplied: function () {
            return isApplied;
        },

        /**
         * @param {Boolean} isAppliedValue
         */
        setIsApplied: function (isAppliedValue) {
            isApplied(isAppliedValue);
        }
    };
});
