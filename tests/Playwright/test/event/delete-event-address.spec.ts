import {expect} from '@playwright/test';
import {test} from '@playwright-test/fixtures/command';

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {});

    test('Delete event address success ajax', async ({page}) => {
        await page.goto('/en/event/address/list');

        await page.locator('.btn.btn-danger.btn-xs').first().click();
        await page.locator('.btn.btn-danger.delete-element').first().click();

        await page.waitForTimeout(2000);

        await expect(page.locator('.alert-success')).toBeVisible();
    });
}
