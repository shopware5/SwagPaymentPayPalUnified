UPDATE s_core_translations
SET `objectdata` = 'a:4:{i:1;a:5:{s:4:\"name\";s:7:\"Invoice\";s:40:\"PayPal_Unified_Instructions_Footer_Value\";s:62:\"<p>Footer content PayPal Plus Invoice and Pay upon Invoice</p>\";s:40:\"PayPal_Unified_Instructions_Footer_Style\";s:53:\"Footer style PayPal Plus Invoice and Pay upon Invoice\";s:35:\"PayPal_Unified_Ratepay_Instructions\";s:44:\"<p>Content info content Pay upon Invoice</p>\";s:41:\"PayPal_Unified_Ratepay_Instructions_Style\";s:42:\"Content info style PayPal Pay upon Invoice\";}i:2;a:1:{s:4:\"name\";s:18:\"Notice of delivery\";}i:3;a:1:{s:4:\"name\";s:6:\"Credit\";}i:4;a:1:{s:4:\"name\";s:12:\"Cancellation\";}}'
WHERE `objecttype` LIKE 'documents'
AND `objectlanguage` = 2
AND `objectkey` = 1;
