SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` LIKE 'show_cookie_note');

INSERT IGNORE INTO s_core_config_values (`element_id`, `shop_id`, `value`)
VALUES (@elementId, 1, 'b:0;');
