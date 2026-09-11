import { expect, test } from '@playwright/test';

test.describe('Password reset request route', () => {
  test('requests a password reset link without revealing account existence', async ({ page }) => {
    let requestBody: unknown;
    await page.route('http://localhost:8080/auth/password-reset-request', async (route) => {
      requestBody = route.request().postDataJSON();
      await route.fulfill({
        status: 202,
        contentType: 'application/json',
        body: JSON.stringify({ sent: true }),
      });
    });

    await page.goto('/password-reset-request');
    await expect(page.locator('input[autocomplete="username"]')).toBeVisible();
    await page.locator('input[autocomplete="username"]').fill('user@example.com');
    await page.getByRole('button', { name: 'Link anfordern' }).click();

    await expect(page.getByRole('status')).toContainText('Wenn ein Konto mit dieser E-Mail-Adresse existiert');
    expect(requestBody).toEqual({ email: 'user@example.com' });
  });
});
