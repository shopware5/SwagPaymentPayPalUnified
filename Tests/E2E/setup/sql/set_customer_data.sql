UPDATE s_user
SET firstname                   = 'Peter',
    lastname                    = 'Lustig',
    birthday                    = '1990-02-12',
    default_billing_address_id  = 1,
    default_shipping_address_id = 1
WHERE email = 'test@example.com';

UPDATE s_user_addresses
SET firstname = 'Peter',
    lastname  = 'Lustig',
    street    = 'Ebbinghoff 10',
    zipcode   = '48624',
    city      = 'Schöppingen',
    phone     = '02555928850'
WHERE id = 1;
