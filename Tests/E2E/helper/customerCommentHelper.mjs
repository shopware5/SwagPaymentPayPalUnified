import MysqlFactory from './mysqlFactory.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';

const connection = MysqlFactory.getInstance();

export default (function() {
    return {
        updateCommentSetting: async function() {
            await new Promise((resolve, reject) => {
                connection.query("UPDATE s_core_config_elements SET value = 'b:1;' WHERE `name` IN ('commentArticle', 'commentVoucherArticle')", function (err) {
                    if (err) {
                        reject(err);
                    }

                    resolve();
                });
            }).then(async () => {
                await clearCacheHelper.clearCache();
            });
        },

        getCustomerComment: async function() {
            var comment;

            await new Promise((resolve, reject) => {
                connection.query('SELECT `customercomment` FROM s_order ORDER BY id DESC LIMIT 0, 1', function(err, result) {
                    if (err) {
                        reject(err);
                    }

                    comment = result[0].customercomment;
                    resolve();
                });
            });

            return comment;
        }
    };
}());
