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
    retries: 5,
    timeout: 300000,
    expect: {
        timeout: 300000
    },
    forbidOnly: !!process.env.CI,
    workers: 1,
    reporter: 'line',
    use: {
        browserName: 'firefox',
        actionTimeout: 30000,
        baseURL: 'http://' + process.env.SW_HOST + process.env.SW_BASE_PATH ?? '',
        trace: 'on',
        video: 'retain-on-failure',
        bypassCSP: true,
        launchOptions: {
            slowMo: 300
        }
    },
    projects: [
        {
            name: 'firefox',
            use: {
                ...devices['Desktop Firefox'],
                viewport: { width: 1920, height: 1080 },
                locale: 'de-DE',
                timezoneId: 'Europe/Berlin',
                geolocation: { longitude: 52.515027, latitude: 13.392027 },
                permissions: ['geolocation']
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
