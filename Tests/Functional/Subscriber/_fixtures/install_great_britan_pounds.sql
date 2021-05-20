INSERT INTO `s_core_currencies` (`id`, `currency`, `name`, `standard`, `factor`, `templatechar`, `symbol_position`, `position`)
VALUES (3,	'GBP',	'Britische Pfund',	0,	0.86,	' & pound;',	0,	0);

INSERT INTO `s_core_shop_currencies` (`shop_id`, `currency_id`) VALUES
(1,	3);
