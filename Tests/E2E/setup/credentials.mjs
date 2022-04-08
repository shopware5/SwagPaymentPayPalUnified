import 'dotenv/config';

export default {
    mysqlHost: process.env.DB_HOST,
    mysqlUser: process.env.DB_USER,
    mysqlPassword: process.env.DB_PASSWORD,
    mysqlPort: process.env.DB_PORT,
    mysqlDatabase: process.env.DB_NAME
};
