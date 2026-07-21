define([
    'jquery',
    'uiRegistry'
], function ($, registry) {
    'use strict';

    return function (placeOrderAction) {
        return function (paymentData, messageContainer) {
            var component = registry.get('checkout.steps.billing-step.payment.beforeMethods.vera-dni-ruc-receipt');

            if (component && !component.validate(true)) {
                return $.Deferred().reject().promise();
            }

            if (component) {
                component.applyToBillingAddress();
                component.persistLocalData();
            }

            return placeOrderAction(paymentData, messageContainer);
        };
    };
});
