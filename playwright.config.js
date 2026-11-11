// @ts-check
const { defineConfig } = require('@playwright/test');

const urlDasarUji = process.env.BANKMINI_URL || 'http://127.0.0.1:8199';

module.exports = defineConfig({
  testDir: './pengujian/e2e',
  fullyParallel: false,
  workers: 1,
  retries: 0,
  timeout: 30000,
  expect: {
    timeout: 7000,
  },
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: urlDasarUji,
    channel: 'chrome',
    headless: true,
    viewport: { width: 1366, height: 800 },
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  webServer: {
    command: 'C:\\xampp\\php\\php.exe -S 127.0.0.1:8199 -t public',
    url: urlDasarUji + '/masuk',
    reuseExistingServer: !process.env.CI,
    timeout: 20000,
  },
});
