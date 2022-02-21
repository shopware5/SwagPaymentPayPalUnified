CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_general
(
    `id`                         INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                    INT(11)      NOT NULL,
    `active`                     TINYINT(1),
    `client_id`                  VARCHAR(255),
    `client_secret`              VARCHAR(255),
    `sandbox_client_id`          VARCHAR(255),
    `sandbox_client_secret`      VARCHAR(255),
    `sandbox`                    TINYINT(1),
    `show_sidebar_logo`          TINYINT(1)   NOT NULL,
    `brand_name`                 VARCHAR(255),
    `landing_page_type`          VARCHAR(255),
    `send_order_number`          TINYINT(1)   NOT NULL,
    `order_number_prefix`        VARCHAR(255),
    `use_in_context`             TINYINT(1)   NOT NULL,
    `log_level`                  INT(11)      NOT NULL,
    `display_errors`             TINYINT(1)   NOT NULL,
    `advertise_returns`          TINYINT(1)   NOT NULL,
    `use_smart_payment_buttons`  TINYINT(1)   NOT NULL,
    `merchant_location`          VARCHAR(255) NOT NULL,
    `submit_cart`                TINYINT(1)   NOT NULL,
    `intent`                     VARCHAR(255) default 'CAPTURE',
    `button_style_color`         VARCHAR(255) NULL,
    `button_style_shape`         VARCHAR(255) NULL,
    `button_style_size`          VARCHAR(255) NULL,
    `button_locale`              VARCHAR(5),
    `paypal_payer_id`         VARCHAR(255) NULL,
    `sandbox_paypal_payer_id` VARCHAR(255) NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_installments
(
    `id`                     INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                INT(11)    NOT NULL,
    `advertise_installments` TINYINT(1) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_express
(
    `id`                 INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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
    `submit_cart`        TINYINT(1) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_plus
(
    `id`                            INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                       INT(11)    NOT NULL,
    `active`                        TINYINT(1),
    `restyle`                       TINYINT(1) NOT NULL,
    `integrate_third_party_methods` TINYINT(1) NOT NULL,
    `payment_name`                  VARCHAR(255),
    `payment_description`           VARCHAR(255),
    `ppcp_active`                   TINYINT(1),
    `sandbox_ppcp_active`           TINYINT(1)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_payment_instruction
(
    `id`             INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_pay_upon_invoice
(
    `id`                           INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                      INT(11)    NOT NULL,
    `onboarding_completed`         TINYINT(1) NOT NULL,
    `sandbox_onboarding_completed` TINYINT(1) NOT NULL,
    `active`                       TINYINT(1) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_advanced_credit_debit_card
(
    `id`                           INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                      INT(11)    NOT NULL,
    `onboarding_completed`         TINYINT(1) NOT NULL,
    `sandbox_onboarding_completed` TINYINT(1) NOT NULL,
    `active`                       TINYINT(1) NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;
