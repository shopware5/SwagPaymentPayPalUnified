INSERT INTO s_order (id, ordernumber, userID, invoice_amount, invoice_amount_net, invoice_shipping, invoice_shipping_net, ordertime, status, cleared, paymentID, transactionID, comment, customercomment, internalcomment, net, taxfree, partnerID, temporaryID, referer, cleareddate, trackingcode, language, dispatchID, currency, currencyFactor, subshopID, remote_addr, deviceType) VALUES
(199, '0', 1, 1700.93, 1429.36, 75, 63.03, '2012-08-30 15:16:55', 1, 0, 7, 'foo', '', '', '', 0, 0, '', '9a0271fe91e7fc853a4a7a1e7ca789c812257d74', '', null, 'bar', '1', 10, 'EUR', 1, 1, '', null)
,(200, '0', 1, 1700.93, 1429.36, 75, 63.03, '2012-08-30 15:16:55', 1, 0, 7, 'foo', '', '', '', 0, 0, '', '9a0271fe91e7fc853a4a7a1e7ca789c812257d74', '', null, 'bar', '1', 10, 'EUR', 1, 2, '', null)
,(201, '0', 1, 1700.93, 1429.36, 75, 63.03, '2012-08-30 15:16:55', 1, 0, 7, 'foo', '', '', '', 0, 0, '', '9a0271fe91e7fc853a4a7a1e7ca789c812257d74', '', null, null, '1', 10, 'EUR', 1, 2, '', null)
,(202, '0', 1, 1700.93, 1429.36, 75, 63.03, '2012-08-30 15:16:55', 1, 0, 7, 'foo', '', '', '', 0, 0, '', '9a0271fe91e7fc853a4a7a1e7ca789c812257d74', '', null, '', '1', 10, 'EUR', 1, 2, '', null)
;

INSERT INTO s_order_attributes (id, orderID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6, swag_paypal_unified_payment_type, swag_paypal_unified_carrier_was_sent, swag_paypal_unified_carrier) VALUES
(199, 199, '', '', '', '', '', '', null, 0, 'DHL')
,(200, 200, '', '', '', '', '', '', null, 0, 'DHL')
,(201, 201, '', '', '', '', '', '', null, 0, 'DHL')
,(202, 202, '', '', '', '', '', '', null, 0, 'DHL')
;
