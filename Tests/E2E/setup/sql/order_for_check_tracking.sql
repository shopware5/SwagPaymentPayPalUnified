INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`) VALUES
    (60061,	'20999',	1,	45.94,	38.6,	25.99,	21.84, '2022-07-25 05:36:47',	0,	12,	7,	'1BJ07833AE2623110',	'',	'',	'',	0,	0,	'',	'4EG21713LT412453K',	'',	'2022-07-25 07:36:47',	'',	'1',	10,	'EUR',	1,	1,	'172.19.0.0',	'desktop');

INSERT INTO `s_order_attributes` (`id`, `orderID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `swag_paypal_unified_payment_type`) VALUES
    (60006,	60061,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'PayPalClassicV2');

INSERT INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`, `ean`, `unit`, `pack_unit`) VALUES
    (600210,	60061,	'20003',	178,	'SW10178',	19.95,	1,	'Strandtuch \"Ibiza\"',	0,	0,	0,	'1970-01-01 00:00:01',	0,	0,	1,	19,	'',	'',	'',	'Stück');

INSERT INTO `s_order_shippingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `countryID`, `stateID`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
    (60003,	1,	60061,	'shopware AG',	'',	'mr',	'Max',	'Mustermann',	'Mustermannstraße 92',	'48624',	'Schöppingen',	2,	NULL,	'',	'',	'');

INSERT INTO `s_order_billingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `countryID`, `stateID`, `ustid`, `additional_address_line1`, `additional_address_line2`, `title`, `phone`) VALUES
    (60003,	1,	60061,	'Muster GmbH',	'',	'mr',	'20001',	'Max',	'Mustermann',	'Musterstr. 55',	'55555',	'Musterhausen',	2,	3,	NULL,	NULL,	NULL,	NULL, '555-0815471142');
