import { expect, test } from '@playwright/test';

test.describe('Root route responsive layout', () => {
  for (const viewport of [
    { name: 'mobile', width: 375, height: 812 },
    { name: 'desktop', width: 1440, height: 900 },
  ]) {
    test(`${viewport.name} layout has no horizontal overflow and matches the baseline`, async ({ page }) => {
      await page.setViewportSize({ width: viewport.width, height: viewport.height });
      await page.goto('/');

      const dimensions = await page.evaluate(() => ({
        documentWidth: document.documentElement.scrollWidth,
        viewportWidth: window.innerWidth,
      }));
      expect(dimensions.documentWidth).toBeLessThanOrEqual(dimensions.viewportWidth);
      await expect(page).toHaveScreenshot(`root-${viewport.name}.png`, {
        maxDiffPixelRatio: 0.12,
      });
    });
  }
});
