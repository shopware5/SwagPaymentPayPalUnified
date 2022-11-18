import http from 'http';

export default (function () {
    return {
        clearCache: async function () {
            return new Promise((resolve, reject) => {
                const options = {
                    hostname: process.env.SW_HOST,
                    port: 80,
                    path: '/api/caches',
                    method: 'DELETE',
                    auth: 'demo:demo',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };

                const req = http.request(options);

                req.on('error', error => {
                    console.error(error);
                    reject(error);
                });

                req.on('response', res => {
                    resolve(res);
                });

                req.end();
            });
        }
    };
}());
