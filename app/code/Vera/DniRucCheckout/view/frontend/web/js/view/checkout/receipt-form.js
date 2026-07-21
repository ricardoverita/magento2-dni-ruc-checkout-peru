define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/translate',
    'mage/storage',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer'
], function ($, ko, Component, $t, storage, urlBuilder, quote, customer) {
    'use strict';

    var storageKeyPrefix = 'vera-dni-ruc-checkout-';

    return Component.extend({
        defaults: {
            template: 'Vera_DniRucCheckout/checkout/receipt-form'
        },

        initialize: function () {
            this._super();

            this.receiptType = ko.observable('boleta');
            this.taxIdType = ko.observable('dni');
            this.taxId = ko.observable('');
            this.companyName = ko.observable('');
            this.fiscalAddress = ko.observable('');
            this.errors = {
                receiptType: ko.observable(''),
                taxId: ko.observable(''),
                companyName: ko.observable(''),
                fiscalAddress: ko.observable('')
            };
            this.isBoleta = ko.pureComputed(function () {
                return this.receiptType() === 'boleta';
            }, this);
            this.isFactura = ko.pureComputed(function () {
                return this.receiptType() === 'factura';
            }, this);
            this.taxIdTypeLabel = ko.pureComputed(function () {
                return this.isBoleta() ? $t('DNI') : $t('RUC');
            }, this);
            this.dniRequired = !!(
                window.checkoutConfig.veraDniRucCheckout &&
                window.checkoutConfig.veraDniRucCheckout.dniRequired
            );
            this.isEnabled = !!(
                window.checkoutConfig.veraDniRucCheckout &&
                window.checkoutConfig.veraDniRucCheckout.enabled
            );

            this.loadData();
            this.applyToBillingAddress();

            return this;
        },

        loadData: function () {
            var localData = window.localStorage.getItem(this.getStorageKey()),
                data;

            if (localData) {
                try {
                    data = JSON.parse(localData);
                } catch (error) {
                    data = null;
                }
            }

            if (data && data.receipt_type) {
                this.setData(data);
            }

            this.loadRemoteData();
        },

        loadRemoteData: function () {
            var url = this.getEndpointUrl();

            if (!url) {
                return;
            }

            storage.get(url).done(function (data) {
                if (data && data.receipt_type) {
                    this.setData(data);
                    this.persistLocalData();
                    this.applyToBillingAddress();
                }
            }.bind(this));
        },

        setData: function (data) {
            var receiptType = data.receipt_type === 'factura' ? 'factura' : 'boleta';

            this.receiptType(receiptType);
            this.taxIdType(receiptType === 'factura' ? 'ruc' : 'dni');
            this.taxId(data.tax_id || '');
            this.companyName(data.company_name || '');
            this.fiscalAddress(data.fiscal_address || '');
        },

        onReceiptTypeChange: function () {
            var receiptType = this.receiptType();

            this.taxIdType(receiptType === 'factura' ? 'ruc' : 'dni');
            this.taxId('');
            this.companyName('');
            this.fiscalAddress('');
            this.clearErrors();
            this.persistLocalData();
            this.applyToBillingAddress();
        },

        onFieldChange: function () {
            this.taxId(this.normalizeNumber(this.taxId()));
            this.companyName(this.normalizeText(this.companyName()));
            this.fiscalAddress(this.normalizeText(this.fiscalAddress()));
            this.persistLocalData();
            this.applyToBillingAddress();

            if (this.validate(false)) {
                this.persist();
            }
        },

        onTaxIdInput: function () {
            var maxLength = this.isBoleta() ? 8 : 11,
                value = this.normalizeNumber(this.taxId()).replace(/[^0-9]/g, '');

            this.taxId(value.substring(0, maxLength));
            this.persistLocalData();
            this.applyToBillingAddress();
        },

        validate: function (showErrors) {
            var valid = true,
                show = showErrors !== false;

            this.clearErrors();

            if (this.isBoleta()) {
                if (this.dniRequired && !this.taxId()) {
                    this.errors.taxId($t('DNI is required for Boleta.'));
                    valid = false;
                } else if (this.taxId() && (!/^\d{8}$/.test(this.taxId()) || /^0+$/.test(this.taxId()))) {
                    this.errors.taxId($t('DNI must contain exactly 8 digits and cannot be all zeros.'));
                    valid = false;
                }
            } else {
                if (!/^\d{11}$/.test(this.taxId()) || /^0+$/.test(this.taxId()) || !this.isValidRuc(this.taxId())) {
                    this.errors.taxId($t('Please enter a valid RUC, including its check digit.'));
                    valid = false;
                }

                if (!this.companyName()) {
                    this.errors.companyName($t('Company name is required for Factura.'));
                    valid = false;
                } else if (this.companyName().length > 255) {
                    this.errors.companyName($t('Company name cannot exceed 255 characters.'));
                    valid = false;
                }

                if (!this.fiscalAddress()) {
                    this.errors.fiscalAddress($t('Fiscal address is required for Factura.'));
                    valid = false;
                } else if (this.fiscalAddress().length > 255) {
                    this.errors.fiscalAddress($t('Fiscal address cannot exceed 255 characters.'));
                    valid = false;
                }
            }

            if (!show) {
                this.clearErrors();
            }

            return valid;
        },

        isValidRuc: function (ruc) {
            var weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2],
                sum = 0,
                checkDigit,
                index,
                prefix = ruc.substring(0, 2);

            if (['10', '15', '17', '20'].indexOf(prefix) === -1) {
                return false;
            }

            for (index = 0; index < weights.length; index++) {
                sum += parseInt(ruc.charAt(index), 10) * weights[index];
            }

            checkDigit = (11 - (sum % 11)) % 10;

            return checkDigit === parseInt(ruc.charAt(10), 10);
        },

        persist: function () {
            var data = this.getData(),
                url = this.getEndpointUrl();

            if (!url || !this.validate(true)) {
                return $.Deferred().reject().promise();
            }

            return storage.post(url, JSON.stringify(data), false, 'application/json')
                .fail(function (response) {
                    var message = response && response.responseJSON && response.responseJSON.message;

                    if (message) {
                        this.errors.taxId(message);
                    }
                }.bind(this));
        },

        getData: function () {
            return {
                receipt_type: this.receiptType(),
                tax_id_type: this.taxIdType(),
                tax_id: this.normalizeNumber(this.taxId()),
                company_name: this.isFactura() ? this.normalizeText(this.companyName()) : null,
                fiscal_address: this.isFactura() ? this.normalizeText(this.fiscalAddress()) : null
            };
        },

        applyToBillingAddress: function () {
            var billingAddress = quote.billingAddress();

            if (!billingAddress) {
                return;
            }

            billingAddress.extension_attributes = billingAddress.extension_attributes || {};
            billingAddress.extension_attributes.receipt_type = this.receiptType();
            billingAddress.extension_attributes.tax_id_type = this.taxIdType();
            billingAddress.extension_attributes.tax_id = this.normalizeNumber(this.taxId());
            billingAddress.extension_attributes.company_name = this.isFactura() ? this.normalizeText(this.companyName()) : null;
            billingAddress.extension_attributes.fiscal_address = this.isFactura() ? this.normalizeText(this.fiscalAddress()) : null;
        },

        persistLocalData: function () {
            window.localStorage.setItem(this.getStorageKey(), JSON.stringify(this.getData()));
        },

        getStorageKey: function () {
            return storageKeyPrefix + (quote.getQuoteId() || 'current');
        },

        getEndpointUrl: function () {
            if (customer.isLoggedIn()) {
                return urlBuilder.createUrl('/V1/carts/mine/vera-receipt');
            }

            if (!quote.getQuoteId()) {
                return null;
            }

            return urlBuilder.createUrl('/V1/guest-carts/:cartId/vera-receipt', {
                cartId: quote.getQuoteId()
            });
        },

        normalizeNumber: function (value) {
            return String(value || '').trim();
        },

        normalizeText: function (value) {
            return String(value || '').replace(/\s+/g, ' ').trim();
        },

        clearErrors: function () {
            this.errors.receiptType('');
            this.errors.taxId('');
            this.errors.companyName('');
            this.errors.fiscalAddress('');
        }
    });
});
