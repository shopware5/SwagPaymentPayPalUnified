describe('All input types where validated correctly', () => {
    test('Test with checkboxes', () => {
        document.body.innerHTML = createTemplateWithCheckboxes();

        const formBaseFunction = getSwagPaymentPaypalFormBaseFunction('.form-selector', '.button-selector');

        let result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeFalsy();

        const checkbox = document.querySelector('.checkbox1');
        checkbox.checked = true;

        result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeTruthy();
    });

    test('Test with radio boxes', () => {
        document.body.innerHTML = createTemplateWithRadioBoxes();

        const formBaseFunction = getSwagPaymentPaypalFormBaseFunction('.form-selector', '.button-selector');

        let result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeFalsy();

        const radioBox = document.querySelector('.radio2');
        radioBox.checked = true;

        result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeTruthy();
    });

    test('Test with textField', () => {
        document.body.innerHTML = createTemplateWithTextFields();

        const formBaseFunction = getSwagPaymentPaypalFormBaseFunction('.form-selector', '.button-selector');

        let result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeFalsy();

        const textField = document.querySelector('.text1');
        textField.value = 'this is a test';

        result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeTruthy();
    });

    test('Test with textarea', () => {
        document.body.innerHTML = createTemplateWithTextAreas();

        const formBaseFunction = getSwagPaymentPaypalFormBaseFunction('.form-selector', '.button-selector');

        let result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeFalsy();

        const textarea = document.querySelector('.textarea1');
        textarea.value = 'this is a test';

        result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeTruthy();
    });

    test('Test with select', () => {
        document.body.innerHTML = createTemplateWithSelectFields();

        const formBaseFunction = getSwagPaymentPaypalFormBaseFunction('.form-selector', '.button-selector');

        let result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeFalsy();

        const option = document.querySelector('.value1-2');
        option.selected = true;

        result = formBaseFunction.checkFormValidity(false);

        expect(result).toBeTruthy();
    });

});

function getSwagPaymentPaypalFormBaseFunction(formSelector, submitButtonSelector) {
    return $.createSwagPaymentPaypalFormValidityFunctions(formSelector, submitButtonSelector, 'is--hidden', 'event-domain');
}

function createTemplateWithCheckboxes() {
    return `<div>
        <form class="form-selector">
            <input type="checkbox" name="checkbox1" class="checkbox1" required/>
            <input type="checkbox" name="checkbox2" class="checkbox2"/>
        </form>
        <input type="button" class="button-selector">
    </div>`;
}

function createTemplateWithRadioBoxes() {
    return `<div>
        <form class="form-selector">
            <input type="radio" name="radio" class="radio1" required/>
            <input type="radio" name="radio" class="radio2"/>
        </form>
        <input type="button" class="button-selector">
    </div>`;
}

function createTemplateWithTextFields() {
    return `<div>
        <form class="form-selector">
            <input type="text" name="text" class="text1" required/>
            <input type="text" name="text" class="text2"/>
        </form>
        <input type="button" class="button-selector">
    </div>`;
}

function createTemplateWithTextAreas() {
    return `<div>
        <form class="form-selector">
            <textarea name="textarea" class="textarea1" required></textarea>
            <textarea name="textarea" class="textarea2"></textarea>
        </form>
        <input type="button" class="button-selector">
    </div>`;
}

function createTemplateWithSelectFields() {
    return `<div>
        <form class="form-selector">
            <select name="select1" id="select1" required>
                <option></option>
                <option class="value1-1" value="value1-1">value1-1</option>
                <option class="value1-2" value="value1-2">value1-2</option>
                <option class="value1-3" value="value1-3">value1-3</option>
                <option class="value1-4" value="value1-4">value1-4</option>
            </select>

            <select name="select2" id="select2">
                <option></option>
                <option class="value2-1" value="value2-1">value2-1</option>
                <option class="value2-2" value="value2-2">value2-2</option>
                <option class="value2-3" value="value2-3">value2-3</option>
                <option class="value2-4" value="value2-4">value2-4</option>
            </select>

        </form>
        <input type="button" class="button-selector">
    </div>`;
}
