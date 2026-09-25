import { expect, test as base, type Page } from '@playwright/test';

export const test = base.extend({
  page: async ({ page }, use) => {
    await page.route('http://localhost:8080/public/configuration**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify([
          { variableName: 'campaign_start_date', value: '2000-01-01' },
          { variableName: 'campaign_end_date', value: '2999-12-31' },
        ]),
      });
    });

    await page.route('http://localhost:8080/admin/overview**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          year: 2026,
          recentDays: 14,
          orders: { provisional: 0, recentProvisional: 0, definitive: 0, toDeliver: 0, qrcode: 0, delivered: 0 },
          categories: [],
        }),
      });
    });

    await page.route('http://localhost:8080/client/order**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ order: null }),
      });
    });

    await page.route('**/auth/session-status**', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          expiresAt: new Date(Date.now() + 3_600_000).toISOString(),
          secondsRemaining: 3_600,
        }),
      });
    });

    await use(page);
  },
});

export { expect, type Page };
