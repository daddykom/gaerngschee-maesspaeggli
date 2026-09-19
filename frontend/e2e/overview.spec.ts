import { expect, test } from '@playwright/test';

test.describe('Admin overview route', () => {
  test('shows the administration menu on a direct admin route', async ({ page }) => {
    await page.route('http://localhost:8080/auth/login', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          user: { id: '1', email: 'admin@example.com', group: 'admin' },
          group: 'admin',
          requiredPasswordReset: false,
        }),
      });
    });
    await page.route('http://localhost:8080/admin/overview', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          year: 2026,
          recentDays: 14,
          orders: { provisional: 1, recentProvisional: 1, definitive: 1, toDeliver: 0, qrcode: 0, delivered: 0 },
          categories: [{ category: 'catA', provisional: 1, recentProvisional: 1, definitive: 3, toDeliver: 0, qrcode: 0, delivered: 0 }],
        }),
      });
    });
    await page.route('http://localhost:8080/public/configuration', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify([
          { variableName: 'campaign_start_date', value: '2000-01-01' },
          { variableName: 'campaign_end_date', value: '2999-12-31' },
        ]),
      });
    });

    await page.goto('/login');
    await page.locator('input[type="email"]').fill('admin@example.com');
    await page.locator('input[type="password"]').fill('secret');
    await page.locator('button[type="submit"]').click();
    await page.waitForURL('**/admin/overview');

    await expect(page.locator('h1')).toHaveText('Admin-Übersicht');
    await expect(page.getByRole('button', { name: 'Bestellungen ausliefern' })).toBeDisabled();
    await expect(page.getByText('Die Auslieferung ist erst nach dem Ende der Anmeldefrist im Verarbeitungsjahr möglich.')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Legende' })).toBeVisible();
    await expect(page.getByText('Provisorische Bestellungen, noch nicht bei Fairgate vorhanden')).toBeVisible();
    await expect(page.getByRole('columnheader', { name: 'Definitiv' })).toBeVisible();
    await expect(page.getByRole('columnheader', { name: 'Provisorisch letzte 14 Tage' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Administrationsmenü öffnen' })).toBeVisible();
    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await expect(page.getByRole('menuitem', { name: 'Benutzerverwaltung' })).toBeVisible();
  });

  test('sends a normal user to delivery', async ({ page }) => {
    await page.route('http://localhost:8080/auth/login', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          user: { id: 'user-1', email: 'user@example.com', group: 'user' },
          group: 'user',
          requiredPasswordReset: false,
        }),
      });
    });
    await page.route('http://localhost:8080/delivery/order', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ order: null, viaToken: false }),
      });
    });

    await page.goto('/login');
    await page.locator('input[type="email"]').fill('user@example.com');
    await page.locator('input[type="password"]').fill('secret');
    await page.locator('button[type="submit"]').click();
    await page.waitForURL('**/delivery');
    await expect(page.locator('h1')).toHaveText('Auslieferung');
  });
});
