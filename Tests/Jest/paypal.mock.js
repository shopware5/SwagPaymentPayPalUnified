// PayPal SDK mock
window.paypal = {
    Buttons: function () {
        return {
            render: jest.fn()
        };
    },
};
