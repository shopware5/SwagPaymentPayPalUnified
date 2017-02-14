CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_payment_instruction (
    `id`             INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number`   VARCHAR(255),
    `bank_name`      VARCHAR(255),
    `account_holder` VARCHAR(255),
    `iban`           VARCHAR(255),
    `bic`            VARCHAR(255),
    `amount`         VARCHAR(255),
    `reference`      VARCHAR(255),
    `due_date`       DATETIME
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings (
    `id`                  INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`             INT(11),
    `client_id`           VARCHAR(255),
    `client_secret`       VARCHAR(255),
    `sandbox`             TINYINT(1),
    `show_sidebar_logo`   TINYINT(1),
    `logo_image`          VARCHAR(1024),
    `brand_name`          VARCHAR(255),
    `send_order_number`   TINYINT(1),
    `order_number_prefix` VARCHAR(255),
    `plus_active`         TINYINT(1),
    `plus_language`       VARCHAR(5)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;