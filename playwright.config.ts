import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: 'tests/E2E',
  testMatch: '**/*.spec.ts',
  fullyParallel: true,
  retries: 1,
  workers: 1,
  use: {
    baseURL: 'http://localhost:8080',
    chromiumSandbox: false,
    trace: 'on-first-retry',
  },
  webServer: {
    command: 'docker compose up -d',
    url: 'http://localhost:8080',
    reuseExistingServer: true,
    timeout: 60000,
    stdout: 'pipe',
  },
});
