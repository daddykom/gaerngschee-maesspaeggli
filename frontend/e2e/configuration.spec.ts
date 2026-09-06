import { expect, test } from '@playwright/test';

test.describe('Configuration route', () => {
  test('loads and saves editable configuration values', async ({ page }) => {
    await page.addInitScript(() => {
      const encode = (value: object) => btoa(JSON.stringify(value))
        .replace(/=/g, '')
        .replace(/\+/g, '-')
        .replace(/\//g, '_');
      localStorage.setItem('gaerngschee.auth', JSON.stringify({
        token: `${encode({ alg: 'none' })}.${encode({ exp: Math.floor(Date.now() / 1000) + 3600 })}.signature`,
        userId: 'admin-1',
        group: 'admin',
        fairgateUserExists: null,
        childrenCount: null,
        adultsCount: null,
        salutation: null,
      }));
    });
    await page.route('http://localhost:8080/admin/configuration', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify([{
          id: 'config-1',
          variableName: 'SITE_TITLE',
          value: 'Mässpäggli',
          description: 'Titel',
          accessGroup: ['admin'],
          updateGroup: ['admin'],
          label: 'Seitentitel',
          canUpdate: true,
        }]),
      });
    });
    await page.route('http://localhost:8080/admin/configuration/config-1', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ id: 'config-1', variableName: 'SITE_TITLE', value: 'Neuer Titel' }),
      });
    });

    await page.goto('/admin/configuration');

    await expect(page.getByText('Seitentitel')).toBeVisible();
    await page.locator('input').fill('Neuer Titel');
    await page.getByRole('button', { name: 'Konfiguration speichern' }).click();
    await expect(page.getByText('Die Konfiguration wurde gespeichert.')).toBeVisible();
  });
});
