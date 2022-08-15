(function ($) {
    /**
     * @param { string } $baseUrl
     * @param { object } $extraParameter
     */
    $.swagPayPalRenderUrl = function ($baseUrl, $extraParameter) {
        var url = new URL($baseUrl);

        $.each($extraParameter, function (key, value) {
            url.searchParams.set(key, value);
        });

        return url.toString();
    };
})(jQuery);
