import 'dotenv/config'

export default {
    mysqlHost: process.env.MYSQL_HOST,
    mysqlUser: process.env.MYSQL_USER,
    mysqlPassword: process.env.MYSQL_PASSWORD,
    mysqlPort: process.env.MYSQL_Port,
    mysqlDatabase: process.env.MYSQL_DATABASE
};
