import { expect, test } from '@playwright/test';
import { issueRegistrationToken } from './support/registration-token';

test.describe('Integration registration edge cases', () => {
  test('rejects an expired registration token', async ({ page }) => {
    const token = issueRegistrationToken(`expired-${Date.now()}@example.com`, 'expired');
    const response = await page.request.post('http://localhost:8082/auth/registration-login', {
      data: { token },
    });

    expect(response.status()).toBe(401);
    await expect(response.json()).resolves.toEqual({
      error: { code: 'INVALID_REGISTRATION_TOKEN', details: [] },
    });
  });

  test('does not convert an existing admin or user account into a client', async ({ page }) => {
    for (const email of ['admin@gaerngschee.ch', 'user@gaerngschee.ch']) {
      const token = issueRegistrationToken(email);
      const response = await page.request.post('http://localhost:8082/auth/registration-login', {
        data: { token },
      });

      expect(response.status()).toBe(401);
      await expect(response.json()).resolves.toEqual({
        error: { code: 'INVALID_REGISTRATION_TOKEN', details: [] },
      });
    }
  });

  test('invalidates the previous token when a new one is issued', async ({ page }) => {
    const email = `reissued-${Date.now()}@example.com`;
    const firstToken = issueRegistrationToken(email);
    const secondToken = issueRegistrationToken(email);

    const firstResponse = await page.request.post('http://localhost:8082/auth/registration-login', {
      data: { token: firstToken },
    });
    expect(firstResponse.status()).toBe(401);

    const secondResponse = await page.request.post(
      'http://localhost:8082/auth/registration-login',
      {
        data: { token: secondToken },
      },
    );
    expect(secondResponse.status()).toBe(200);
  });
});
