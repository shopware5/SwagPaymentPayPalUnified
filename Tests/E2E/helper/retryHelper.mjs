export default async function tryUntilSucceed(promiseFn, maxTries = 3) {
    try {
        return await promiseFn();
    } catch (e) {
        if (maxTries > 0) {
            return tryUntilSucceed(promiseFn, maxTries - 1);
        }
        throw e;
    }
}
