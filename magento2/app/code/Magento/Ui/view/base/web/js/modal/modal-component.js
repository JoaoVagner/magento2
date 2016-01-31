/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/view/utils/async',
    'uiCollection',
    'uiRegistry',
    'underscore',
    './modal'
], function ($, Collection, registry, _) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'ui/modal/modal-component',
            options: {
                title: '',
                buttons: [],
                keyEventHandlers: {}
            },
            valid: true,
            listens: {
                state: 'onState'
            },
            modalClass: 'modal-component'
        },

        /**
         * Initializes component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
            _.bindAll(this,
                'initModal',
                'openModal',
                'closeModal',
                'toggleModal',
                'setPrevValues',
                'actionCancel',
                'validate');
            this.initializeContent();

            return this;
        },

        /**
         * Initializes modal configuration
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            return this._super()
                .initSelector()
                .initModalEvents();
        },

        /**
         * Configure modal selector
         *
         * @returns {Object} Chainable.
         */
        initSelector: function () {
            this.contentSelector = '.' + this.modalClass;
            this.options.modalClass = this.name.replace(/\./g, '_');
            this.rootSelector = '.' + this.options.modalClass;

            return this;
        },

        /**
         * Configure modal keyboard handlers
         * and outer click
         *
         * @returns {Object} Chainable.
         */
        initModalEvents: function () {
            this.options.keyEventHandlers.escapeKey = this.options.outerClickHandler = this.actionCancel.bind(this);

            return this;
        },

        /**
         * Initialize modal's content components
         */
        initializeContent: function () {
            $.async(this.contentSelector, this, this.initModal);
        },

        /**
         * Init toolbar section so other components will be able to place something in it
         */
        initToolbarSection: function () {
            this.set('toolbarSection', this.modal.data('modal').modal.find('header').get(0));
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super();
            this.observe('state');

            return this;
        },

        /**
         * Wrap content in a modal of certain type
         *
         * @param {HTMLElement} element
         * @returns {Object} Chainable.
         */
        initModal: function (element) {
            if (!this.modal) {
                this.overrideModalButtonCallback();
                this.options.modalCloseBtnHandler = this.actionCancel;
                this.modal = $(element).modal(this.options);
                this.initToolbarSection();

                if (this.waitCbk) {
                    this.waitCbk();
                    this.waitCbk = null;
                }
            }

            return this;
        },

        /**
         * Open modal
         */
        openModal: function () {
            if (this.modal) {
                this.state(true);
            } else {
                this.waitCbk = this.openModal;
            }
        },

        /**
         * Close modal
         */
        closeModal: function () {
            if (this.modal) {
                this.state(false);
            } else {
                this.waitCbk = this.closeModal;
            }
        },

        /**
         * Toggle modal
         */
        toggleModal: function () {
            if (this.modal) {
                this.state(!this.state());
            } else {
                this.waitCbk = this.toggleModal;
            }
        },

        /**
         * Wrap content in a modal of certain type
         *
         * @param {Boolean} state
         */
        onState: function (state) {
            if (state) {
                this.modal.modal('openModal');
                this.applyData();
            } else {
                this.modal.modal('closeModal');
            }
        },

        /**
         * Validate everything validatable in modal
         */
        validate: function (elem) {
            if (typeof elem.validate === 'function') {
                this.valid = this.valid & elem.validate().valid;
            } else if (elem.elems) {
                elem.elems().forEach(this.validate, this);
            }
        },

        /**
         * Reset data from provider
         */
        resetData: function () {
            this.elems().forEach(this.resetValue, this);
        },

        /**
         * Update 'applied' property with data from modal content
         */
        applyData: function () {
            var applied = {};

            this.elems().forEach(this.gatherValues.bind(this, applied), this);
            this.applied = applied;
        },

        /**
         * Gather values from modal content
         *
         * @param {Array} applied
         * @param {HTMLElement} elem
         */
        gatherValues: function (applied, elem) {
            if (typeof elem.value === 'function') {
                applied[elem.index] = elem.value();
            } else if (elem.elems) {
                elem.elems().forEach(this.gatherValues.bind(this, applied), this);
            }
        },

        /**
         * Set to previous values from modal content
         *
         * @param {HTMLElement} elem
         */
        setPrevValues: function (elem) {
            if (typeof elem.value === 'function') {
                this.modal.focus();
                elem.value(this.applied[elem.index]);
            } else if (elem.elems) {
                elem.elems().forEach(this.setPrevValues, this);
            }
        },

        /**
         * Triggers some method in every modal child elem, if this method is defined
         *
         * @param {Object} action - action configuration,
         * must contain actionName and targetName and
         * can contain params
         */
        triggerAction: function (action) {
            var targetName = action.targetName,
                params = action.params,
                actionName = action.actionName,
                target;

            target = registry.async(targetName);

            if (target && typeof target === 'function' && actionName) {
                target(actionName, params);
            }
        },

        /**
         * Override modal buttons callback placeholders with real callbacks
         */
        overrideModalButtonCallback: function () {
            var buttons = this.options.buttons;

            if (buttons && buttons.length) {
                buttons.forEach(function (button) {
                    button.click = this.getButtonClickHandler(button.actions);
                }, this);
            }
        },

        /**
         * Generate button click handler based on button's 'actions' configuration
         */
        getButtonClickHandler: function (actionsConfig) {
            var actions = actionsConfig.map(
                function (actionConfig) {
                    if (_.isObject(actionConfig)) {
                        return this.triggerAction.bind(this, actionConfig);
                    }

                    return this[actionConfig] ? this[actionConfig].bind(this) : function () {};
                }, this);

            return function () {
                actions.forEach(
                    function (action) {
                        action();
                    }
                );
            };
        },

        /**
         * Cancels changes in modal:
         * returning elems values to the previous state,
         * and close modal
         */
        actionCancel: function () {
            this.elems().forEach(this.setPrevValues, this);
            this.closeModal();
        },

        /**
         * Accept changes in modal by not preventing them.
         * Can be extended by exporting 'gatherValues' result somewhere
         */
        actionDone: function () {
            this.valid = true;
            this.elems().forEach(this.validate, this);

            if (this.valid) {
                this.closeModal();
            }
        }
    });
});
