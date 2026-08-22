import { expect, test } from '@playwright/test';

test.describe('Logout flow', () => {
  test('navigates to login after a successful backend logout', async ({ page }) => {
    let logoutCalled = false;
    await page.route('http://localhost:8080/auth/logout', async (route) => {
      logoutCalled = true;
      await route.fulfill({ status: 204 });
    });

    await page.goto('/admin/overview');
    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await page.getByRole('menuitem', { name: 'Abmelden' }).click();

    await page.waitForURL('**/login');
    expect(logoutCalled).toBe(true);
  });

  test('navigates to login when the backend logout fails', async ({ page }) => {
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

    await page.goto('/admin/overview');
    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await page.getByRole('menuitem', { name: 'Abmelden' }).click();

    await page.waitForURL('**/login');
    expect(logoutCalled).toBe(true);
  });
});
