import { test, expect } from '@playwright/test';

test.describe('Search', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
  });

  test('search with debounce works', async ({ page }) => {
    const searchInput = page.getByTestId('search-input');
    await searchInput.fill('Test Book');
    await page.waitForResponse(
      r => r.url().includes('/dashboard/api/search') && r.status() === 200
    );
    await expect(page.getByTestId('search-button')).toBeVisible();
  });
});
