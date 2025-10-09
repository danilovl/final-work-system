import {expect} from '@playwright/test';
import {test} from '@playwright-test/fixtures/command';
import {successUserData} from '@playwright-test/fixtures/user/user';

export default function createTests() {
    const failedUserData = {...successUserData};

    failedUserData.email = {
        id: '#user_email',
        text: Math.random().toString(36).substring(2, 10)
    };

    failedUserData.username = {
        id: '#user_username',
        text: Math.random().toString(36).substring(2, 10)
    };

    test.beforeEach(async ({loginSupervisor}) => {});

    test('Create user success', async ({page}) => {
        await page.goto('/en/user/create');

        for (const prop in successUserData) {
            await page.fill(
                successUserData[prop as keyof typeof successUserData].id,
                successUserData[prop as keyof typeof successUserData].text.toString()
            );
        }

        await page.locator('#user-button-action').click();
        await page.waitForTimeout(1000);

        await expect(page.locator('.alert-success')).toBeVisible();
    });

    test('Create user failed', async ({page}) => {
        await page.goto('/en/user/create');

        for (const prop in failedUserData) {
            await page.fill(
                failedUserData[prop as keyof typeof failedUserData].id,
                failedUserData[prop as keyof typeof failedUserData].text.toString()
            );
        }

        await page.locator('#user-button-action').click();

        await expect(page.locator('.alert-danger')).toBeVisible();
    });
}
