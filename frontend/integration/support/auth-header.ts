import { Page } from '@playwright/test';

export async function authHeaders(page: Page): Promise<{ Authorization: string }> {
  const token = await page.evaluate(() => {
    const raw = localStorage.getItem('gaerngschee.auth');
    return raw === null ? null : (JSON.parse(raw) as { token?: string }).token;
  });

  if (!token) {
    throw new Error('Expected an authentication token after login.');
  }

  return { Authorization: `Bearer ${token}` };
}
