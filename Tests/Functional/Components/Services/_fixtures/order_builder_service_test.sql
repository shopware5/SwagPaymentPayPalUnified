INSERT INTO `s_user` (`id`, `password`, `encoder`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`, `default_billing_address_id`, `default_shipping_address_id`, `title`, `salutation`, `firstname`, `lastname`, `birthday`, `customernumber`) VALUES
    (3,	'$2y$10$GTCr6Y6saAGWQwG2eQntoevo7bxAtNUf2b2o/xDF3qlvOK.IMncuu',	'bcrypt',	'phpUnit.tester@test.com',	1,	0,	'',	7,	'2022-01-05',	'2022-01-05 07:35:46',	'9219869a66e0a4d45be2c7d48f234bfd',	0,	'',	0,	'EK',	0,	'1',	1,	'',	NULL,	'',	0,	NULL,	5,	5,	NULL,	'mr',	'PhpUnit',	'Tester',	'1970-01-01',	'20005');

INSERT INTO `s_user_addresses` (`id`, `user_id`, `company`, `department`, `salutation`, `title`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `country_id`, `state_id`, `ustid`, `phone`, `additional_address_line1`, `additional_address_line2`) VALUES
    (5,	3,	NULL,	NULL,	'mr',	NULL,	'PhpUnit',	'Tester',	'FooBarStreet, 42',	'12345',	'SinCity',	2,	NULL,	NULL,	'123456789',	NULL,	NULL);

INSERT INTO `s_user_addresses_attributes` (`id`, `address_id`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`) VALUES
    (3,	5,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);

INSERT INTO `swag_payment_paypal_unified_settings_general` (`id`, `shop_id`, `active`, `client_id`, `client_secret`, `sandbox`, `show_sidebar_logo`, `brand_name`, `landing_page_type`, `send_order_number`, `order_number_prefix`, `use_in_context`, `log_level`, `display_errors`, `advertise_returns`, `use_smart_payment_buttons`, `submit_cart`, `intent`) VALUES
    (1,	1,	1,	'AQOs-Zqi0kU3aLWsrLVActt1d1u2AhpdLeiddWssUq-y7WnT_kPv4TEFNkTShvrlLFlschWKIi9JCVkl',	'EMATe3fxb-01TvYZxs1QFKf2N2WpQjE_os-XtfBddY5hjyHJ71SEpU4dr7woMlWz5_OYw-kpSwMRQvoD',	1,	1,	'',	'Login',	0,	'',	0,	1,	1,	0,	0,	1,	'CAPTURE');
