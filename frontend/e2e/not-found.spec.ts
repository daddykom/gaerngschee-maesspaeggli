import { expect, test } from '@playwright/test';

test.describe('Not-found route', () => {
  test('shows the not-found page for an unknown route', async ({ page }) => {
    await page.goto('/route-does-not-exist');

    await page.waitForURL('**/not-found');
    await expect(page.locator('h1')).toHaveText('Seite nicht gefunden');
    await expect(page.getByText('Diese Seite gibt es nicht')).toBeVisible();
  });

  test('links to the login page', async ({ page }) => {
    await page.goto('/not-found');
    await page.getByRole('link', { name: 'Anmelden' }).click();

    await page.waitForURL('**/login');
  });

  test('links to the start page', async ({ page }) => {
    await page.goto('/not-found');
    await page.getByRole('link', { name: 'Mässpäggli bestellen' }).click();

    await page.waitForURL('**/start');
  });

  test('shows the not-found page for a forbidden admin route', async ({ page }) => {
    await page.goto('/admin/overview');

    await page.waitForURL('**/not-found');
    await expect(page.locator('h1')).toHaveText('Seite nicht gefunden');
  });

  test('shows the not-found page for the unauthenticated user administration route', async ({ page }) => {
    await page.goto('/admin/users');
    await page.waitForURL('**/not-found');
    await expect(page.locator('h1')).toHaveText('Seite nicht gefunden');
  });
});
