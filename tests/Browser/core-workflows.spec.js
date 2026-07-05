import { expect, test } from '@playwright/test';

async function login(page, username, password = 'password') {
    await page.goto('/login');
    await page.getByLabel('Username').fill(username);
    await page.getByLabel('Password').fill(password);
    await page.getByRole('button', { name: /sign in/i }).click();
}

test('administrator reaches finance and reconciliation workflows', async ({ page }) => {
    await login(page, 'admin');
    await expect(page).toHaveURL(/admin\/dashboard/);
    await page.getByRole('link', { name: 'Bank Reconciliation' }).click();
    await expect(page.getByRole('heading', { name: 'Bank Reconciliation' })).toBeVisible();
    await page.getByRole('link', { name: 'Student Payments' }).click();
    await expect(page.getByRole('heading', { name: 'Student Payments' })).toBeVisible();
});

test('teacher remains scoped to teacher navigation', async ({ page }) => {
    await login(page, 'teacher');
    await expect(page).toHaveURL(/teacher\/dashboard/);
    await expect(page.getByRole('link', { name: 'My Students', exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'User Management' })).toHaveCount(0);
});

test('login and navigation fit a mobile viewport', async ({ page }) => {
    await page.goto('/login');
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();
    await login(page, 'admin');
    await expect(page.locator('body')).not.toHaveCSS('overflow-x', 'scroll');
});
