import { expect, test } from '@playwright/test';

test.describe('Gaerngschee App', () => {
  test('should load the app and show offers page', async ({ page }) => {
    await page.goto('/');
    await page.waitForURL('**/offers/list');
    await expect(page.locator('app-root')).toBeVisible();
  });

  test('should display offer list or loading state', async ({ page }) => {
    await page.goto('/offers');
    const hasContent = await page
      .locator('mat-card, mat-spinner')
      .first()
      .isVisible({ timeout: 5000 })
      .catch(() => false);
    expect(hasContent).toBeTruthy();
  });
});
