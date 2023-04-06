describe('All input types where collected for request data', () => {
    test('Test with different input types', () => {
        prepareStorageManager();

        window.document.body.innerHTML = createTemplate();

        window.StateManager.addPlugin('*[data-storage-field="true"]', 'swStorageField');
        window.StateManager._initPlugin('*[data-storage-field="true"]', 'swStorageField');

        const createOrderFunctions = $.createSwagPaymentPaypalCreateOrderFunction('', { opts: { } });

        const result = createOrderFunctions.createExtraData();

        cleanUpStorageManager();

        expect(result.snewsletter).toBe('true');
        expect(result.textfield).toBe('textfieldValue');
        expect(result.radio).toBe('radioOneValue');
        expect(result.checkbox).toBe('checkboxValue');
        expect(result.selectfield).toBe('selectValue');
        expect(result.textarea).toBe('textareaValue');
        expect(result.scomment).toBe('This is a user comment');
    });

    test('Test different input types without the swStorageField plugin', () => {
        // unset the plugin swStorageField
        $.fn.swStorageField = undefined;
        window.document.body.innerHTML = createTemplate();

        prepareFormFields();

        const createOrderFunctions = $.createSwagPaymentPaypalCreateOrderFunction('', { opts: { } });

        const result = createOrderFunctions.createExtraData();

        expect(result.snewsletter).toBe('true');
        expect(result.textfield).toBe('textfieldValue');
        expect(result.radio).toBe('radioOneValue');
        expect(result.checkbox).toBe('true');
        expect(result.selectfield).toBe('1');
        expect(result.textarea).toBe('textareaValue');
        expect(result.scomment).toBe('This is a user comment');
    });
});

function prepareFormFields() {
    $('#sNewsletter').val('true');
    $('#textField').val('textfieldValue');
    $('#radio1').val('radioOneValue');
    $('#checkbox1').val('true');
    $('#selectField').val('1');
    $('#textArea').val('textareaValue');
    $('#sComment').val('This is a user comment');
}

function prepareStorageManager() {
    window.StorageManager.setItem('session', 'sw-local-snewsletter', 'true');
    window.StorageManager.setItem('session', 'sw-local-scomment', 'This is a user comment');
    window.StorageManager.setItem('session', 'sw-local-radio', 'radioOneValue');
    window.StorageManager.setItem('session', 'sw-local-textfield', 'textfieldValue');
    window.StorageManager.setItem('session', 'sw-local-textarea', 'textareaValue');
    window.StorageManager.setItem('session', 'sw-local-checkbox', 'checkboxValue');
    window.StorageManager.setItem('session', 'sw-local-selectfield', 'selectValue');
}

function cleanUpStorageManager() {
    window.StorageManager.clear('session');
}

function createTemplate() {
    return `<div>
        <form class="testForm">
            <div>
                <input type="checkbox" name="sNewsletter" id="sNewsletter" value="checkbox1" data-storage-field="true"/>
                <label for="sNewsletter">sNewsletter</label>
            </div>

            <div>
                <input type="text" name="textField" id="textField" data-storage-field="true"/>
                <label for="textField">Text Field</label>
            </div>

            <div>
                <input type="radio" name="radio" id="radio1" value="radio1" data-storage-field="true"/>
                <label for="radio1">radio1</label>
            </div>

            <div>
                <input type="checkbox" name="checkbox" id="checkbox1" value="checkbox1" data-storage-field="true"/>
                <label for="checkbox1">checkbox1</label>
            </div>

            <div>
            <label for="selectField">selectField</label>
                <select name="selectField" id="selectField" data-storage-field="true">
                    <option value="1">one</option>
                    <option value="2">two</option>
                    <option value="3">three</option>
                </select>
            </div>

            <div>
                <label for="textArea">textArea</label>
                <textarea name="textArea" id="textArea" data-storage-field="true"></textarea>
            </div>

            <div>
                <label for="textArea">comment</label>
                <textarea name="sComment" id="sComment" data-storage-field="true"></textarea>
            </div>
        </form>
    </div>`;
}
