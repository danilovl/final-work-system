import {expect} from '@playwright/test';
import {test} from '@playwright-test/fixtures/command';

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {});

    test('Delete conversation success', async ({page}) => {
        await page.goto('/en/conversation/list');
        await page.waitForTimeout(2000);

        const deleteButton = page.locator('.btn.btn-danger').first();
        const dataTarget = await deleteButton.getAttribute('data-target');

        await deleteButton.click();
        await page.locator(`${dataTarget} .delete-element`).click();

        await page.waitForTimeout(1000);

        await expect(page.locator('.alert-success')).toBeVisible();
    });
}
