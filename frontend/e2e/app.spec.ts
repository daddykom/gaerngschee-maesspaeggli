import { expect, test } from '@playwright/test';

test.describe('Gaerngschee App', () => {
  test('should load the anmeldung page', async ({ page }) => {
    await page.goto('/');
    await page.waitForURL('**/anmeldung');
    await expect(page.locator('app-root')).toBeVisible();
  });

  test('should display the email form', async ({ page }) => {
    await page.goto('/anmeldung');
    await expect(page.locator('input[type="email"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });
});
