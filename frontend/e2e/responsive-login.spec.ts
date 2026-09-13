import { expect, test } from '@playwright/test';

test.describe('Login route responsive layout', () => {
  for (const viewport of [
    { name: 'mobile', width: 375, height: 812 },
    { name: 'desktop', width: 1440, height: 900 },
  ]) {
    test(`${viewport.name} layout keeps the form within the viewport and matches the baseline`, async ({ page }) => {
      await page.setViewportSize({ width: viewport.width, height: viewport.height });
      await page.goto('/login');

      const formBounds = await page.locator('form').boundingBox();
      expect(formBounds).not.toBeNull();
      expect(formBounds?.x).toBeGreaterThanOrEqual(0);
      expect((formBounds?.x ?? 0) + (formBounds?.width ?? 0)).toBeLessThanOrEqual(viewport.width);
      await expect(page).toHaveScreenshot(`login-${viewport.name}.png`, {
        maxDiffPixelRatio: 0.12,
      });
    });
  }
});
