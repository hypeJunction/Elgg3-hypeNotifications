import { test, expect } from '@playwright/test';

/**
 * E2E smoke for hypenotifications.
 *
 * Notification rules + delivery-config plugin: typically registers
 * actions for managing user notification preferences, view extensions
 * for the settings UI, and hook handlers for delivery routing.
 *
 * The smoke surface validates that activation doesn't break the site
 * and that the css aggregates still compile after the plugin's view
 * extensions are applied.
 */
test.describe('hypenotifications', () => {
  test('homepage renders with no PHP fatal markers', async ({ page }) => {
    const response = await page.goto('/');
    expect(response).toBeTruthy();
    expect(response!.status()).toBeLessThan(500);
    const body = await page.content();
    expect(body).not.toContain('Fatal error');
    expect(body).not.toContain('Uncaught');
    expect(body).not.toContain('ParseError');
  });

  test('default css aggregate compiles', async ({ page }) => {
    const response = await page.goto('/cache/0/default/elgg.css');
    expect(response).toBeTruthy();
    if (response!.status() !== 404) {
      expect(response!.status()).toBeLessThan(400);
      expect(response!.headers()['content-type'] || '').toMatch(/css|text/);
    }
  });

  test('admin css aggregate compiles', async ({ page }) => {
    const response = await page.goto('/cache/0/default/admin.css');
    expect(response).toBeTruthy();
    if (response!.status() !== 404) {
      expect(response!.status()).toBeLessThan(400);
      expect(response!.headers()['content-type'] || '').toMatch(/css|text/);
    }
  });
});
