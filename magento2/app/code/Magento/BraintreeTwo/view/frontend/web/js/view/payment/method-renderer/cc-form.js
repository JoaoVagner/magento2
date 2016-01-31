/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/quote',
        'Magento_BraintreeTwo/js/view/payment/adapter',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'Magento_BraintreeTwo/js/validator',
        'Magento_BraintreeTwo/js/view/payment/validator-handler'
    ],
    function (
        _,
        $,
        Component,
        quote,
        braintree,
        globalMessageList,
        $t,
        validator,
        validatorManager
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                active: false,
                isInitialized: false,
                braintreeClient: null,
                braintreeDeviceData: null,
                paymentMethodNonce: null,
                lastBillingAddress: null,
                validatorManager: validatorManager,
                code: 'braintreetwo',

                /**
                 * Additional payment data
                 *
                 * {Object}
                 */
                additionalData: {},

                /**
                 * {String}
                 */
                integration: 'custom',

                /**
                 * Braintree client configuration
                 *
                 * {Object}
                 */
                clientConfig: {

                    /**
                     * Triggers on payment nonce receive
                     *
                     * @param {Object} response
                     */
                    onPaymentMethodReceived: function (response) {
                        this.paymentMethodNonce = response.nonce;
                        this.placeOrder();
                    },

                    /**
                     * Triggers on any Braintree error
                     */
                    onError: function () {
                        this.paymentMethodNonce = '';
                    }
                }
            },

            /**
             * Init config
             */
            initClientConfig: function () {
                // Advanced fraud tools settings
                if (this.hasFraudProtection()) {
                    this.clientConfig = _.extend(this.clientConfig, this.kountConfig());
                }

                _.each(this.clientConfig, function (fn, name) {
                    if (typeof fn === 'function') {
                        this.clientConfig[name] = fn.bind(this);
                    }
                }, this);
            },

            /**
             * @returns {Object}
             */
            kountConfig: function () {
                var config = {
                    dataCollector: {
                        kount: {
                            environment: this.getEnvironment()
                        }
                    },

                    /**
                     * Device data initialization
                     *
                     * @param {Object} braintreeInstance
                     */
                    onReady: function (braintreeInstance) {
                        this.additionalData['device_data'] = braintreeInstance.deviceData;
                    }
                };

                if (this.getKountMerchantId()) {
                    config.dataCollector.kount.merchantId = this.getKountMerchantId();
                }

                return config;
            },

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                validator.setConfig(window.checkoutConfig.payment[this.getCode()]);
                this._super()
                    .observe(['active', 'isInitialized']);
                this.validatorManager.initialize();
                this.braintreeClient = braintree;
                this.initBraintree();

                return this;
            },

            /**
             * Get payment name
             *
             * @returns {String}
             */
            getCode: function () {
                return this.code;
            },

            /**
             * Check if payment is active
             *
             * @returns {Boolean}
             */
            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },

            /**
             * Init Braintree handlers
             */
            initBraintree: function () {
                if (!this.braintreeClient.getClientToken()) {
                    this.showError($t('Sorry, but something went wrong.'));
                }

                if (!this.isInitialized()) {
                    this.isInitialized(true);
                    this.initClient();
                }
            },

            /**
             * Init Braintree client
             */
            initClient: function () {
                this.initClientConfig();
                this.braintreeClient.getSdkClient().setup(
                    this.braintreeClient.getClientToken(),
                    this.integration,
                    this.clientConfig
                );
            },

            /**
             * Show error message
             *
             * @param {String} errorMessage
             */
            showError: function (errorMessage) {
                globalMessageList.addErrorMessage({
                    message: errorMessage
                });
            },

            /**
             * Get full selector name
             *
             * @param {String} field
             * @returns {String}
             */
            getSelector: function (field) {
                return '#' + this.getCode() + '_' + field;
            },

            /**
             * Get list of available CC types
             *
             * @returns {Object}
             */
            getCcAvailableTypes: function () {
                var availableTypes = validator.getAvailableCardTypes(),
                    billingAddress = quote.billingAddress(),
                    billingCountryId;

                this.lastBillingAddress = quote.shippingAddress();

                if (!billingAddress) {
                    billingAddress = this.lastBillingAddress;
                }

                billingCountryId = billingAddress.countryId;

                if (billingCountryId && validator.getCountrySpecificCardTypes(billingCountryId)) {

                    return validator.collectTypes(
                        availableTypes, validator.getCountrySpecificCardTypes(billingCountryId)
                    );
                }

                return availableTypes;
            },

            /**
             * @returns {Boolean}
             */
            hasFraudProtection: function () {
                return window.checkoutConfig.payment[this.getCode()].hasFraudProtection;
            },

            /**
             * @returns {String}
             */
            getEnvironment: function () {
                return window.checkoutConfig.payment[this.getCode()].environment;
            },

            /**
             * @returns {String}
             */
            getKountMerchantId: function () {
                return window.checkoutConfig.payment[this.getCode()].kountMerchantId;
            },

            /**
             * Get data
             *
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce
                    }
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

                return data;
            },

            /**
             * Set payment nonce
             * @param {String} paymentMethodNonce
             */
            setPaymentMethodNonce: function (paymentMethodNonce) {
                this.paymentMethodNonce = paymentMethodNonce;
            },

            /**
             * Action to place order
             *
             * @param {String} key
             */
            placeOrder: function (key) {
                var self = this;

                if (key) {
                    return self._super();
                }
                // place order on success validation
                self.validatorManager.validate(self, function () {
                    return self.placeOrder('parent');
                });

                return false;
            }
        });
    }
);
