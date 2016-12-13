INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`)
VALUES
(9999, '99999', 3, 41.85, 35.17, 3.9, 3.28, '2017-01-17 11:14:23', 0, 17, 5, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 9, 'EUR', 1, 1, '127.0.0.1', 'desktop');

INSERT INTO `s_order_attributes` (`id`, `orderID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`)
VALUES
(999, 9999, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`, `ean`, `unit`, `pack_unit`)
VALUES
(99998, 9999, '99999', 170, 'SW10170', 39.95, 1, 'Sonnenbrille "Red"', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, '', '', '', ''),
(99999, 9999, '99999', 0, 'SHIPPINGDISCOUNT', -2, 1, 'Warenkorbrabatt', 0, 0, 0, '0000-00-00', 4, 0, 0, 19, '', '', '', '');

INSERT INTO `s_order_details_attributes` (`id`, `detailID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`)
VALUES
(99998, 99998, '', NULL, NULL, NULL, NULL, NULL),
(99999, 99999, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `s_order_billingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `ustid`, `additional_address_line1`, `additional_address_line2`, `title`)
VALUES
(9999, 111111, 9999, '', '', 'mr', '99999', 'Test', 'Buyer', 'Testweg 12', '12345', 'Teststadt', '', 2, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `s_order_billingaddress_attributes` (`id`, `billingID`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`) VALUES
(9999, 9999, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `s_user` (`id`, `password`, `encoder`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`, `default_billing_address_id`, `default_shipping_address_id`, `title`, `salutation`, `firstname`, `lastname`, `birthday`, `customernumber`)
VALUES
(111111, '$2y$10$4MyRZmdh5xYIs0yc8wW/T.ufadAHztqZRSr.1X8h8AZeS4YRxkAI6', 'bcrypt', 'test@example', 1, 0, '', 7, '2017-01-12', '2017-01-17 10:45:49', 'oksouuk3moh7b35ou4dknh4mj5', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, 5, 5, NULL, 'mr', 'Test', 'Buyer', NULL, '99999');

INSERT INTO `s_user_addresses` (`id`, `user_id`, `company`, `department`, `salutation`, `title`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `country_id`, `state_id`, `ustid`, `phone`, `additional_address_line1`, `additional_address_line2`)
VALUES
(222222, 111111, NULL, NULL, 'mr', NULL, 'Test', 'Buyer', 'Testweg 12', '12345', 'Teststadt', 2, NULL, NULL, NULL, NULL, NULL);