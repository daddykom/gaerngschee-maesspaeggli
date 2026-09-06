import { expect, test } from '@playwright/test';

test.describe('Client login route', () => {
  test('exchanges the registration token and navigates to the order page', async ({ page }) => {
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
          childrenCount: 2,
          adultsCount: 2,
          salutation: 'Hallo',
        }),
      });
    });

    await page.goto('/client-login?token=registration-token');
     await page.waitForURL('**/order/edit');
    await expect(page.locator('h1')).toHaveText('Mässpäggli bestellen');
  });

  test('shows an error when the registration token is invalid', async ({ page }) => {
    await page.route('http://localhost:8080/auth/registration-login', async (route) => {
      await route.fulfill({
        status: 401,
        contentType: 'application/json',
        body: JSON.stringify({
          error: { code: 'INVALID_REGISTRATION_TOKEN', details: {} },
        }),
      });
    });

    await page.goto('/client-login?token=invalid-token');
    await expect(page).toHaveURL(/\/start$/);
    await expect(page.getByText('Fehler')).toBeVisible();
    await expect(page.getByText('Der Anmeldelink ist ungültig oder abgelaufen. Bitte fordere auf der Startseite einen neuen Link an.')).toBeVisible();
  });
});
