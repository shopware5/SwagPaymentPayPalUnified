;(function($) {

    var defaults = {
        activeCls: 'js--is-active',
        staticActiveCls: 'is--active',
        paymentSelectionSelector: '.paypal--payment-selection',
        paymentMethodSelector: '.unified--payment',
        restylePaymentSelectionAttribute: 'data-restylePaymentSelection'
    },
    restylePaymentSelection = $(defaults.paymentSelectionSelector).attr(defaults.restylePaymentSelectionAttribute);

    if (restylePaymentSelection === 'true') {
        $.overridePlugin('swShippingPayment', {
            registerEvents: function() {
                var me = this;
                me.$el.on('click', defaults.paymentMethodSelector, $.proxy(me.onClick, me));
                me.$el.on('change', me.opts.radioSelector, $.proxy(me.onInputChanged, me));

                $.publish('plugin/swShippingPayment/onRegisterEvents', [ me ]);
            },

            onClick: function(event) {
                var me = this,
                    $target = $(event.currentTarget),
                    $radio = $target.find('input[name="payment"]');

                if ($target.hasClass(defaults.activeCls) || $target.hasClass(defaults.staticActiveCls)) {
                    return;
                }

                me.$el.find(defaults.paymentMethodSelector).removeClass(defaults.activeCls).removeClass(defaults.staticActiveCls);

                $target.addClass(defaults.activeCls);
                $radio.prop('checked', true).trigger('change');
            }
        })
    }
})(jQuery);