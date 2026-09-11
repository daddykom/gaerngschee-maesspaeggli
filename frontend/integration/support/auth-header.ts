import { Page } from '@playwright/test';

export async function authHeaders(page: Page): Promise<{ Cookie: string; 'X-CSRF-Token': string }> {
  const cookies = await page.context().cookies('http://localhost:8082');
  const csrfToken = cookies.find(({ name }) => name === 'XSRF-TOKEN')?.value;
  if (!csrfToken) {
    throw new Error('Expected a CSRF token after login.');
  }

  return {
    Cookie: cookies.map(({ name, value }) => `${name}=${value}`).join('; '),
    'X-CSRF-Token': csrfToken,
  };
}
