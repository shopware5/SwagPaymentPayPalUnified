describe('Check checkout change cart plugin', () => {

    test('init function', () => {
        window.document.body.innerHTML = createTemplate();

        delete window.location;
        window.location = {};
        window.location.search = 'token=anyToken';

        const plugin = registerPlugin();

        const form = plugin.$form;

        expect(plugin.payPalToken).toBe('anyToken');
        expect(form[0].action).toBe('https://localhost/test?expressCheckout=true&token=anyToken&payPalCartHasChanged=true');
    });

    test('init function without token', () => {
        window.document.body.innerHTML = createTemplate();

        delete window.location;
        window.location = {};

        const plugin = registerPlugin();

        const form = plugin.$form;

        expect(plugin.payPalToken).toBeNull();
        expect(form).toBeUndefined();
    });

    test('checkHasToken function to be true', () => {
        window.document.body.innerHTML = createTemplate();

        delete window.location;
        window.location = {};
        window.location.search = 'token=anyToken';

        const plugin = registerPlugin();


        expect(plugin.checkHasToken()).toBe(true);
        expect(plugin.payPalToken).toBe('anyToken');
    });

    test('checkHasToken function to be false', () => {
        window.document.body.innerHTML = createTemplate();

        delete window.location;
        window.location = {};

        const plugin = registerPlugin();

        expect(plugin.checkHasToken()).toBe(false);
    });

    test('loadParams function to be anyToken', () => {
        window.document.body.innerHTML = createTemplate();

        delete window.location;
        window.location = {};
        window.location.search = 'token=anyToken';

        const plugin = registerPlugin();

        plugin.loadParams();

        expect(plugin.payPalToken).toBe('anyToken');
    });

    test('getForm function to be a form element', () => {
        window.document.body.innerHTML = createTemplate();

        delete window.location;
        window.location = {};
        window.location.search = 'token=anyToken';

        const plugin = registerPlugin();

        const form = plugin.getForm();

        expect(form).toBeDefined();
        expect(form[0]).toBeInstanceOf(HTMLFormElement);
    });

    test('updateFormAction function', () => {
        window.document.body.innerHTML = createTemplate();

        delete window.location;
        window.location = {};
        window.location.search = 'token=anyToken';

        const plugin = registerPlugin();

        plugin.updateFormAction();

        expect(plugin.$form[0].action).toBe('https://localhost/test?expressCheckout=true&token=anyToken&payPalCartHasChanged=true');
    });
});

function createTemplate() {
    return `<div class="testDiv"
                 data-paypalUnifiedEcButtonChangeCart="true"
                 data-addVoucherFormSelector="testForm"
                 data-expressCheckoutParameterKey="expressCheckout"
                 data-payPalCartHasChangedKey="payPalCartHasChanged"
                 data-expressCheckoutTokenKey="token">
                 <form action="https://localhost/test"></form>
           </div>`;
}

function registerPlugin() {
    const $testDiv = $('.testDiv');

    $testDiv.swagPayPalUnifiedExpressCheckoutChangeCart();

    return $testDiv.data('plugin_swagPayPalUnifiedExpressCheckoutChangeCart');
}
