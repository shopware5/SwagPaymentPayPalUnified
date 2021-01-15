INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`)
VALUES
(100080,	'2000101',	2,	998.56,	839.13,	0,	0, '2021-08-30 10:15:54',	0,	17,	4,	'',	'',	'',	'',	1,	0,	'',	'',	'',	NULL,	'',	'1',	9,	'EUR',	1,	1,	'217.86.205.141',	NULL),
(100081,	'2000102',	1,	201.86,	169.63,	0,	0, '2021-08-31 08:51:46',	0,	17,	4,	'',	'',	'',	'',	0,	0,	'',	'',	'',	NULL,	'',	'1',	9,	'EUR',	1,	1,	'217.86.205.141',	NULL),
(100082,	'2000103',	1,	201.86,	169.63,	0,	0, '2021-01-12 08:51:46',	0,	17,	4,	'',	'',	'',	'',	0,	0,	'',	'',	'',	NULL,	'',	'1',	9,	'EUR',	1,	1,	'217.86.205.141',	NULL),
(100083,	'2000104',	1,	201.86,	169.63,	0,	0, '2021-08-31 08:51:46',	0,	17,	7,	'',	'',	'',	'',	0,	0,	'',	'',	'',	NULL,	'',	'1',	9,	'EUR',	1,	1,	'217.86.205.141',	NULL);


INSERT INTO `s_order_attributes` (`id`, `orderID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `swag_paypal_unified_payment_type`)
VALUES
(1025,	100080,	'',	'',	'',	'',	'',	'',	'PayPalClassic'),
(1026,	100081,	'',	'',	'',	'',	'',	'',	'PayPalClassic'),
(1028,	100082,	'',	'',	'',	'',	'',	'',	'PayPalClassic'),
(1029,	100083,	'',	'',	'',	'',	'',	'',	'PayPalClassic');

