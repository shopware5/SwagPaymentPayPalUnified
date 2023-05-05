describe('Check address patch plugin', () => {

    test('init function', () => {
        window.document.body.innerHTML = createTemplate();
        const plugin = registerPlugin();

        expect(plugin.opts.patchAddressUrl).toBe('aNiceUrlToPatchTheAddress');
        expect(plugin.opts.requireAddressPatchKey).toBe('aNewRequireAddressPatchKey');
        expect(plugin.opts.tokenKey).toBe('aNewTokenKey');

        expect($.event.global['plugin/swAddressSelection/onAfterSave']).toBeTruthy();
        expect($.event.global['plugin/swAddressEditor/onAfterSave']).toBeTruthy();
    });
});

function createTemplate() {
    return `<div class="testDiv"
                 data-swagPayPalUnifiedExpressAddressPatch="true"
                 data-patchAddressUrl="aNiceUrlToPatchTheAddress"
                 data-requireAddressPatchKey="aNewRequireAddressPatchKey"
                 data-tokenKey="aNewTokenKey">
           </div>`;
}

function registerPlugin() {
    const $testDiv = $('.testDiv');

    $testDiv.swagPayPalUnifiedExpressAddressPatch();

    return $testDiv.data('plugin_swagPayPalUnifiedExpressAddressPatch');
}
