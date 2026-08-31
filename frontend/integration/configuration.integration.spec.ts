import { expect, test } from '@playwright/test';
import { loginAsAdmin } from './support/admin-login';

test.describe('Integration configuration', () => {
  test('loads, saves and reloads a configuration value', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/configuration');

    const input = page.locator('input').first();
    await expect(input).toHaveValue('isabelle.joss@gaerngschee.ch');
    await input.fill('integration.changed@example.com');
    await page.getByRole('button', { name: 'Konfiguration speichern' }).click();
    await expect(page.getByText('Die Konfiguration wurde gespeichert.')).toBeVisible();

    await page.reload();
    await expect(page.locator('input').first()).toHaveValue('integration.changed@example.com');

    await page.locator('input').first().fill('isabelle.joss@gaerngschee.ch');
    await page.getByRole('button', { name: 'Konfiguration speichern' }).click();
    await expect(page.getByText('Die Konfiguration wurde gespeichert.')).toBeVisible();
  });
});
