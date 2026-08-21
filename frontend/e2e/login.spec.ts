import { expect, test } from '@playwright/test';

test.describe('Login route', () => {
  test('logs in and navigates to the admin overview', async ({ page }) => {
    await page.route('http://localhost:8080/auth/login', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          user: { id: '1', email: 'admin@example.com', group: 'admin' },
          token: 'test-token',
          group: 'admin',
        }),
      });
    });

    await page.goto('/login');
    await page.locator('input[type="email"]').fill('admin@example.com');
    await page.locator('input[type="password"]').fill('secret');
    await page.locator('button[type="submit"]').click();

    await page.waitForURL('**/admin/overview');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('shows a translated error for invalid credentials', async ({ page }) => {
    await page.route('http://localhost:8080/auth/login', async (route) => {
      await route.fulfill({
        status: 401,
        contentType: 'application/json',
        body: JSON.stringify({
          error: { code: 'INVALID_CREDENTIALS', details: {} },
        }),
      });
    });

    await page.goto('/login');
    await page.locator('input[type="email"]').fill('admin@example.com');
    await page.locator('input[type="password"]').fill('wrong-password');
    await page.locator('button[type="submit"]').click();

    await expect(page.locator('.info-box')).toContainText('Anmeldung fehlgeschlagen');
    await expect(page.locator('.info-box')).toContainText(
      'E-Mail-Adresse oder Passwort ist ungültig.',
    );
  });
});
