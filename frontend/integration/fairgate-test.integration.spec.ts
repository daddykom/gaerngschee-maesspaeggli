import { expect, test } from '@playwright/test';
import { loginAsAdmin } from './support/admin-login';

test.describe('Integration Fairgate test', () => {
  test('renders the configured Fairgate test response', async ({ page }) => {
    await page.route('http://localhost:8082/admin/fairgate/test', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          email: 'isabelle.joss@gaerngschee.ch',
          fairgate: { success: true, data: null },
        }),
      });
    });
    await loginAsAdmin(page);
    await page.goto('/admin/fairgate-test');
    await page.getByRole('button', { name: 'Fairgate-Abfrage starten' }).click();

    const result = page.locator('.fairgate-test-result');
    await expect(result).toContainText('isabelle.joss@gaerngschee.ch');
    await expect(result).toContainText('"success": true');
    await expect(result).toContainText('"data": null');
  });
});
