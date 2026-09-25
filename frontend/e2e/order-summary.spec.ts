import { expect, test } from './support/test';
import { expectTranslatedText } from './support/assertions';

test.describe('Order summary route', () => {
  test('shows the grouped order, supports back navigation and saves the order', async ({ page }) => {
    await page.route('http://localhost:8080/auth/session-status', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ expiresAt: '2099-01-01T00:00:00Z', secondsRemaining: 3600 }) });
    });
    await page.route('http://localhost:8080/public/configuration', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify([{ variableName: 'campaign_year', value: '2026' }]),
      });
    });
    await page.route('http://localhost:8080/auth/registration-login', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          user: { id: 'client-1', email: 'client@example.com', group: 'client' },
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
              { personType: 'child', category: 'catC', quantity: 1 },
            ],
            createdAt: null, updatedAt: null,
          },
        }),
      });
    });

    await page.goto('/client-login?token=registration-token');
     await page.waitForURL('**/order/edit');

    const selects = page.getByRole('combobox');
    for (const [index, option] of ['Kinder 1-3 Jahre'].entries()) {
      await selects.nth(index).click();
      await page.getByRole('option', { name: option, exact: true }).click();
    }
    await page.getByRole('button', { name: 'Weiter' }).click();
    await page.waitForURL('**/order/summary');

     await expectTranslatedText(page.locator('h1'), 'app.order.summary.pageTitle');
     await expectTranslatedText(page.locator('main'), 'app.order.summary.description');
     await expectTranslatedText(page.locator('main'), 'app.order.summary.statuses.definitive');
     await expectTranslatedText(page.locator('main'), 'app.order.categories.options.catC');
    await expect(page.getByRole('heading', { name: 'Erwachsene', exact: true })).toHaveCount(0);

    await page.getByRole('button', { name: 'Zurück' }).click();
     await page.waitForURL('**/order/edit');
     await expectTranslatedText(selects.nth(0), 'app.order.categories.options.catC');

    await page.getByRole('button', { name: 'Weiter' }).click();
    await page.waitForURL('**/order/summary');
    await page.getByRole('button', { name: 'Bestellen' }).click();
    await page.waitForURL('**/');
     await expectTranslatedText(page.getByRole('alert'), 'app.order.notifications.successTitle');
  });
});
