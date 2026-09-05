import { expect, test } from '@playwright/test';

test.describe('Order route', () => {
  test('shows the order introduction for a client', async ({ page }) => {
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

    await page.goto('/client-login?token=registration-token');
     await page.waitForURL('**/order/edit');

    await expect(page.locator('h1')).toHaveText('Mässpäggli bestellen');
    await expect(
      page.getByText('Für deine Bestellung sind 2 Erwachsene und 1 Kinder erfasst.'),
    ).toBeVisible();
    await expect(page.getByText('Hallo')).toBeVisible();
    await expect(page.getByRole('combobox')).toHaveCount(3);
  });

  test('allows manual person counts when Fairgate data is unavailable', async ({ page }) => {
    await page.route('http://localhost:8080/client/order', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ order: null }) });
    });
    await page.route('http://localhost:8080/public/configuration', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify([
          { variableName: 'fairgate_url', value: 'https://fairgate.example' },
        ]),
      });
    });

    await page.route('http://localhost:8080/auth/registration-login', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          user: { id: 'client-1', email: 'client@example.com', group: 'client' },
          token: 'client-token',
          group: 'client',
          requiredPasswordReset: false,
          fairgateUserExists: false,
          childrenCount: 0,
          adultsCount: 0,
          salutation: 'Guten Tag',
        }),
      });
    });

    await page.goto('/client-login?token=registration-token');
     await page.waitForURL('**/order/edit');

    const counts = page.locator('input[type="number"]');
    await counts.nth(0).fill('2');
    await counts.nth(1).fill('3');

    await expect(page.getByRole('combobox')).toHaveCount(5);
    await expect(page.getByText('Bitte auswählen', { exact: true })).toHaveCount(5);
    await page.getByRole('button', { name: 'Weiter' }).click();
    await expect(page.getByRole('alert')).toHaveCount(5);
     await expect(page).toHaveURL('/order/edit');
    await expect(page.getByText('Anzahl Personen erfassen')).toBeVisible();
    await expect(page.getByText('Lieber Besteller', { exact: true })).toBeVisible();
    await expect(page.getByText('Wir können Dich leider nicht bei Fairgate finden.')).toBeVisible();
    await expect(page.getByRole('link', { name: 'hier' })).toHaveAttribute('href', 'https://fairgate.example');
  });

  test('redirects unauthenticated users to the not-found page', async ({ page }) => {
    await page.goto('/order');
    await page.waitForURL('**/not-found');
    await expect(page.locator('h1')).toHaveText('Seite nicht gefunden');
  });
});
