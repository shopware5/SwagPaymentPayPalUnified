import MysqlFactory from './mysqlFactory.mjs';

const connection = MysqlFactory.getInstance();

export default (function() {
    return {
        activateAll: async function() {
            await this.activatePayLaterForPayPal();
            await this.activatePayLaterForExpress();
        },

        deactivateAll: async function() {
            await this.deactivatePayLaterForPayPal();
            await this.deactivatePayLaterForExpress();
        },

        activatePayLaterForPayPal: async function() {
            return new Promise((resolve, reject) => {
                const sql = 'UPDATE swag_payment_paypal_unified_settings_installments SET show_pay_later_paypal = 1 WHERE true';

                connection.query(sql, (err, result) => {
                    if (err) {
                        reject(err);
                    }

                    resolve(result);
                });
            });
        },

        activatePayLaterForExpress: async function() {
            return new Promise((resolve, reject) => {
                const sql = 'UPDATE swag_payment_paypal_unified_settings_installments SET show_pay_later_express = 1 WHERE true';

                connection.query(sql, (err, result) => {
                    if (err) {
                        reject(err);
                    }

                    resolve(result);
                });
            });
        },

        deactivatePayLaterForPayPal: async function() {
            return new Promise((resolve, reject) => {
                const sql = 'UPDATE swag_payment_paypal_unified_settings_installments SET show_pay_later_paypal = 0 WHERE true';

                connection.query(sql, (err, result) => {
                    if (err) {
                        reject(err);
                    }

                    resolve(result);
                });
            });
        },

        deactivatePayLaterForExpress: async function() {
            return new Promise((resolve, reject) => {
                const sql = 'UPDATE swag_payment_paypal_unified_settings_installments SET show_pay_later_express = 0 WHERE true';

                connection.query(sql, (err, result) => {
                    if (err) {
                        reject(err);
                    }

                    resolve(result);
                });
            });
        }
    };
})();
