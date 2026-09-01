import { expect, test } from '@playwright/test';

test.describe('Password change route', () => {
  test('validates required and matching passwords', async ({ page }) => {
    await page.goto('/password-change');
    await page.getByRole('button', { name: 'Passwort ändern' }).click();

    await expect(page.getByText('Bitte gib ein neues Passwort ein.')).toBeVisible();
    await expect(page.getByText('Bitte bestätige dein neues Passwort.')).toBeVisible();

    await page.locator('input[type="password"]').nth(0).fill('new-secret');
    await page.locator('input[type="password"]').nth(1).fill('different-secret');
    await page.getByRole('button', { name: 'Passwort ändern' }).click();

    await expect(page.getByText('Die Passwörter stimmen nicht überein.')).toBeVisible();
  });

  test('changes the password and navigates to the overview', async ({ page }) => {
    await loginAsUser(page);
    let requestReceived = false;
    await page.route('http://localhost:8080/auth/password-change-authenticated', async (route) => {
      requestReceived = true;
      expect(route.request().postDataJSON()).toEqual({ password: 'new-secret' });
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ user: { id: '1', email: 'user@example.com', group: 'user' } }),
      });
    });

    await page.locator('input[type="password"]').nth(0).fill('new-secret');
    await page.locator('input[type="password"]').nth(1).fill('new-secret');
    await page.getByRole('button', { name: 'Passwort ändern' }).click();

    await page.waitForURL('**/admin/overview');
    expect(requestReceived).toBe(true);
  });

  test('shows a backend error notification', async ({ page }) => {
    await loginAsUser(page);
    await page.route('http://localhost:8080/auth/password-change-authenticated', async (route) => {
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ error: { code: 'PASSWORD_CHANGE_FAILED', details: {} } }),
      });
    });

    await page.locator('input[type="password"]').nth(0).fill('new-secret');
    await page.locator('input[type="password"]').nth(1).fill('new-secret');
    await page.getByRole('button', { name: 'Passwort ändern' }).click();

    await expect(page.locator('.info-box')).toContainText(
      'Das Passwort konnte nicht geändert werden.',
    );
  });
});

async function loginAsUser(page: import('@playwright/test').Page): Promise<void> {
  await page.route('http://localhost:8080/auth/login', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        user: { id: '2', email: 'user@example.com', group: 'user' },
        token: 'test-token',
        group: 'user',
        requiredPasswordReset: true,
      }),
    });
  });
  await page.goto('/login');
  await page.locator('input[type="email"]').fill('user@example.com');
  await page.locator('input[type="password"]').fill('temporary-secret');
  await page.getByRole('button', { name: 'Anmelden' }).click();
  await page.waitForURL('**/password-change');
}
