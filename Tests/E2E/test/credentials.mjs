import 'dotenv/config';

export default {
    defaultShopCustomerEmail: 'test@example.com',
    defaultShopCustomerPassword: 'shopware',
    defaultBackendUserUsername: 'demo',
    defaultBackendUserPassword: 'demo',
    paypalCustomerEmail: process.env.PAYPAL_CUSTOMER_EMAIL,
    paypalCustomerPassword: process.env.PAYPAL_CUSTOMER_PASSWORD,
    paypalSandboxClientId: process.env.PAYPAL_SANDBOX_CLIENT_ID,
    paypalSandboxClientSecret: process.env.PAYPAL_SANDBOX_CLIENT_SECRET,
    paypalSandboxMerchantId: process.env.PAYPAL_SANDBOX_MERCHANT_ID,
    paypalCreditCard: process.env.PAYPAL_CREDIT_CARD
};
