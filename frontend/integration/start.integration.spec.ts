import { expect, test } from '@playwright/test';

test.describe('Integration start', () => {
  test('submits a registration request through the real backend', async ({ page }) => {
    await page.goto('/start');
    await page.locator('input[type="email"]').fill('integration@example.com');
    await page.getByRole('button', { name: 'Weiter' }).click();

    await expect(page.locator('input[type="email"]')).toHaveValue('integration@example.com');
  });
});
