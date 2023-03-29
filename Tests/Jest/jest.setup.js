window.$ = window.jQuery = require('./../../../../../themes/Frontend/Responsive/node_modules/jquery');
require('./matchMedia.mock.js');
require('./paypal.mock.js');

require('./../../../../../themes/Frontend/Responsive/frontend/_public/src/js/jquery.plugin-base.js');
require('./../../../../../themes/Frontend/Responsive/frontend/_public/src/js/jquery.state-manager.js');

// Base functions
require('./../../Resources/views/frontend/_public/src/js/jquery.button-config');
require('./../../Resources/views/frontend/_public/src/js/jquery.create-url-function');
require('./../../Resources/views/frontend/_public/src/js/jquery.create_order_function');
require('./../../Resources/views/frontend/_public/src/js/jquery.form_validity_functions');

// Plugins
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.advanced-credit-debit-card');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.advanced-credit-debit-card-fallback');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.custom-shipping-payment');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.express-checkout-button');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.in-context-checkout');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.installments-banner');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.pay-later');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall-confirm');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall-shipping-payment');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.polling');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.pui-phone-number-field');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.smart-payment-buttons');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified-fraudnet');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified-sepa');
require('./../../Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified-sepa-eligibility');
