ALTER TABLE swag_payment_paypal_unified_settings_express ADD button_locale varchar(5);

TRUNCATE TABLE swag_payment_paypal_unified_settings_general;
TRUNCATE TABLE swag_payment_paypal_unified_settings_express;

INSERT INTO swag_payment_paypal_unified_settings_general (shop_id, active, client_id, client_secret,
                                                          sandbox_client_id, sandbox_client_secret, sandbox,
                                                          show_sidebar_logo, brand_name, landing_page_type,
                                                          order_number_prefix, display_errors,
                                                          advertise_returns, use_smart_payment_buttons, submit_cart,
                                                          intent, button_style_color, button_style_shape,
                                                          button_style_size, button_locale, paypal_payer_id,
                                                          sandbox_paypal_payer_id)
VALUES (2, 1, '', '', 'foo',
        'bar', 1, 0, '', 'NO_PREFERENCE',
        '', 0, 0, 0, 1, 'CAPTURE', 'gold', 'rect', 'large', '', '', '3HLCE2PRFSWU6'),
       (3, 1, '', '', 'foo',
        'bar', 1, 0, '', 'NO_PREFERENCE',
        '', 0, 0, 0, 1, 'CAPTURE', 'gold', 'rect', 'large', 'fr_XC', '', '3HLCE2PRFSWU6');


INSERT INTO swag_payment_paypal_unified_settings_express (shop_id, detail_active, cart_active, off_canvas_active,
                                                          login_active, listing_active, button_style_color,
                                                          button_style_shape, button_style_size, button_locale,
                                                          submit_cart)
VALUES (2, 1, 1, 1, 1, 1, 'gold', 'rect', 'medium', 'en_US', 0),
(3, 1, 1, 1, 1, 1, 'gold', 'rect', 'medium', 'en_US', 0);


INSERT INTO `s_core_paymentmeans` (`name`, `description`, `template`, `class`, `table`, `hide`, `additionaldescription`, `debit_percent`, `surcharge`, `surchargestring`, `position`, `active`, `esdactive`, `embediframe`, `hideprospect`, `action`, `pluginID`, `source`, `mobile_inactive`) VALUES
    ('SwagPaymentPayPalUnifiedOXXO',	'',	'',	'',	'',	0,	'',	0,	0,	'',	1,	0,	0,	'',	0,	'',	58,	NULL,	0);
