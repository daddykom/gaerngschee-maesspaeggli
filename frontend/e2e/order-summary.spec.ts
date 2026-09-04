import { expect, test } from '@playwright/test';

test.describe('Order summary route', () => {
  test('shows the grouped order, supports back navigation and saves the order', async ({ page }) => {
    await page.route('http://localhost:8080/auth/registration-login', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          user: { id: 'client-1', email: 'client@example.com', group: 'client' },
          token: 'client-token',
          group: 'client',
          requiredPasswordReset: false,
          fairgateUserExists: true,
          childrenCount: 1,
          adultsCount: 2,
          salutation: 'Hallo',
        }),
      });
    });
    await page.route('http://localhost:8080/client/order', async (route) => {
      if (route.request().method() === 'GET') {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ order: null }) });
        return;
      }

      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          order: {
            id: 'order-1', userId: 'client-1', year: 2026, status: 'definitive',
            adultsCount: 2, childrenCount: 1,
            items: [
              { personType: 'adult', category: 'catA', quantity: 1 },
              { personType: 'adult', category: 'catB', quantity: 1 },
              { personType: 'child', category: 'catC', quantity: 1 },
            ],
            createdAt: null, updatedAt: null,
          },
        }),
      });
    });

    await page.goto('/client-login?token=registration-token');
    await page.waitForURL('**/order');

    const selects = page.getByRole('combobox');
    for (const [index, option] of ['Erwachsene ruhig', 'Erwachsene Action', 'Kinder 1-3 Jahre'].entries()) {
      await selects.nth(index).click();
      await page.getByRole('option', { name: option, exact: true }).click();
    }
    await page.getByRole('button', { name: 'Weiter' }).click();
    await page.waitForURL('**/order/summary');

    await expect(page.locator('h2', { hasText: 'Bestellübersicht' })).toBeVisible();
    await expect(page.getByText('1 x Erwachsene ruhig')).toBeVisible();
    await expect(page.getByText('1 x Erwachsene Action')).toBeVisible();
    await expect(page.getByText('1 x Kinder 1-3 Jahre')).toBeVisible();

    await page.getByRole('button', { name: 'Zurück' }).click();
    await page.waitForURL('**/order');
    await expect(selects.nth(0)).toHaveText('Erwachsene ruhig');

    await page.getByRole('button', { name: 'Weiter' }).click();
    await page.waitForURL('**/order/summary');
    await page.getByRole('button', { name: 'Bestellen' }).click();
    await expect(page.getByText('Bestellung gespeichert')).toBeVisible();
  });
});
