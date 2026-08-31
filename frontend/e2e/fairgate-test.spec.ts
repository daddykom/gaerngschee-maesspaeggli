import { expect, test } from '@playwright/test';

test.describe('Fairgate test route', () => {
  test('rejects a normal user', async ({ page }) => {
    await page.route('http://localhost:8080/auth/login', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          user: { id: 'user-1', email: 'user@example.com', group: 'user' },
          token: 'user-token',
          group: 'user',
          requiredPasswordReset: false,
        }),
      });
    });

    await page.goto('/login');
    await page.locator('input[type="email"]').fill('user@example.com');
    await page.locator('input[type="password"]').fill('secret');
    await page.locator('button[type="submit"]').click();
    await page.waitForURL('**/admin/overview');
    await page.goto('/admin/fairgate-test');
    await page.waitForURL('**/not-found');
    await expect(page.locator('h1')).toHaveText('Seite nicht gefunden');
  });
});
