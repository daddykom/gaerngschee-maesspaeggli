import { expect, test } from '@playwright/test';
import { issueRegistrationToken } from './support/registration-token';
import { authHeaders } from './support/auth-header';

test.describe('Integration order', () => {
  test('saves a family order with children packages only', async ({ page }) => {
    const email = `order+family-${Date.now()}@example.com`;
    const token = issueRegistrationToken(email);

    await page.goto(`/client-login?token=${encodeURIComponent(token)}`);
    await page.waitForURL('**/order/edit');

    const auth = await page.evaluate(() => JSON.parse(localStorage.getItem('gaerngschee.auth') ?? '{}'));
    const headers = await authHeaders(page);
    const response = await page.request.put('http://localhost:8082/client/order', {
      headers: { ...headers, Authorization: `Bearer ${auth.token}` },
      data: {
        adultsCount: 2,
        childrenCount: 3,
        adults: [],
        children: ['catC', 'catD', 'catE'],
      },
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.order.adultsCount).toBe(2);
    expect(data.order.childrenCount).toBe(3);
    expect(data.order.items).toEqual([
      { personType: 'child', category: 'catC', quantity: 1 },
      { personType: 'child', category: 'catD', quantity: 1 },
      { personType: 'child', category: 'catE', quantity: 1 },
    ]);
  });
});
