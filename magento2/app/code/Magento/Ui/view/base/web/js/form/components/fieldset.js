/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/collapsible',
    'underscore'
], function (Collapsible, _) {
    'use strict';

    return Collapsible.extend({
        defaults: {
            template: 'ui/form/fieldset',
            collapsible: false,
            changed: false,
            loading: false,
            error: false,
            opened: false
        },

        /**
         * Extends instance with defaults. Invokes parent initialize method.
         * Calls initListeners and pushParams methods.
         */
        initialize: function () {
            _.bindAll(this, 'onChildrenUpdate', 'onChildrenError', 'onContentLoading');

            return this._super();
        },

        /**
         * Calls initObservable of parent class.
         * Defines observable properties of instance.
         * @return {Object} - reference to instance
         */
        initObservable: function () {
            this._super()
                .observe('changed loading error');

            return this;
        },

        /**
         * Calls parent's initElement method.
         * Assignes callbacks on various events of incoming element.
         * @param  {Object} elem
         * @return {Object} - reference to instance
         */
        initElement: function (elem) {
            this._super();

            elem.on({
                'update':   this.onChildrenUpdate,
                'loading':  this.onContentLoading,
                'error':  this.onChildrenError
            });

            return this;
        },

        /**
         * Is being invoked on children update.
         * Sets changed property to one incoming.
         *
         * @param  {Boolean} hasChanged
         */
        onChildrenUpdate: function (hasChanged) {
            if (!hasChanged) {
                hasChanged = _.some(this.delegate('hasChanged'));
            }

            this.changed(hasChanged);
        },

        /**
         * Is being invoked on children validation error.
         * Sets error property to one incoming.
         */
        onChildrenError: function () {
            var hasErrors = this.elems.some('error');

            this.error(hasErrors);
        },

        /**
         * Callback that sets loading property to true.
         */
        onContentLoading: function (isLoading) {
            this.loading(isLoading);
        }
    });
});
