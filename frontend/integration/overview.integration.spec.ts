import { expect, test } from '@playwright/test';
import { loginAsAdmin } from './support/admin-login';

test.describe('Integration admin overview', () => {
  test('loads the three order summaries with the configured period', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/overview');

    await expect(page.getByRole('columnheader', { name: 'Definitiv' })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: 'Provisorisch letzte 14 Tage' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Bestellungen ausliefern' })).toBeVisible();
    await expect(page.locator('.overview-table tbody tr')).toHaveCount(5);
  });
});
