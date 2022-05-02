import fs from 'fs';
import path from 'path';

export default (function () {
    return {
        setProductNumberWithLeadingZero: function() {
            return fs.readFileSync(path.join(path.resolve(''), 'setup/sql/update_product_number_with_leading_zero.sql'), 'utf8');
        },

        reset: function () {
            return fs.readFileSync(path.join(path.resolve(''), 'setup/sql/reset_product_number_with_leading_zero.sql'), 'utf8');
        }
    };
}());
