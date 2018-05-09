-- Resets the plugin Ids of the legacy payment methods
-- If not doing that, the legacy mode would not work
-- anymore after completely deleting the plugin through the plugin manager.
UPDATE s_core_paymentmeans AS payment
SET payment.pluginID = NULL
WHERE payment.name='paypal'
OR payment.name='payment_paypal_installments';
