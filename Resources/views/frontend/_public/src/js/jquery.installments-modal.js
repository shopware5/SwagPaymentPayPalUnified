(function($, window) {
    $.plugin('PayPalUnifiedInstallmentsModal', {

        defaults: {
            title: '',
            modalURL: '',
            productPrice: 0.0
        },

        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.subscribeEvents();
        },

        subscribeEvents: function() {
            var me = this;

            me._on(me.$el, 'click', $.proxy(me.onButtonClick, me));
        },

        onButtonClick: function() {
            var me = this;

            me.showModal(me.opts.modalURL);
        },

        showModal: function(url) {
            var me = this;

            $.loadingIndicator.open({
                openOverlay: false
            });

            $.overlay.open();

            console.log(me.opts.productPrice);

            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    productPrice: me.opts.productPrice
                }
            }).done(function(data) {
                var options = {
                    title: me.opts.title
                };

                $.loadingIndicator.close();
                $.modal.open(data, options);
            });
        },

        destroy: function() {
            var me = this;

            me._destroy();
        }
    });

    window.StateManager.addPlugin(
        '.paypal-unified-installments--modal-link',
        'PayPalUnifiedInstallmentsModal'
    );

    $.subscribe('plugin/swAjaxVariant/onRequestData', function() {
        $('.paypal-unified-installments--modal-link').PayPalUnifiedInstallmentsModal();
    });
})(jQuery, window);
