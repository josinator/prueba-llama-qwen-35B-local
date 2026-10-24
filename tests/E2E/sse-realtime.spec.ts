import { test, expect } from '@playwright/test';

test.describe('SSE Real-time Updates', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
  });

  test('SSE shows connected status', async ({ page }) => {
    await expect(page.getByTestId('sse-status')).toBeVisible();
  });

  test('creates book via API and sees real-time update', async ({ page }) => {
    const responsePromise = page.waitForResponse(
      r => r.url().includes('/dashboard/api/search') && r.status() === 200
    );

    await page.evaluate(async () => {
      await fetch('/api/books', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: 'SSE Test', author: 'Author', description: 'Test' })
      });
    });

    await responsePromise;
    await expect(page.getByTestId('books-tbody')).toContainText('SSE Test');
  });
});
