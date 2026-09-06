import { expect, test } from '@playwright/test';

test.describe('Root route', () => {
  test('redirects to the start route', async ({ page }) => {
    await page.goto('/');
    await page.waitForURL('**/start');
    await expect(page.locator('app-root')).toBeVisible();
    await expect(page.locator('h1')).toHaveText('Mässpäggli bekommen');
  });
});
