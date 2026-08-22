import { expect, Page, test } from '@playwright/test';

test.describe('Logout flow', () => {
  test('navigates to login after a successful backend logout', async ({ page }) => {
    await loginAsAdmin(page);
    let logoutCalled = false;
    await page.route('http://localhost:8080/auth/logout', async (route) => {
      logoutCalled = true;
      await route.fulfill({ status: 204 });
    });

    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await page.getByRole('menuitem', { name: 'Abmelden' }).click();

    await page.waitForURL('**/login');
    expect(logoutCalled).toBe(true);
  });

  test('navigates to login when the backend logout fails', async ({ page }) => {
    await loginAsAdmin(page);
    let logoutCalled = false;
    await page.route('http://localhost:8080/auth/logout', async (route) => {
      logoutCalled = true;
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({
          error: { code: 'LOGOUT_FAILED', details: {} },
        }),
      });
    });

    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await page.getByRole('menuitem', { name: 'Abmelden' }).click();

    await page.waitForURL('**/login');
    expect(logoutCalled).toBe(true);
  });
});

async function loginAsAdmin(page: Page): Promise<void> {
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
}
