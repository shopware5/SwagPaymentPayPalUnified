CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_php_unit_test_table_one
(
    `id`                     INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                INT(11) NOT NULL,
    CONSTRAINT unique_shop_id UNIQUE (`shop_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_php_unit_test_table_two
(
    `id`                     INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                INT(11) NOT NULL,
    CONSTRAINT unique_shop_id UNIQUE (`shop_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_php_unit_test_table_three
(
    `id`                     INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                INT(11) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_php_unit_test_table_four
(
    `id`                     INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                INT(11) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;
