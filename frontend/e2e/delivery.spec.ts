import { expect, test } from './support/test';
import { expectTranslatedText } from './support/assertions';

test.describe('Delivery route', () => {
  test('loads an order by email and confirms delivery', async ({ page }) => {
    await page.route('http://localhost:8080/auth/session-status', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ expiresAt: '2099-01-01T00:00:00Z', secondsRemaining: 3600 }) });
    });
    await page.route('http://localhost:8080/auth/login', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          user: { id: 'user-1', email: 'staff@example.com', group: 'user' },
          group: 'user',
          requiredPasswordReset: false,
        }),
      });
    });
    await page.route('http://localhost:8080/delivery/order**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          viaToken: false,
          order: {
            id: 'order-1', userId: 'client-1', year: 2026, status: 'qrcode',
            adultsCount: 2, childrenCount: 1,
            items: [{ personType: 'adult', category: 'catA', quantity: 2 }, { personType: 'child', category: 'catC', quantity: 1 }],
            createdAt: null, updatedAt: null,
          },
          clientName: 'Jane Doe',
          children: [{ name: 'Baby Doe', birthDate: null }],
        }),
      });
    });
    await page.route('http://localhost:8080/delivery/orders/order-1/deliver', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ status: 'delivered' }) });
    });

    await page.goto('/login');
    await page.locator('input[type="email"]').fill('staff@example.com');
    await page.locator('input[type="password"]').fill('secret');
    await page.getByRole('button', { name: 'Anmelden' }).click();
    await page.waitForURL('**/delivery');

    await page.locator('input[type="email"]').fill('client@example.com');
    await page.getByRole('button', { name: 'Bestellung suchen' }).click();
    await expect(page.getByText('Erwachsene: 2')).toBeVisible();
    await expect(page.getByText('Kinder: 1')).toBeVisible();
    await expect(page.getByText('Bezüger/in: Jane Doe')).toBeVisible();
    await expect(page.getByText('Baby Doe')).toBeVisible();
    await page.getByRole('button', { name: 'Ausliefern' }).click();
     await expectTranslatedText(page.getByRole('dialog'), 'app.delivery.identityQuestion');
    await page.getByRole('button', { name: 'Ausliefern' }).last().click();
  });
});
