import { expect, test } from '@playwright/test';
import { loginAsAdmin } from './support/admin-login';

test.describe('Integration Fairgate test', () => {
  test('runs the configured Fairgate fake successfully', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/fairgate-test');
    await page.getByRole('button', { name: 'Fairgate-Abfrage starten' }).click();

    const result = page.locator('.fairgate-test-result');
    await expect(result).toContainText('isabelle.joss@gaerngschee.ch');
    await expect(result).toContainText('"success": true');
    await expect(result).toContainText('"data": null');
  });
});
