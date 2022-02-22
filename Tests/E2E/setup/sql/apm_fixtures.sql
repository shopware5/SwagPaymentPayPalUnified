UPDATE s_core_countries
set active = 1
WHERE true;
INSERT IGNORE INTO `s_core_currencies` (`id`, `currency`, `name`, `standard`, `factor`, `templatechar`, `symbol_position`,
                                        `position`)
VALUES (3, 'PLN', 'Polnischer Złoty', 0, 4.6331, 'Złoty', 0, 0),
       (4, 'MXN', 'Mexikanischer Peso', 0, 22.915, 'Peso', 0, 0),
       (5, 'SEK', 'Schwedische Krone', 0, 10.5848, 'Krone', 0, 0);
INSERT IGNORE INTO `s_core_shops` (`id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `base_url`, `hosts`,
                                   `secure`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`,
                                   `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`)
VALUES (3, 1, 'Poland', 'Poland', 0, NULL, NULL, '/pl', '', 0, NULL, NULL, 3, 187, 3, 1, 2, 0, 0, 1),
       (4, 1, 'Belgium', 'Belgium', 0, NULL, NULL, '/be', '', 0, NULL, NULL, 3, 43, 1, 1, 2, 0, 0, 1),
       (5, 1, 'Austria', 'Austria', 0, NULL, NULL, '/at', '', 0, NULL, NULL, 3, 42, 1, 1, NULL, 0, 0, 1),
       (6, 1, 'Netherlands', 'Netherlands', 0, NULL, NULL, '/nl', '', 0, NULL, NULL, 3, 176, 1, 1, 2, 0, 0, 1),
       (7, 1, 'Portugal', 'Portugal', 0, NULL, NULL, '/pt', '', 0, NULL, NULL, 3, 190, 1, 1, 2, 0, 0, 1),
       (8, 1, 'Mexico', 'Mexico', 0, NULL, NULL, '/mx', '', 0, NULL, NULL, 3, 88, 4, 1, 2, 0, 0, 1),
       (9, 1, 'Spain', 'Spain', 0, NULL, NULL, '/esp', '', 0, NULL, NULL, 3, 85, 1, 1, 2, 0, 0, 1),
       (10, 1, 'Italy', 'Italy', 0, NULL, NULL, '/it', '', 0, NULL, NULL, 3, 136, 1, 1, 2, 0, 0, 1),
       (11, 1, 'Estonia', 'Estonia', 0, NULL, NULL, '/ee', '', 0, NULL, NULL, 3, 98, 1, 1, 2, 0, 0, 1),
       (12, 1, 'Finland', 'Finland', 0, NULL, NULL, '/fi', '', 0, NULL, NULL, 3, 102, 1, 1, 2, 0, 0, 1),
       (13, 1, 'Sweden', 'Sweden', 0, NULL, NULL, '/sw', '', 0, NULL, NULL, 3, 221, 5, 1, 2, 0, 0, 1);
INSERT IGNORE INTO `s_core_shop_currencies` (`shop_id`, `currency_id`)
VALUES (1, 3),
       (1, 4),
       (1, 5);
