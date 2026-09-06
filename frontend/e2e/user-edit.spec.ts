import { expect, Page, test } from '@playwright/test';

test.describe('User edit route', () => {
  test('admin loads and updates a user including the reset flag', async ({ page }) => {
    await loginAsAdmin(page);
    await page.route('http://localhost:8080/admin/users', async (route) => {
      if (route.request().method() === 'GET') {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify([
          { id: '2', email: 'user@example.com', group: 'user', required_password_reset: false },
        ]) });
      }
    });
    await page.route('http://localhost:8080/admin/users/2', async (route) => {
      if (route.request().method() === 'GET') {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ user: {
          id: '2', email: 'user@example.com', group: 'user', required_password_reset: false,
        } }) });
        return;
      }
      expect(route.request().method()).toBe('PATCH');
      expect(route.request().postDataJSON()).toEqual({
        email: 'changed@example.com', group: 'admin', required_password_reset: true,
      });
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({
        user: { id: '2', email: 'changed@example.com', group: 'admin', required_password_reset: true },
        emailSentTo: 'changed@example.com',
      }) });
    });

    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await page.getByRole('menuitem', { name: 'Benutzerverwaltung' }).click();
    await page.waitForURL('**/admin/users');
    await page.getByRole('link', { name: 'Bearbeiten' }).click();
    await page.waitForURL('**/admin/users/2');
    await expect(page.locator('input[type="email"]')).toHaveValue('user@example.com');
    await page.locator('input[type="email"]').fill('changed@example.com');
    await page.getByRole('combobox').click();
    await page.getByRole('option', { name: 'Administrator' }).click();
    await page.getByRole('checkbox').check();
    await page.getByRole('button', { name: 'Speichern' }).click();

    await page.waitForURL('**/admin/users');
    await expect(page.getByText('Der Benutzer wurde geändert.')).toBeVisible();
  });

  test('normal user sees only the own email field', async ({ page }) => {
    await loginAsUser(page);
    await page.route('http://localhost:8080/admin/users/2', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ user: {
        id: '2', email: 'user@example.com', group: 'user', required_password_reset: false,
      } }) });
    });

    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await page.getByRole('menuitem', { name: 'Meine Daten' }).click();
    await page.waitForURL('**/admin/users/2');
    await expect(page.locator('input[type="email"]')).toBeVisible();
    await expect(page.getByRole('combobox')).not.toBeVisible();
    await expect(page.getByRole('checkbox')).not.toBeVisible();
  });

  test('cancel navigates back without saving', async ({ page }) => {
    await loginAsAdmin(page);
    await page.route('http://localhost:8080/admin/users', async (route) => {
      if (route.request().method() === 'GET') {
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify([
          { id: '2', email: 'user@example.com', group: 'user', required_password_reset: false },
        ]) });
      }
    });
    await page.route('http://localhost:8080/admin/users/2', async (route) => {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ user: {
        id: '2', email: 'user@example.com', group: 'user', required_password_reset: false,
      } }) });
    });
    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await page.getByRole('menuitem', { name: 'Benutzerverwaltung' }).click();
    await page.waitForURL('**/admin/users');
    await page.getByRole('link', { name: 'Bearbeiten' }).click();
    await page.getByRole('button', { name: 'Abbrechen' }).click();
    await page.waitForURL('**/admin/users');
  });
});

async function loginAsAdmin(page: Page): Promise<void> {
  await login(page, 'admin@example.com', 'admin', '1');
}

async function loginAsUser(page: Page): Promise<void> {
  await login(page, 'user@example.com', 'user', '2');
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
  await page.waitForURL(group === 'user' ? '**/delivery' : '**/admin/overview');
}
