import { expect, test } from '@playwright/test';

test.describe('Start route', () => {
  test('redirects the root route to start', async ({ page }) => {
    await page.goto('/');
    await page.waitForURL('**/start');
    await expect(page.locator('app-root')).toBeVisible();
  });

  test('displays the email form', async ({ page }) => {
    await page.goto('/start');
    await expect(page.locator('input[type="email"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });
});
