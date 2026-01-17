import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {
    contactInformationData,
    personalInformationData,
    messageData,
    tabData
} from '@playwright-test/fixtures/user/user-profile'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Edit user profile success', async ({page}) => {
        await page.goto('/en/user/profile/edit')

        for (const prop in contactInformationData) {
            const propKey = prop as keyof typeof contactInformationData

            await page
                .locator(contactInformationData[propKey].id)
                .clear()

            await page.fill(
                contactInformationData[propKey].id,
                contactInformationData[propKey].text.toString()
            )
        }

        await page.locator(tabData.tabPersonal.id).click()

        for (const prop in personalInformationData) {
            const propKey = prop as keyof typeof personalInformationData

            await page
                .locator(personalInformationData[propKey].id)
                .clear()

            await page.fill(
                personalInformationData[propKey].id,
                personalInformationData[propKey].text.toString()
            )
        }

        await page.locator(tabData.tabMessage.id).click()

        for (const prop in messageData) {
            const propKey = prop as keyof typeof messageData

            await page.evaluate(
                ({id, text}) => {
                    const editor = (window as any).tinymce.get(id)
                    if (editor) {
                        editor.setContent(`<strong>${text}</strong>`)
                    }
                },
                {
                    id: messageData[propKey].id,
                    text: messageData[propKey].text.toString()
                }
            )
        }

        await page.locator('#user-edit-profile-button-action').click()
        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
