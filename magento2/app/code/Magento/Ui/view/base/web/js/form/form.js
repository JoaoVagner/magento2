/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/lib/spinner',
    'rjsResolver',
    './adapter',
    'uiCollection'
], function (_, loader, resolver, adapter, Collection) {
    'use strict';

    function collectData(selector) {
        var items = document.querySelectorAll(selector),
            result = {};

        items = Array.prototype.slice.call(items);

        items.forEach(function (item) {
            switch (item.type) {
                case 'checkbox':
                    result[item.name] = +!!item.checked;
                    break;

                case 'radio':
                    if (item.checked) {
                        result[item.name] = item.value;
                    }
                    break;

                default:
                    result[item.name] = item.value;
            }
        });

        return result;
    }

    return Collection.extend({
        initialize: function () {
            this._super()
                .initAdapter();

            resolver(this.hideLoader, this);

            return this;
        },

        initAdapter: function () {
            adapter.on({
                'reset': this.reset.bind(this),
                'save': this.save.bind(this, true),
                'saveAndContinue': this.save.bind(this, false),
                'saveAndApply': this.saveAndApply.bind(this, true)
            });

            return this;
        },

        initConfig: function () {
            this._super();

            this.selector = '[data-form-part=' + this.namespace + ']';

            return this;
        },

        hideLoader: function () {
            loader.get(this.name).hide();

            return this;
        },

        save: function (redirect) {
            this.validate();

            if (!this.source.get('params.invalid')) {
                this.submit(redirect);
            }
        },

        /**
         * Submits form
         */
        submit: function (redirect) {
            var additional = collectData(this.selector),
                source = this.source;

            _.each(additional, function (value, name) {
                source.set('data.' + name, value);
            });

            source.save({
                redirect: redirect,
                attributes: {
                    id: this.namespace
                }
            });
        },

        /**
         * Validates each element and returns true, if all elements are valid.
         */
        validate: function () {
            this.source.set('params.invalid', false);
            this.source.trigger('data.validate');
        },

        reset: function () {
            this.source.trigger('data.reset');
        },

        saveAndApply: function (redirect) {
            this.validate();

            if (!this.source.get('params.invalid')) {
                this.source.set('data.auto_apply', 1);
                this.submit(redirect);
            }
        }
    });
});
