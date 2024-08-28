(function ($) {
    /**
     * @param { string } url
     */
    $.redirectToUrl = function (url) {
        setTimeout(function () {
            window.location.replace(url);
        }, 250);
    };
})(jQuery);
