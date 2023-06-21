import { exec } from 'child_process';

export default (function() {
    return {
        clearCache: async function() {
            return new Promise((resolve, reject) => {
                exec('php ../../../../../bin/console sw:cache:clear', (error, stdout, stderr) => {
                    if (error) {
                        reject(new Error(`Clear cache error: ${error.message}`));
                        return;
                    }
                    if (stderr) {
                        reject(new Error(`Clear cache stderr: ${stderr}`));
                        return;
                    }

                    resolve();
                });
            });
        },

        makeCurl: async function() {
            return new Promise((resolve, reject) => {
                exec('curl localhost', (error, stdout, stderr) => {
                    // TODO: REMOVE AFTER DEBUG
                    console.log('OUT: ' + stdout);
                    // console.log('ERROR: ' + error);
                    // console.log('STD_ERROR: ' + stderr);
                    // TODO: REMOVE AFTER DEBUG

                    resolve();
                });
            });
        }
    };
}());
