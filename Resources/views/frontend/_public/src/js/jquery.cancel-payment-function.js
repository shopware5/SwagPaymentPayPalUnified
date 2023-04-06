;(function($) {
    var CancelPaymentFunction = function() {
        this.restoreOrderNumberService = $.createRestoreOrderNumberService();
    };

    CancelPaymentFunction.prototype.onCancel = function () {
        this.restoreOrderNumberService.restoreOrderNumber();

        $.loadingIndicator.close();
    };

    $.createCancelPaymentFunction = function() {
        return new CancelPaymentFunction();
    };
})(jQuery);
