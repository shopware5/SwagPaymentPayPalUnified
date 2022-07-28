INSERT INTO `swag_payment_paypal_unified_payment_instruction` (`order_number`, `bank_name`, `account_holder`, `iban`, `bic`, `amount`, `reference`, `due_date`) VALUES
('21003',	'Test Sparkasse - Berlin',	'Paypal - Ratepay GmbH - Test Bank Account',	'DE12345678901234567890',	'BELADEBEXXX',	'45.94',	'7SW1992983152031M',	NULL);


INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`) VALUES
    (60999,	'21003',	1,	45.94,	38.6,	25.99,	21.84, '2022-07-28 06:07:01',	0,	12,	9,	'65Y142711V805773E',	'',	'',	'\n{\"jsonDescription\":\"Pay Upon Invoice Payment Instructions\",\"orderNumber\":\"20003\",\"bankName\":\"Test Sparkasse - Berlin\",\"accountHolder\":\"Paypal - Ratepay GmbH - Test Bank Account\",\"iban\":\"DE12345678901234567890\",\"bic\":\"BELADEBEXXX\",\"amount\":\"45.94\",\"dueDate\":null,\"reference\":\"7SW1992983152031M\"}\n',	0,	0,	'',	'7SW1992983152031M',	'',	'2022-07-28 08:07:19',	'',	'1',	10,	'EUR',	1,	1,	'172.19.0.0');

INSERT INTO `s_order_attributes` (`id`, `orderID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `swag_paypal_unified_payment_type`) VALUES
    (50999,	60999,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'PayPalPayUponInvoiceV2');

INSERT INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`, `ean`, `unit`, `pack_unit`) VALUES
    (20999,	60999,	'21003',	178,	'SW10178',	19.95,	1,	'Strandtuch \"Ibiza\"',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'Stück');


INSERT INTO `s_order_billingaddress` (`userID`, `orderID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `ustid`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
    (1,	60999,	'Muster GmbH',	'',	'mr',	'20001',	'Max',	'Mustermann',	'Musterstr. 55',	'55555',	'Musterhausen',	'05555 / 555555',	2,	3,	NULL,	NULL,	NULL,	NULL);

INSERT INTO `s_order_shippingaddress` (`userID`, `orderID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
    (1,	60999,	'shopware AG',	'',	'mr',	'Max',	'Mustermann',	'Mustermannstraße 92',	'48624',	'Schöppingen',	'',	2,	NULL,	'',	'',	'');
