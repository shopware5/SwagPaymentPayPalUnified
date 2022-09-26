const { devices } = require('@playwright/test');
require('dotenv').config();

/**
 * @see https://playwright.dev/docs/test-configuration
 * @type {import('@playwright/test').PlaywrightTestConfig}
 */
const config = {
    testDir: './.',
    globalTeardown: './setup/globalTeardown.mjs',
    globalSetup: './setup/globalSetup.mjs',
    retries: 0,
    timeout: 300000,
    expect: {
        timeout: 300000
    },
    forbidOnly: !!process.env.CI,
    workers: 1,
    reporter: 'line',
    use: {
        browserName: 'chromium',
        actionTimeout: 30000,
        baseURL: 'http://' + process.env.SW_HOST + process.env.SW_BASE_PATH ?? '',
        trace: 'on',
        video: 'retain-on-failure',
        launchOptions: {
            slowMo: 300
        }
    },
    projects: [
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome']
            }
        }
    ],
    outputDir: './results/test-results',
    snapshotDir: './results/snapshotDir'
};

// Only required for non-debian systems
if (process.env.BROWSER_EXECUTABLE_PATH) {
    config.use.launchOptions = {
        executablePath: process.env.BROWSER_EXECUTABLE_PATH
    };
}

module.exports = config;
