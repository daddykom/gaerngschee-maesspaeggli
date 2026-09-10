import { expect, test } from '@playwright/test';

test.describe('Root route', () => {
  test('shows the public campaign landing page', async ({ page }) => {
    await page.route('http://localhost:8080/public/configuration', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify([
          { variableName: 'campaign_year', value: '2026' },
          { variableName: 'donation_url', value: 'https://donate.example/maesspaeggli' },
        ]),
      });
    });

    await page.goto('/');
    await expect(page.locator('app-root')).toBeVisible();
    await expect(page.locator('h1')).toHaveText('Mässpäggli');
    await expect(page.locator('.home__lead')).toContainText('Besuch der Messe 2026');
    await expect(page.getByRole('link', { name: 'Jetzt spenden' })).toHaveAttribute('href', 'https://donate.example/maesspaeggli');
    await expect(page.getByRole('link', { name: 'Mässpäggli anfragen' })).toHaveAttribute('href', '/start');
    await expect(page.getByRole('link', { name: 'Login' })).toHaveAttribute('href', '/login');
  });
});
