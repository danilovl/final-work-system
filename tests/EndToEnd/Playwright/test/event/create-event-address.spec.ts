import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {addressData} from '@playwright-test//fixtures/event/address'
import {setEditorContent} from '@playwright-test/page/tinymce'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Create event address success', async ({page}) => {
        await page.goto('/en/event/address/create')
        await page.waitForLoadState('networkidle')

        for (const prop in addressData) {
            const propKey = prop as keyof typeof addressData

            if (addressData[propKey].tinymce) {
                await setEditorContent(page, `<strong>${addressData[propKey].text}</strong>`)
            } else {
                await page.fill(
                    addressData[propKey].id,
                    addressData[propKey].text.toString()
                )
            }
        }

        await page.click('#create-event-address-button-action')

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
