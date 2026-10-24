import { test, expect } from '@playwright/test';

test.describe('Create, Edit, Delete', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
  });

  test('create a new book via modal', async ({ page }) => {
    await page.getByTestId('new-book-button').click();
    await expect(page.getByTestId('modal-overlay')).toBeVisible();

    await page.getByTestId('modal-title-input').fill('Playwright Test Book');
    await page.getByTestId('modal-author-input').fill('E2E Author');
    await page.getByTestId('modal-save').click();

    await expect(page.getByTestId('modal-overlay')).not.toBeVisible();
  });

  test('delete a book via confirmation modal', async ({ page }) => {
    const book = await page.evaluate(async () => {
      const res = await fetch('/api/books', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: 'Delete Test', author: 'Author', description: 'Test' })
      });
      return res.json();
    });

    await page.reload();
    await page.waitForLoadState('networkidle');

    await page.locator(`tr:has-text("${book.title}")`).getByTestId('delete-button').click();
    await expect(page.getByTestId('delete-modal-overlay')).toBeVisible();

    await page.getByTestId('delete-confirm').click();
    await expect(page.getByTestId('delete-modal-overlay')).not.toBeVisible();
  });
});
