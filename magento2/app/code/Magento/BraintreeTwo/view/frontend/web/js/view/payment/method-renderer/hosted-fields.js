/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'Magento_BraintreeTwo/js/view/payment/method-renderer/cc-form',
    'Magento_BraintreeTwo/js/validator',
    'Magento_Vault/js/view/payment/vault-enabler',
    'mage/translate'
], function ($, Component, validator, vaultEnabler, $t) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Magento_BraintreeTwo/payment/form',
            clientConfig: {

                /**
                 * {String}
                 */
                id: 'co-transparent-form-braintree'
            }
        },

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            this.vaultEnabler = vaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getCode());

            return this;
        },

        /**
         * Init config
         */
        initClientConfig: function () {
            this._super();

            // Hosted fields settings
            this.clientConfig.hostedFields = this.getHostedFields();
        },

        /**
         * @returns {Object}
         */
        getData: function () {
            var data = this._super();

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        /**
         * @returns {Bool}
         */
        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        /**
         * Get Braintree Hosted Fields
         * @returns {Object}
         */
        getHostedFields: function () {
            var self = this,
                fields = {
                    number: {
                        selector: self.getSelector('cc_number')
                    },
                    expirationMonth: {
                        selector: self.getSelector('expirationMonth'),
                        placeholder: $t('MM')
                    },
                    expirationYear: {
                        selector: self.getSelector('expirationYear'),
                        placeholder: $t('YY')
                    }
                };

            if (self.hasVerification()) {
                fields.cvv = {
                    selector: self.getSelector('cc_cid')
                };
            }

            /**
             * Triggers on Hosted Field changes
             * @param {Object} event
             * @returns {Boolean}
             */
            fields.onFieldEvent = function (event) {
                if (event.isEmpty === false) {
                    self.validateCardType();
                }

                if (event.type !== 'fieldStateChange') {

                    return false;
                }

                // Handle a change in validation or card type
                if (event.target.fieldKey === 'number') {
                    self.selectedCardType(null);
                }

                if (!event.isValid) {
                    return false;
                }

                if (event.card) {
                    self.selectedCardType(
                        validator.getMageCardType(event.card.type, self.getCcAvailableTypes())
                    );
                }
            };

            return fields;
        },

        /**
         * Validate current credit card type
         * @returns {Boolean}
         */
        validateCardType: function () {
            var $selector = $(this.getSelector('cc_number')),
                invalidClass = 'braintree-hosted-fields-invalid';

            $selector.removeClass(invalidClass);

            if (this.selectedCardType() === null) {
                $(this.getSelector('cc_number')).addClass(invalidClass);

                return false;
            }

            return true;
        },

        /**
         * Trigger order placing
         */
        placeOrderClick: function () {
            if (this.validateCardType()) {
                $(this.getSelector('submit')).trigger('click');
            }
        }
    });
});
