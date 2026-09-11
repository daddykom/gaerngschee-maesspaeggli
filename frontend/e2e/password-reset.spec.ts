import { expect, test } from '@playwright/test';

test.describe('Password reset route', () => {
  test('sets a new password with the reset token', async ({ page }) => {
    let requestBody: unknown;
    await page.route('http://localhost:8080/auth/password-reset', async (route) => {
      requestBody = route.request().postDataJSON();
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ user: { id: '1', email: 'user@example.com', group: 'user' } }),
      });
    });

    await page.goto('/password-reset?token=reset-token');
    await page.locator('input[autocomplete="username"]').fill('user@example.com');
    await page.locator('input[autocomplete="new-password"]').nth(0).fill('new-secret');
    await page.locator('input[autocomplete="new-password"]').nth(1).fill('new-secret');
    await page.getByRole('button', { name: 'Passwort setzen' }).click();

    await page.waitForURL('**/login');
    expect(requestBody).toEqual({ token: 'reset-token', password: 'new-secret' });
  });
});
