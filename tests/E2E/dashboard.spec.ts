import { test, expect } from '@playwright/test';

test.describe('Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
  });

  test('stats are displayed', async ({ page }) => {
    await expect(page.getByTestId('stats-grid')).toBeVisible();
    await expect(page.getByTestId('stat-totalBooks')).toBeVisible();
  });

  test('search input is visible', async ({ page }) => {
    await expect(page.getByTestId('search-input')).toBeVisible();
  });

  test('new book button is visible', async ({ page }) => {
    await expect(page.getByTestId('new-book-button')).toBeVisible();
  });

  test('books table is visible', async ({ page }) => {
    await expect(page.getByTestId('books-table')).toBeVisible();
  });
});
