import MysqlFactory from './mysqlFactory.mjs';

const connection = MysqlFactory.getInstance();

export default (function() {
    return {
        /**
         * @returns { Promise<void> }
         */
        activateOffCanvas: async function() {
            await this.__changeValue('b:1;');
        },

        /**
         * @returns { Promise<void> }
         */
        deactivateOffCanvasCart: async function() {
            await this.__changeValue('b:0;');
        },

        /**
         * @param { string } value
         *
         * @returns { Promise<void> }
         *
         * @private
         */
        __changeValue: async function(value) {
            const configElementId = await this.__getConfigElementId(),
                configValuesId = await this.__getConfigValueId(configElementId);

            if (configElementId) {
                await this.__updateConfigElementDefaultValue(configElementId, value);
            }

            if (configValuesId) {
                await this.__updateConfigValuesValue(configValuesId, value);
            }
        },

        /**
         * @param { number } configElementId
         * @param { string } value
         *
         * @returns { Promise<array> }
         *
         * @private
         */
        __updateConfigElementDefaultValue: async function(configElementId, value) {
            const updateElementDefaultValuePromise = function(configElementId, value) {
                return new Promise((resolve, reject) => {
                    const sql = 'UPDATE s_core_templates_config_elements SET default_value = "%s1" WHERE `id` = "%s2";'
                        .replace('%s1', value)
                        .replace('%s2', configElementId);

                    connection.query(sql, (err, result) => {
                        if (err) {
                            reject(err);
                        }

                        resolve(result);
                    });
                });
            };

            return updateElementDefaultValuePromise(configElementId, value);
        },

        /**
         * @param { number } configValuesId
         * @param { string } value
         *
         * @returns { Promise<array> }
         *
         * @private
         */
        __updateConfigValuesValue: async function(configValuesId, value) {
            const updateConfigValuesValuePromise = function(configValuesId, value) {
                return new Promise((resolve, reject) => {
                    const sql = 'UPDATE s_core_templates_config_values SET `value` = "%s1" WHERE `id` = "%s2";'
                        .replace('%s1', value)
                        .replace('%s2', configValuesId);

                    connection.query(sql, (err, result) => {
                        if (err) {
                            reject(err);
                        }

                        resolve(result);
                    });
                });
            };

            return updateConfigValuesValuePromise(configValuesId, value);
        },

        /**
         * @returns { Promise<number> }
         *
         * @private
         */
        __getConfigElementId: async function() {
            const selectIdPromise = function() {
                return new Promise((resolve, reject) => {
                    const sql = 'SELECT id FROM s_core_templates_config_elements WHERE `name` LIKE "offcanvasCart";';

                    connection.query(sql, (err, result) => {
                        if (err) {
                            reject(err);
                        }

                        resolve(result);
                    });
                });
            };

            const result = await selectIdPromise();

            if (result[0] && Object.prototype.hasOwnProperty.call(result[0], 'id')) {
                return result[0].id;
            }

            return 0;
        },

        /**
         * @param { number } configElementId
         *
         * @returns { Promise<number> }
         *
         * @private
         */
        __getConfigValueId: async function(configElementId) {
            const selectIdPromise = function(configElementId) {
                return new Promise((resolve, reject) => {
                    const sql = 'SELECT id FROM s_core_templates_config_values WHERE `element_id` = "%s";'.replace('%s', configElementId);

                    connection.query(sql, (err, result) => {
                        if (err) {
                            reject(err);
                        }

                        resolve(result);
                    });
                });
            };

            const result = await selectIdPromise(configElementId);

            if (result[0] && Object.prototype.hasOwnProperty.call(result[0], 'id')) {
                return result[0].id;
            }

            return 0;
        }
    };
}());
