import { expect, test } from '@playwright/test';

test.describe('Admin overview route', () => {
  test('shows the administration menu on a direct admin route', async ({ page }) => {
    await page.goto('/admin/overview');

    await expect(page.locator('h1')).toHaveText('Admin-Übersicht');
    await expect(page.getByRole('button', { name: 'Administrationsmenü öffnen' })).toBeVisible();
  });
});
