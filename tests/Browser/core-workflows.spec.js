import { expect, test } from '@playwright/test';

async function login(page, username, password = 'password') {
    await page.goto('/login');
    await page.getByLabel('Username').fill(username);
    await page.getByLabel('Password').fill(password);
    await page.getByRole('button', { name: /sign in/i }).click();

    const locale = page.locator('#locale');
    if (await locale.count() && await locale.inputValue() !== 'en') {
        await locale.selectOption('en');
        await locale.locator('xpath=ancestor::form').getByRole('button').click();
    }
}

async function openNavigation(page) {
    const toggle = page.locator('#mobile-navigation-toggle');

    if (await toggle.isVisible() && await toggle.getAttribute('aria-expanded') === 'false') {
        await toggle.click();
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    }
}

test('administrator completes finance navigation, month context, localization, and responsive checks', async ({ page }) => {
    await page.goto('/login');
    await expect(page.getByRole('button', { name: /sign in/i })).toBeVisible();
    await login(page, 'admin');
    await expect(page).toHaveURL(/admin\/dashboard/);
    await expect(page.locator('body')).not.toHaveCSS('overflow-x', 'scroll');

    const toggle = page.locator('#mobile-navigation-toggle');
    if (await toggle.isVisible()) {
        await expect(page.locator('#primary-navigation')).toBeHidden();
    }
    await openNavigation(page);
    await page.getByRole('link', { name: 'Bank Reconciliation', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Bank Reconciliation' })).toBeVisible();
    await openNavigation(page);
    await page.getByRole('link', { name: 'Student Payments', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Student Payments' })).toBeVisible();

    await page.goto('/admin/dashboard?month=2026-05');
    for (const path of ['finance-summary', 'lesson-counts', 'month-closing', 'student-charges', 'payments', 'expenses', 'bank-months']) {
        await expect(page.locator(`a[href*="/${path}"][href*="month=2026-05"]`)).toHaveCount(1);
    }

    await page.locator('#locale').selectOption('uk');
    await page.locator('#locale').locator('xpath=ancestor::form').getByRole('button').click();
    await openNavigation(page);
    await page.getByRole('link', { name: 'Звірка банку', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Звірка банку' })).toBeVisible();
    await page.locator('#locale').selectOption('en');
    await page.locator('#locale').locator('xpath=ancestor::form').getByRole('button').click();
});

test('teacher remains scoped with responsive navigation', async ({ page }) => {
    await login(page, 'teacher');
    await expect(page).toHaveURL(/teacher\/dashboard/);
    await openNavigation(page);
    await expect(page.getByRole('link', { name: 'My Students', exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'User Management' })).toHaveCount(0);
    await expect(page.locator('body')).not.toHaveCSS('overflow-x', 'scroll');
});
