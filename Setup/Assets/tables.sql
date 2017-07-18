CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_payment_instruction (
    `id`             INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number`   VARCHAR(255) NOT NULL,
    `bank_name`      VARCHAR(255) NOT NULL,
    `account_holder` VARCHAR(255) NOT NULL,
    `iban`           VARCHAR(255) NOT NULL,
    `bic`            VARCHAR(255) NOT NULL,
    `amount`         VARCHAR(255) NOT NULL,
    `reference`      VARCHAR(255) NOT NULL,
    `due_date`       DATETIME
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings (
    `id`                                INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                           INT(11) NOT NULL,
    `active`                            TINYINT(1),
    `client_id`                         VARCHAR(255),
    `client_secret`                     VARCHAR(255),
    `sandbox`                           TINYINT(1),
    `show_sidebar_logo`                 TINYINT(1) NOT NULL,
    `logo_image`                        VARCHAR(1024),
    `brand_name`                        VARCHAR(255),
    `send_order_number`                 TINYINT(1) NOT NULL,
    `order_number_prefix`               VARCHAR(255),
    `use_in_context`                    TINYINT(1) NOT NULL,
    `paypal_payment_intent`             INT(11),
    `web_profile_id`                    VARCHAR(255),
    `web_profile_id_ec`                 VARCHAR(255),
    `plus_active`                       TINYINT(1) NOT NULL,
    `plus_restyle`                      TINYINT(1) NOT NULL,
    `plus_language`                     VARCHAR(5),
    `installments_active`               TINYINT(1) NOT NULL,
    `installments_presentment_detail`   INT(11),
    `installments_presentment_cart`     INT(11),
    `installments_show_logo`            TINYINT(1) NOT NULL,
    `ec_active`                         TINYINT(1) NOT NULL,
    `ec_detail_active`                  TINYINT(1) NOT NULL,
    `ec_button_style_color`             VARCHAR(255),
    `ec_button_style_shape`             VARCHAR(255),
    `ec_button_style_size`              VARCHAR(255),
    `ec_submit_cart`                    TINYINT(1) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_financing_information (
    `id`              INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `payment_id`    VARCHAR(255) NOT NULL,
    `fee_amount`      DOUBLE NOT NULL,
    `total_cost`      DOUBLE NOT NULL,
    `term`            INT(11) NOT NULL,
    `monthly_payment` DOUBLE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
