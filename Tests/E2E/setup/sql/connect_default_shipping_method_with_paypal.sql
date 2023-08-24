SET @shippingMethodId = (SELECT id FROM `s_premium_dispatch` WHERE name='Standard Versand');
SET @paymentMethodId = (SELECT id FROM `s_core_paymentmeans` WHERE name='SwagPaymentPayPalUnified');

INSERT INTO `s_premium_dispatch_paymentmeans`(`dispatchID`, `paymentID`) VALUES (@shippingMethodId, @paymentMethodId)
ON DUPLICATE KEY UPDATE `dispatchID` = `dispatchID`, `paymentID` = `paymentID`;
