import {expect} from '@playwright/test';
import {test} from '@playwright-test/fixtures/command';

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {});

    test('Delete task success ajax', async ({page}) => {
        await page.goto('/en/work/task/list');

        await page.locator('.btn.btn-danger.btn-xs').first().click();
        await page.locator('.btn.btn-danger.delete-element').first().click();

        await page.waitForTimeout(1000);

        await expect(page.locator('.alert-success')).toBeVisible();
    });
}
