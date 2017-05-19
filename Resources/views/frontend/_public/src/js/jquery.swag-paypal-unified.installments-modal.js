/**
 * This jQuery plugin handles the modal an offcanvas action for PayPal Unified - Installments.
 *
 * Depending on the viewport, it either opens a modal box or an offcanvas menu.
 *
 * If the mode is "offcanvas", it automatically adds an empty DOM element to the body which will
 * contain the received template later on.
 *
 * Methods:
 *  showModal()
 *  showOffcanvas()
 *
 * Events:
 *  plugin/swagPayPalUnifiedInstallmentsModal/init
 *  plugin/swagPayPalUnifiedInstallmentsModal/beforeClick
 *  plugin/swagPayPalUnifiedInstallmentsModal/afterClick
 *  plugin/swagPayPalUnifiedInstallmentsModal/showModal
 *  plugin/swagPayPalUnifiedInstallmentsModal/showOffcanvas
 *  plugin/swagPayPalUnifiedInstallmentsModal/createOffcanvasElement
 *  plugin/swagPayPalUnifiedInstallmentsModal/ajaxSuccess
 *  plugin/swagPayPalUnifiedInstallmentsModal/ajaxError
 *
 */
(function($, window) {
    $.plugin('swagPayPalUnifiedInstallmentsModal', {

        defaults: {
            /**
             * The title for the modal
             *
             * @type string
             */
            paypalInstallmentsModalTitle: '',

            /**
             * The url which is being called to obtain the modal's content (ajax).
             *
             * @type string
             */
            paypalInstallmentsModalURL: '',

            /**
             * The requested product price
             *
             * @type number
             */
            paypalInstallmentsProductPrice: 0.0,

            /**
             * Gets the mode for the modal.
             * Available modes:
             *  - modal
             *  - offcanvas
             *
             *  @type string
             */
            mode: 'modal',

            /**
             * The class of the offcanvas element
             *
             * @type string
             */
            offcanvasClass: 'paypal-installments-ct--off-canvas-info',

            /**
             * The selector for the offcanvas element
             *
             * @type string
             */
            offcanvasSelector: '.paypal-installments-ct--off-canvas-info',

            /**
             * The selector for the offcanvas close button
             *
             * @type string
             */
            offcanvasCloseSelector: '.paypal-unified-installments--modal-content .close--off-canvas'
        },

        /**
         * @type { Object }
         */
        offcanvasPlugin: null,

        /**
         * @type { Object }
         */
        $offcanvasElement: null,

        /**
         * @public
         * @method init
         */
        init: function() {
            var me = this;
            me.applyDataAttributes();
            me.subscribeEvents();

            if (me.opts.mode === 'offcanvas') {
                me.createOffcanvasElement();
            }

            $.publish('plugin/swagPayPalUnifiedInstallmentsModal/init', me);
        },

        /**
         * @private
         * @method subscribeEvents
         */
        subscribeEvents: function() {
            var me = this;

            me._on(me.$el, 'click', $.proxy(me.onClick, me));
        },

        /**
         * Will be triggered when the plugin's element was clicked.
         *
         * @private
         * @method onClick
         */
        onClick: function() {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedInstallmentsModal/beforeClick', me);

            if (me.opts.mode === 'modal') {
                me.showModal();
            } else {
                me.showOffcanvas();
            }

            $.publish('plugin/swagPayPalUnifiedInstallmentsModal/afterClick', me);
        },

        /**
         * Shows the PayPal installments modal box
         *
         * @public
         * @method showModal
         */
        showModal: function() {
            var me = this,
                url = me.opts.paypalInstallmentsModalURL;

            $.publish('plugin/swagPayPalUnifiedInstallmentsModal/showModal', me);

            $.loadingIndicator.open({
                openOverlay: false
            });

            $.overlay.open();

            $.ajax({
                method: 'GET',
                url: url,
                data: {
                    productPrice: me.opts.paypalInstallmentsProductPrice
                },
                success: $.proxy(me.ajaxRequestCallbackSuccess, me),
                error: $.proxy(me.ajaxRequestCallbackError, me)
            });
        },

        /**
         * Shows the PayPal installments offcanvas element.
         *
         * @public
         * @method showOffcanvas
         */
        showOffcanvas: function() {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedInstallmentsModal/showOffcanvas', me);

            $.loadingIndicator.open();

            $.ajax({
                method: 'GET',
                url: me.opts.paypalInstallmentsModalURL,
                data: {
                    productPrice: me.opts.paypalInstallmentsProductPrice
                },
                success: $.proxy(me.ajaxRequestCallbackSuccess, me),
                error: $.proxy(me.ajaxRequestCallbackError, me)
            });
        },

        /**
         * Adds a new element to the DOM, which will include received DOM elements later.
         *
         * @private
         * @method createOffcanvasElement
         */
        createOffcanvasElement: function() {
            var me = this;

            me.$offcanvasEl = $('<div>', {
                'class': me.opts.offcanvasClass
            });

            me.$offcanvasEl.appendTo('body');

            // Initialize the offcanvas plugin
            me.$offcanvasEl.swOffcanvasMenu({
                fullscreen: true,
                direction: 'fromRight',
                mode: 'local',
                closeButtonSelector: me.opts.offcanvasCloseSelector,
                offCanvasSelector: me.opts.offcanvasSelector
            });

            me.offcanvasPlugin = me.$offcanvasEl.data('plugin_swOffcanvasMenu');

            $.publish('plugin/swagPayPalUnifiedInstallmentsModal/createOffcanvasElement', me);
        },

        /**
         * Will be triggered when the ajax request was successful.
         *
         * @param { Object } response
         */
        ajaxRequestCallbackSuccess: function (response) {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedInstallmentsModal/ajaxSuccess', [ me, response ]);

            if (me.opts.mode === 'modal') {
                var options = {
                    title: me.opts.paypalInstallmentsModalTitle,
                    sizing: 'content',
                    width: '50%'
                };

                $.loadingIndicator.close();
                $.modal.open(response, options);
            } else {
                // Assign received data to DOM
                me.$offcanvasEl.html(response);

                me.offcanvasPlugin.openMenu();
                $.loadingIndicator.close();
            }
        },

        /**
         *
         * Will be triggered when the ajax request failed.
         *
         * @private
         * @method ajaxRequestCallbackError
         * @param { Object } response
         */
        ajaxRequestCallbackError: function (response) {
            var me = this;

            $.loadingIndicator.close();

            $.publish('plugin/swagPayPalUnifiedInstallmentsModal/ajaxError', [ me, response ]);
        },

        /**
         * @public
         * @method destroy
         */
        destroy: function() {
            var me = this;

            if (me.opts.mode === 'offcanvas') {
                me.offcanvasPlugin.destroy();
                me.$offcanvasEl.remove();
            }

            me._destroy();
        }
    });

    /**
     *  After the loading another article-variant, we lose the
     *  plugin instance, therefore, we have to re-initialize it here.
     *
     */
    $.subscribe('plugin/swAjaxVariant/onRequestData', function() {
        window.StateManager.addPlugin(
            '.paypal-unified-installments--modal-link',
            'swagPayPalUnifiedInstallmentsModal',
            { mode: 'modal' },
            ['xl', 'l', 'm']
        );

        window.StateManager.addPlugin(
            '.paypal-unified-installments--modal-link',
            'swagPayPalUnifiedInstallmentsModal',
            { mode: 'offcanvas' },
            ['s', 'xs']
        );
    });

    /**
     * If the modal link has been placed here via ajax, we have to re-initialize the plugin since
     * it looses its state.
     */
    $.subscribe('plugin/swagPayPalUnifiedAjaxInstallments/ajaxSuccess', function() {
        window.StateManager.addPlugin(
            '.paypal-unified-installments--modal-link',
            'swagPayPalUnifiedInstallmentsModal',
            { mode: 'modal' },
            ['xl', 'l', 'm']
        );

        window.StateManager.addPlugin(
            '.paypal-unified-installments--modal-link',
            'swagPayPalUnifiedInstallmentsModal',
            { mode: 'offcanvas' },
            ['s', 'xs']
        );
    });

    window.StateManager.addPlugin(
        '.paypal-unified-installments--modal-link',
        'swagPayPalUnifiedInstallmentsModal',
        { mode: 'modal' },
        ['xl', 'l', 'm']
    );

    window.StateManager.addPlugin(
        '.paypal-unified-installments--modal-link',
        'swagPayPalUnifiedInstallmentsModal',
        { mode: 'offcanvas' },
        ['s', 'xs']
    );
})(jQuery, window);
