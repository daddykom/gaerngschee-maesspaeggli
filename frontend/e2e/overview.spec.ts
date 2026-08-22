import { expect, test } from '@playwright/test';

test.describe('Admin overview route', () => {
  test('shows the administration menu on a direct admin route', async ({ page }) => {
    await page.route('http://localhost:8080/auth/login', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          user: { id: '1', email: 'admin@example.com', group: 'admin' },
          token: 'test-token',
          group: 'admin',
          requiredPasswordReset: false,
        }),
      });
    });

    await page.goto('/login');
    await page.locator('input[type="email"]').fill('admin@example.com');
    await page.locator('input[type="password"]').fill('secret');
    await page.locator('button[type="submit"]').click();
    await page.waitForURL('**/admin/overview');

    await expect(page.locator('h1')).toHaveText('Admin-Übersicht');
    await expect(page.getByRole('button', { name: 'Administrationsmenü öffnen' })).toBeVisible();
    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await expect(page.getByRole('menuitem', { name: 'Benutzerverwaltung' })).toBeVisible();
  });
});
