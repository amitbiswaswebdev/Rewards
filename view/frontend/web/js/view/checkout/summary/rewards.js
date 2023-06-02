define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Customer/js/model/customer'
    ],
    function (Component, quote, priceUtils, totals, customer) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'EasyMage_Rewards/checkout/summary/rewards'
            },
            totals: quote.getTotals(),

            /**
             * @return {*|Boolean}
             */
            isDisplayed: function () {
                var isEnabled = window.checkoutConfig.reward.isEnabled;
                return customer.isLoggedIn() && isEnabled && this.isFullMode() && this.getPureValue() != 0; //eslint-disable-line eqeqeq
            },

            /**
             * Get discount title
             *
             * @returns {null|String}
             */
            getTitle: function () {
                if (!this.totals()) {
                    return null;
                }

                return this.title
            },

            /**
             * @return {Number}
             */
            getPureValue: function () {
                var price = 0;

                if (this.totals()) {
                    var segment = totals.getSegment('rewards');
                    if (segment != null) {
                        price = segment.value;
                    }
                }

                return price;
            },

            /**
             * @return {*|String}
             */
            getValue: function () {
                return this.getFormattedPrice(this.getPureValue());
            }
        });
    }
);
