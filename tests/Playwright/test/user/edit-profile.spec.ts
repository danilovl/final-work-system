import {expect} from '@playwright/test';
import {test} from '@playwright-test/fixtures/command';
import {contactInformationData, personalInformationData, messageData, tabData} from '@playwright-test/fixtures/user/user-profile';

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {});

    test('Edit user profile success', async ({ page }) => {
        await page.goto('/en/user/profile/edit');

        for (const prop in contactInformationData) {
            await page
                .locator(contactInformationData[prop as keyof typeof contactInformationData].id)
                .clear();

            await page.fill(
                contactInformationData[prop as keyof typeof contactInformationData].id,
                contactInformationData[prop as keyof typeof contactInformationData].text.toString()
            );
         }

        await page.locator(tabData.tabPersonal.id).click();

        for (const prop in personalInformationData) {
            await page
                .locator(personalInformationData[prop as keyof typeof personalInformationData].id)
                .clear();

            await page.fill(
                personalInformationData[prop as keyof typeof personalInformationData].id,
                personalInformationData[prop as keyof typeof personalInformationData].text.toString()
            );
        }

        await page.locator(tabData.tabMessage.id).click();

        for (const prop in messageData) {
            await page.evaluate(
                ({ id, text }) => {
                    const editor = (window as any).tinymce.get(id);
                    if (editor) {
                        editor.setContent(`<strong>${text}</strong>`);
                    }
                },
                {
                    id: messageData[prop as keyof typeof messageData].id,
                    text: messageData[prop as keyof typeof messageData].text.toString()
                }
            );
        }

        await page.locator('#user-edit-profile-button-action').click();
        await page.waitForTimeout(1000);

        await expect(page.locator('.alert-success')).toBeVisible();
    });
}
