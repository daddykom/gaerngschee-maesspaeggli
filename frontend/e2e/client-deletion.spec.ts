import { expect, test } from './support/test';
import { expectTranslatedText } from './support/assertions';

test.describe('Client deletion route', () => {
  test('opens from the administration menu and shows a client order', async ({ page }) => {
    await loginAsAdmin(page);
    await page.route('**/admin/client-deletion?email=*', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          client: { id: 'client-1', email: 'client@example.com' },
          year: 2026,
          canDelete: true,
          order: {
            id: 'order-1', userId: 'client-1', year: 2026, status: 'provisional',
            adultsCount: 1, childrenCount: 0, items: [], createdAt: null, updatedAt: null,
          },
        }),
      });
    });

    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await page.getByRole('menuitem', { name: 'Client-Bestellung löschen' }).click();
    await page.waitForURL('**/admin/client-deletion');
    await expectTranslatedText(page.locator('h1'), 'app.admin.users.clientDeletion.title');

    await page.locator('input[type="email"]').fill('client@example.com');
    await page.locator('button[type="submit"]').click();
    await expect(page.getByText('client@example.com')).toBeVisible();
    await expectTranslatedText(page.locator('main'), 'app.admin.users.clientDeletion.deleteButton');
  });
});

async function loginAsAdmin(page: import('@playwright/test').Page): Promise<void> {
  await page.route('http://localhost:8080/auth/session-status', async (route) => {
    await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ expiresAt: '2099-01-01T00:00:00Z', secondsRemaining: 3600 }) });
  });
  await page.route('http://localhost:8080/auth/login', async (route) => {
    await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({
      user: { id: '1', email: 'admin@example.com', group: 'admin' }, group: 'admin', requiredPasswordReset: false,
    }) });
  });
  await page.route('http://localhost:8080/admin/overview', async (route) => {
    await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ year: 2026, recentDays: 14, orders: {}, categories: [] }) });
  });
  await page.route('http://localhost:8080/public/configuration', async (route) => {
    await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify([]) });
  });
  await page.goto('/login');
  await page.locator('input[type="email"]').fill('admin@example.com');
  await page.locator('input[type="password"]').fill('secret');
  await page.locator('button[type="submit"]').click();
  await page.waitForURL('**/admin/overview');
}
