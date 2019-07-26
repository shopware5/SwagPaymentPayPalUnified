CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_general (
    `id`                        INT(11)      UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                   INT(11)      NOT NULL,
    `active`                    TINYINT(1),
    `client_id`                 VARCHAR(255),
    `client_secret`             VARCHAR(255),
    `sandbox`                   TINYINT(1),
    `show_sidebar_logo`         TINYINT(1)   NOT NULL,
    `brand_name`                VARCHAR(255),
    `landing_page_type`         VARCHAR(255),
    `send_order_number`         TINYINT(1)   NOT NULL,
    `order_number_prefix`       VARCHAR(255),
    `use_in_context`            TINYINT(1)   NOT NULL,
    `log_level`                 INT(11)      NOT NULL,
    `display_errors`            TINYINT(1)   NOT NULL,
    `advertise_returns`         TINYINT(1)   NOT NULL,
    `use_smart_payment_buttons` TINYINT(1)   NOT NULL,
    `merchant_location`         VARCHAR(255) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_installments (
    `id`                 INT(11)    UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`            INT(11)    NOT NULL,
    `active`             TINYINT(1),
    `presentment_detail` INT(11),
    `presentment_cart`   INT(11),
    `show_logo`          TINYINT(1) NOT NULL,
    `intent`             INT(11)    NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_express (
    `id`                 INT(11)    UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`            INT(11)    NOT NULL,
    `detail_active`      TINYINT(1) NOT NULL,
    `cart_active`        TINYINT(1) NOT NULL,
    `off_canvas_active`  TINYINT(1) NOT NULL,
    `login_active`       TINYINT(1) NOT NULL,
    `listing_active`     TINYINT(1) NOT NULL,
    `button_style_color` VARCHAR(255),
    `button_style_shape` VARCHAR(255),
    `button_style_size`  VARCHAR(255),
    `button_locale`      VARCHAR(5),
    `submit_cart`        TINYINT(1) NOT NULL,
    `intent`             INT(11)    NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_plus (
    `id`                            INT(11)    UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                       INT(11)    NOT NULL,
    `active`                        TINYINT(1),
    `restyle`                       TINYINT(1) NOT NULL,
    `integrate_third_party_methods` TINYINT(1) NOT NULL,
    `payment_name`                  VARCHAR(255),
    `payment_description`           VARCHAR(255)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_financing_information (
    `id`              INT(11)      UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `payment_id`      VARCHAR(255) NOT NULL,
    `fee_amount`      DOUBLE       NOT NULL,
    `total_cost`      DOUBLE       NOT NULL,
    `term`            INT(11)      NOT NULL,
    `monthly_payment` DOUBLE       NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_payment_instruction (
    `id`             INT(11)      UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number`   VARCHAR(255) NOT NULL,
    `bank_name`      VARCHAR(255) NOT NULL,
    `account_holder` VARCHAR(255) NOT NULL,
    `iban`           VARCHAR(255) NOT NULL,
    `bic`            VARCHAR(255) NOT NULL,
    `amount`         VARCHAR(255) NOT NULL,
    `reference`      VARCHAR(255) NOT NULL,
    `due_date`       DATETIME
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;
