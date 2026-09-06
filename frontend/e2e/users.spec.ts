import { expect, Page, test } from '@playwright/test';

test.describe('User administration route', () => {
  test('loads the users and shows the create action', async ({ page }) => {
    await loginAsAdmin(page);
    await page.route('http://localhost:8080/admin/users', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify([
          { id: '1', email: 'admin@example.com', group: 'admin', required_password_reset: false },
          { id: '2', email: 'user@example.com', group: 'user', required_password_reset: true },
        ]),
      });
    });

    await openUserManagement(page);
    await expect(page.getByText('admin@example.com')).toBeVisible();
    await expect(page.getByText('user@example.com')).toBeVisible();
    await expect(page.getByText('Administrator')).toBeVisible();
    await expect(page.getByText('Passwort muss neu gesetzt werden')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Benutzer erstellen' })).toBeVisible();
  });

  test('cancelling deletion does not call the backend', async ({ page }) => {
    await loginAsAdmin(page);
    await page.route('http://localhost:8080/admin/users', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify([
        { id: '2', email: 'user@example.com', group: 'user', required_password_reset: false },
      ]) });
    });
    let deleteCalled = false;
    await page.route('**/admin/users/2', async (route) => {
      deleteCalled = true;
      await route.fulfill({ status: 200, body: JSON.stringify({ deleted: true, userId: '2' }) });
    });

    await openUserManagement(page);
    await page.getByRole('button', { name: 'Löschen' }).click();
    await page.getByRole('button', { name: 'Abbrechen' }).click();

    expect(deleteCalled).toBe(false);
    await expect(page.getByText('user@example.com')).toBeVisible();
  });

  test('confirms deletion and shows the global success notification', async ({ page }) => {
    await loginAsAdmin(page);
    await page.route('http://localhost:8080/admin/users', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify([
        { id: '2', email: 'user@example.com', group: 'user', required_password_reset: false },
      ]) });
    });
    await page.route('**/admin/users/2', async (route) => {
      await route.fulfill({ status: 200, body: JSON.stringify({ deleted: true, userId: '2' }) });
    });

    await openUserManagement(page);
    await page.getByRole('button', { name: 'Löschen' }).click();
    await page.getByRole('dialog').getByRole('button', { name: 'Löschen' }).click();

    await expect(page.getByText('Der Benutzer wurde gelöscht.')).toBeVisible();
    await expect(page.getByText('user@example.com')).not.toBeVisible();
  });

});

async function loginAsAdmin(page: Page): Promise<void> {
  await login(page, 'admin@example.com', 'admin', '1');
}

async function login(page: Page, email: string, group: 'admin' | 'user', id: string): Promise<void> {
  await page.route('http://localhost:8080/auth/login', async (route) => {
    await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({
      user: { id, email, group }, token: 'test-token', group, requiredPasswordReset: false,
    }) });
  });
  await page.goto('/login');
  await page.locator('input[type="email"]').fill(email);
  await page.locator('input[type="password"]').fill('secret');
  await page.getByRole('button', { name: 'Anmelden' }).click();
  await page.waitForURL('**/admin/overview');
}

async function openUserManagement(page: Page): Promise<void> {
  await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
  await page.getByRole('menuitem', { name: 'Benutzerverwaltung' }).click();
  await page.waitForURL('**/admin/users');
}
