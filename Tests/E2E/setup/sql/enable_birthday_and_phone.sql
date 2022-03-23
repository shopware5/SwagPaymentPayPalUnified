SET @phoneElementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showphonenumberfield' LIMIT 1);
SET @birthdayElementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showbirthdayfield' LIMIT 1);

INSERT IGNORE INTO `s_core_config_values` (element_id, shop_id, value) VALUES (@phoneElementId, 1, 'b:1;');
INSERT IGNORE INTO `s_core_config_values` (element_id, shop_id, value) VALUES (@birthdayElementId, 1, 'b:1;');
