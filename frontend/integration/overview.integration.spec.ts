import { expect, test } from '@playwright/test';
import { loginAsAdmin } from './support/admin-login';

test.describe('Integration admin overview', () => {
  test('loads the three order summaries with the configured period', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/overview');

    await expect(page.getByRole('heading', { name: 'Definitive Bestellungen' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Provisorische Bestellungen' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Provisorische Bestellungen in den letzten 14 Tagen' })).toBeVisible();
    await expect(page.locator('.overview-list__row')).toHaveCount(24);
  });
});
