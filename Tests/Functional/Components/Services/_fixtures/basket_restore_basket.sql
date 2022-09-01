INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
    (678000,	'restoreBasketSessionId',	1,	'Münsterländer Aperitif 16%',	3,	'SW10003',	0,	1,	14.95,	12.563025210084,	19,	'2022-04-22 10:49:02',	0,	0,	'',	'checkout',	'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36',	'',	1),
    (680000,	'restoreBasketSessionId',	1,	'Cigar Special 40%',	6,	'SW10006',	0,	1,	35.95,	30.210084033613,	19,	'2022-04-22 10:49:12',	0,	0,	'',	'checkout',	'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36',	'',	1),
    (682000,	'restoreBasketSessionId',	1,	'T.S. Privat 41,5 %',	8,	'SW10008',	0,	1,	49.95,	41.974789915966,	19,	'2022-04-22 10:49:23',	0,	0,	'',	'checkout',	'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36',	'',	1),
    (685000,	'restoreBasketSessionId',	1,	'Strandtuch \"Ibiza\"',	178,	'SW10178',	0,	1,	19.95,	16.764705882353,	19,	'2022-04-22 10:49:38',	0,	0,	'',	'checkout',	'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36',	'',	1),
    (691000,	'restoreBasketSessionId',	1,	'Warenkorbrabatt',	0,	'SHIPPINGDISCOUNT',	0,	1,	-2,	-1.68,	19,	'2022-04-22 10:50:15',	4,	0,	'',	'checkout',	'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36',	'',	1);

INSERT INTO `s_order_basket_attributes` (`id`, `basketID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`) VALUES
    (3700,	678000,	'Attr1',	'Attr2',	'Attr3',	'Attr4',	'Attr5',	'Attr6'),
    (3800,	680000,	'Attr1',	'Attr2',	'Attr3',	'Attr4',	'Attr5',	'Attr6'),
    (3900,	682000,	'Attr1',	'Attr2',	'Attr3',	'Attr4',	'Attr5',	'Attr6'),
    (4000,	685000,	'Attr1',	'Attr2',	'Attr3',	'Attr4',	'Attr5',	'Attr6');
