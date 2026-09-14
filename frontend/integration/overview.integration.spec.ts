import { expect, test } from '@playwright/test';
import { loginAsAdmin } from './support/admin-login';

test.describe('Integration admin overview', () => {
  test('loads the three order summaries with the configured period and hides delivery during the campaign', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/overview');

    await expect(page.getByRole('columnheader', { name: 'Definitiv' })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: 'Provisorisch letzte 14 Tage' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Bestellungen ausliefern' })).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Legende' })).toBeVisible();
    await expect(page.getByText('Provisorische Bestellungen, noch nicht bei Fairgate vorhanden')).toBeVisible();
    await expect(page.locator('.overview-table tbody tr').first()).toContainText('Bestellungen');
  });
});
