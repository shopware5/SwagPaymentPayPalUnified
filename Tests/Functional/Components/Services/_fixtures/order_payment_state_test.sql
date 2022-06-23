INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`) VALUES
    (66,	'20006',	1,	224.99,	189.07,	75,	63.03, '2022-04-04 08:34:19',	0,	12,	7,	'2N350210JC439501D',	'',	'',	'',	0,	0,	'',	'4CA53157WG120912D',	'',	NULL,	'',	'1',	10,	'EUR',	1,	1,	'::',	'desktop');

INSERT INTO `s_order_attributes` (`id`, `orderID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`, `swag_paypal_unified_payment_type`) VALUES
    (11,	66,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'PayPalClassicV2');

INSERT INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`, `ean`, `unit`, `pack_unit`) VALUES
    (214,	66,	'20006',	148,	'SW10148',	149.99,	1,	'Reisetasche Gladstone Wildleder',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'');

INSERT INTO `s_order_billingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `ustid`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
    (6,	1,	66,	'Muster GmbH',	'',	'mr',	'20001',	'Max',	'Mustermann',	'Musterstr. 55',	'55555',	'Musterhausen',	'05555 / 555555',	2,	3,	NULL,	NULL,	NULL,	NULL);

INSERT INTO `s_order_shippingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
    (6,	1,	66,	'shopware AG',	'',	'mr',	'Max',	'Mustermann',	'Mustermannstraße 92',	'48624',	'Schöppingen',	'',	2,	NULL,	'',	'',	'');
