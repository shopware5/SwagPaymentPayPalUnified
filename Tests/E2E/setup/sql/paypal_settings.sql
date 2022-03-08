INSERT INTO swag_payment_paypal_unified_settings_advanced_credit_debit_card (id, shop_id, onboarding_completed, sandbox_onboarding_completed, active) VALUES
(1, 1, 0, 1, 1);

INSERT INTO swag_payment_paypal_unified_settings_express (id, shop_id, detail_active, cart_active, off_canvas_active, login_active, listing_active, button_style_color, button_style_shape, button_style_size, button_locale, submit_cart) VALUES
(1, 1, 1, 1, 1, 1, 1, 'gold', 'rect', 'medium', '', 0);

INSERT INTO swag_payment_paypal_unified_settings_general (id, shop_id, active, client_id, client_secret, sandbox_client_id, sandbox_client_secret, sandbox, show_sidebar_logo, brand_name, landing_page_type, send_order_number, order_number_prefix, use_in_context, log_level, display_errors, advertise_returns, use_smart_payment_buttons, submit_cart, intent, button_style_color, button_style_shape, button_style_size, button_locale, paypal_payer_id, sandbox_paypal_payer_id) VALUES
(1, 1, 1, '', '', 'sandbox_client_id::replace::me', 'sandbox_client_secret::replace::me', 1, 0, '', 'NO_PREFERENCE', 0, '', 1, 0, 0, 0, 0, 1, 'CAPTURE', 'gold', 'rect', 'large', '', '', 'sandbox_paypal_payer_id::replace::me');

INSERT INTO swag_payment_paypal_unified_settings_installments (id, shop_id, advertise_installments) VALUES
(1, 1, 1);

INSERT INTO swag_payment_paypal_unified_settings_pay_upon_invoice (id, shop_id, onboarding_completed, sandbox_onboarding_completed, active, customer_service_instructions) VALUES
(1, 1, 0, 1, 1, 'Fill');

INSERT INTO swag_payment_paypal_unified_settings_plus (id, shop_id, active, restyle, integrate_third_party_methods, payment_name, payment_description, ppcp_active, sandbox_ppcp_active) VALUES
(1, 1, 0, 0, 0, 'PayPal, Lastschrift oder Kreditkarte', 'Zahlung per Lastschrift oder Kreditkarte ist auch ohne PayPal Konto möglich', 0, 0);
