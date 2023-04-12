;(function($) {
    var RestoreOrderNumberService = function() {
        var $urlContainer = $(this.restoreOrderNumberUrlSelector);

        this.restoreOrderNumberUrl = $urlContainer.attr(this.restoreOrderNumberUrlDataAttribute);
    };

    RestoreOrderNumberService.prototype.restoreOrderNumberUrlSelector = '[data-paypalUnifiedMetaDataContainer="true"]';
    RestoreOrderNumberService.prototype.restoreOrderNumberUrlDataAttribute = 'data-paypalUnifiedRestoreOrderNumberUrl';
    RestoreOrderNumberService.prototype.restoreOrderNumberUrl = '';

    RestoreOrderNumberService.prototype.restoreOrderNumber = function() {
        $.ajax({
            type: 'POST',
            dateType: 'json',
            url: this.restoreOrderNumberUrl
        });
    };

    $.createRestoreOrderNumberService = function() {
        return new RestoreOrderNumberService();
    };
})(jQuery);
