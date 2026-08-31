import { Page } from '@playwright/test';

export async function loginAsUser(page: Page): Promise<void> {
  await page.goto('/login');
  await page.locator('input[type="email"]').fill('user@gaerngschee.ch');
  await page.locator('input[type="password"]').fill('secret');
  await page.getByRole('button', { name: 'Anmelden' }).click();
  await page.waitForURL('**/admin/overview');
}
