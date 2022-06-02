export default (function () {
    return {
        checkCsrfCookie: async function (page) {
            const browserCookies = await page.context().cookies(page.url());
            const csrfInputLocator = page.locator('form').first().locator('input[name="__csrf_token"]');

            let csrfCookie = browserCookies.filter(cookie => cookie.name === '__csrf_token-1');
            const csrfToken = await csrfInputLocator.inputValue();

            if (csrfCookie.length < 1) {
                await page.context().request.get('/csrftoken')
                    .then(() => {
                        this.checkCsrfCookie(page);
                    })
                    .catch((response) => {
                        console.error({ responseError: response.json() });
                    });

                return;
            }

            csrfCookie = csrfCookie.pop();

            if (csrfCookie.value !== csrfToken) {
                csrfCookie.value = csrfToken;

                page.context().addCookies([csrfCookie]);
            }
        }
    };
}());
