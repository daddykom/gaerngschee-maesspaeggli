import { expect, test } from '@playwright/test';

test.describe('Start route', () => {
  test('displays the email form', async ({ page }) => {
    await page.goto('/start');
    await expect(page.locator('h1')).toHaveText('Mässpäggli bekommen');
    await expect(page.locator('input[type="email"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('displays the Fairgate info box with a background color', async ({ page }) => {
    await page.goto('/start');

    const backgroundColor = await page.locator('app-info-box .info-box').evaluate(
      (element) => getComputedStyle(element).backgroundColor,
    );

    expect(backgroundColor).toMatch(/^rgb\(/);
  });

  test('validates an invalid email without calling the backend', async ({ page }) => {
    let requestCalled = false;
    await page.route('http://localhost:8080/public/start', async (route) => {
      requestCalled = true;
      await route.continue();
    });

    await page.goto('/start');
    await page.locator('input[type="email"]').fill('invalid');
    await page.getByRole('button', { name: 'Weiter' }).click();

    await expect(page.getByText('Bitte prüfe deine E-Mail-Adresse.')).toBeVisible();
    expect(requestCalled).toBe(false);
  });

  test('submits the email request', async ({ page }) => {
    await page.route('http://localhost:8080/public/start', async (route) => {
      expect(route.request().postDataJSON()).toEqual({ email: 'person@example.com', language: 'de' });
      await route.fulfill({ status: 202, contentType: 'application/json', body: JSON.stringify({ sent: true }) });
    });

    await page.goto('/start');
    await page.locator('input[type="email"]').fill('person@example.com');
    await page.getByRole('button', { name: 'Weiter' }).click();
    await expect(page.locator('input[type="email"]')).toHaveValue('person@example.com');
  });
});
