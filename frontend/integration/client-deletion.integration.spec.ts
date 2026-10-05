import { expect, test } from '@playwright/test';
import { loginAsAdmin } from './support/admin-login';
import { authHeaders } from './support/auth-header';
import { issueRegistrationToken } from './support/registration-token';

test.describe('Integration client deletion', () => {
  test('searches and deletes a real provisional client order', async ({ page }) => {
    const email = `client-deletion-${Date.now()}@example.com`;
    const clientPage = await page.context().newPage();
    const token = issueRegistrationToken(email);

    await clientPage.goto(`/client-login?token=${encodeURIComponent(token)}`);
    await clientPage.waitForURL('**/order/edit');
    const clientAuth = await clientPage.evaluate(() => JSON.parse(localStorage.getItem('gaerngschee.auth') ?? '{}'));
    const clientHeaders = await authHeaders(clientPage);
    const saveResponse = await clientPage.request.put('http://localhost:8082/client/order', {
      headers: { ...clientHeaders, Authorization: `Bearer ${clientAuth.token}` },
      data: { adultsCount: 1, childrenCount: 0, adults: ['catA'], children: [] },
    });
    expect(saveResponse.status()).toBe(200);
    await clientPage.close();

    await loginAsAdmin(page);
    await page.goto('/admin/client-deletion');
    await page.locator('input[type="email"]').fill(email);
    await page.locator('button[type="submit"]').click();
    await expect(page.getByText(email)).toBeVisible();
    await page.getByRole('button', { name: 'Client-Bestellung löschen' }).click();
    await page.getByRole('dialog').getByRole('button', { name: 'Weiter' }).click();
    let deleteEventValue = 0;
    page.on('response', async (response) => {
      if (response.request().method() === 'DELETE' && response.url().includes('/admin/client-deletion/')) {
        deleteEventValue = Number(response.headers()['x-event-order-status-change'] ?? 0);
      }
    });
    await page.getByRole('dialog').getByRole('button', { name: 'Endgültig löschen' }).click();

    await expect(page.getByRole('alert')).toBeVisible();
    await expect.poll(() => deleteEventValue).toBeGreaterThan(0);
  });
});
