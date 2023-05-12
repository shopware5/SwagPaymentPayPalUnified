INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`) VALUES
    (9586421, '0', 1, 21.85, 18.36, 3.9, 3.28, '2023-05-12 06:08:47', -1, 0, 5, 'anyPayPalOrderId', '', '', '', 0, 0, '', 'anyPayPalOrderId', '', NULL, '', '1', 9, 'EUR', 1, 1, NULL, 'desktop');

INSERT INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`, `ean`, `unit`, `pack_unit`, `articleDetailID`) VALUES
    (95864206, 9586421, '0', 178, 'SW10178', 19.95, 1, 'Strandtuch \"Ibiza\"', 0, 0, 0, NULL, 0, 0, 1, 19, '', NULL, NULL, NULL, 407),
    (95864207, 9586421, '0', 0, 'SHIPPINGDISCOUNT', -2, 1, 'Warenkorbrabatt', 0, 0, 0, NULL, 4, 0, 0, 19, '', NULL, NULL, NULL, NULL);
