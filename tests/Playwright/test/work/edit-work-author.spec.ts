import {expect} from '@playwright/test';
import {test} from '@playwright-test/fixtures/command';
import {successUserData} from '@playwright-test/fixtures/user/user';

const { email, username, ...successData } = successUserData;

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {});

    test('Edit work user success ajax', async ({page}) => {
        await page.goto('/en/work/supervisor/list');

        await page.locator('.work-group-list').first().click();
        const groupHref = await page.locator('.btn.btn-primary.btn-xs').first().getAttribute('href') as string;
        await page.goto(groupHref);

        await page.click('#toggle-admin-panel');
        await page.click('#work-author-edit');

        for (const prop in successData) {
            const propKey = prop as keyof typeof successData;

            await page.locator(successData[propKey].id).clear();
            await page.fill(
                successData[propKey].id,
                successData[propKey].text.toString()
            );
        }

        await page.click('#user-button-action');
        await page.waitForTimeout(1000);

        await expect(page.locator('.alert-success')).toBeVisible();
    });

    test('Edit work user success', async ({page}) => {
        await page.goto('/en/work/supervisor/list');

        await page.locator('.work-group-list').first().click();
        const groupHref = await page.locator('.btn.btn-primary.btn-xs').first().getAttribute('href') as string;
        await page.goto(groupHref);

        await page.click('#work-open-admin-panel');
        const editHref = await page.locator('#work-author-edit').getAttribute('href') as string;
        await page.goto(editHref);

        for (const prop in successData) {
            const propKey = prop as keyof typeof successData;

            await page.locator(successData[propKey].id).clear();
            await page.fill(
                successData[propKey].id,
                successData[propKey].text.toString()
            );
        }

        await page.click('#user-button-action');
        await page.waitForTimeout(1000);

        await expect(page.locator('.alert-success')).toBeVisible();
    });
}
