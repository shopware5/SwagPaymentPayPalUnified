import credentials from "../setup/credentials.mjs";
import mysql from "mysql";

export default (function () {
    let instance;

    return {
        getInstance: function () {
            if(instance == null) {
                instance = mysql.createConnection({
                    host: credentials.mysqlHost,
                    port: credentials.mysqlPort,
                    user: credentials.mysqlUser,
                    password: credentials.mysqlPassword,
                    database: credentials.mysqlDatabase,
                    multipleStatements: true
                })
            }

            return instance;
        }
    }
}());
