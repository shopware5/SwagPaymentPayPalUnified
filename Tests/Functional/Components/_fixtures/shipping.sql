INSERT INTO s_premium_dispatch (id, name, type, description, comment, active, position, calculation, surcharge_calculation, tax_calculation, shippingfree, multishopID, customergroupID, bind_shippingfree, bind_time_from, bind_time_to, bind_instock, bind_laststock, bind_weekday_from, bind_weekday_to, bind_weight_from, bind_weight_to, bind_price_from, bind_price_to, bind_sql, status_link, calculation_sql) VALUES
(500, '500', 0, '', '', 1, 1, 1, 3, 0, null, null, null, 0, null, null, null, 0, null, null, null, 1.000, null, null, null, '', null)
,(600, '600', 0, '', '', 1, 1, 1, 3, 0, null, null, null, 0, null, null, null, 0, null, null, null, 1.000, null, null, null, '', null)
,(700, '700', 0, '', '', 1, 1, 1, 3, 0, null, null, null, 0, null, null, null, 0, null, null, null, 1.000, null, null, null, '', null)                                                                                                                                                                                                                                                                                                                                                                                                                 ,(300, '300', 0, '', '', 1, 1, 1, 3, 0, null, null, null, 0, null, null, null, 0, null, null, null, 1.000, null, null, null, '', null)
;

INSERT INTO s_premium_dispatch_attributes (id, dispatchID, swag_paypal_unified_carrier) VALUES
(500, 500, 'DHL')
,(600, 600, null)
,(700, 700, '')
;
