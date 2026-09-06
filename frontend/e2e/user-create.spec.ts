import { expect, Page, test } from '@playwright/test';

test.describe('User creation route', () => {
  test('validates the email and creates a user', async ({ page }) => {
    await loginAsAdmin(page);
    await page.route('http://localhost:8080/admin/users', async (route) => {
      if (route.request().method() === 'GET') {
        await route.fulfill({ status: 200, contentType: 'application/json', body: '[]' });
      } else {
        await route.continue();
      }
    });
    await page.getByRole('button', { name: 'Administrationsmenü öffnen' }).click();
    await page.getByRole('menuitem', { name: 'Benutzerverwaltung' }).click();
    await page.waitForURL('**/admin/users');
    await page.getByRole('link', { name: 'Benutzer erstellen' }).click();
    await page.waitForURL('**/admin/users/new');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Dieses Feld ist erforderlich.')).toBeVisible();

    await page.locator('input[type="email"]').fill('new@example.com');
    await page.getByRole('combobox').click();
    await page.getByRole('option', { name: 'Benutzer' }).click();
    await page.route('http://localhost:8080/admin/users', async (route) => {
      if (route.request().method() === 'POST') {
        expect(route.request().postDataJSON()).toEqual({ email: 'new@example.com', group: 'user' });
        await route.fulfill({ status: 201, contentType: 'application/json', body: JSON.stringify({
          user: { id: '3', email: 'new@example.com', group: 'user', required_password_reset: true },
          emailSentTo: 'new@example.com',
        }) });
      } else {
        await route.continue();
      }
    });
    await page.getByRole('button', { name: 'Speichern' }).click();

    await page.waitForURL('**/admin/users');
    await expect(page.getByText('Der Benutzer wurde erstellt.')).toBeVisible();
    await expect(page.getByText('Eine E-Mail wurde an new@example.com versendet.')).toBeVisible();
  });
});

async function loginAsAdmin(page: Page): Promise<void> {
  await page.route('http://localhost:8080/auth/login', async (route) => {
    await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({
      user: { id: '1', email: 'admin@example.com', group: 'admin' },
      token: 'test-token', group: 'admin', requiredPasswordReset: false,
    }) });
  });
  await page.goto('/login');
  await page.locator('input[type="email"]').fill('admin@example.com');
  await page.locator('input[type="password"]').fill('secret');
  await page.getByRole('button', { name: 'Anmelden' }).click();
  await page.waitForURL('**/admin/overview');
}
