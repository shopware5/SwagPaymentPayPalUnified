SET @paypalpamentid = (SELECT `id` FROM s_core_paymentmeans WHERE `name`='SwagPaymentPayPalUnified');

INSERT INTO `s_core_rulesets` (`id`, `paymentID`, `rule1`, `value1`, `rule2`, `value2`) VALUES
(8,	@paypalpamentid,	'ARTICLESFROM',	'6',	'',	'');
