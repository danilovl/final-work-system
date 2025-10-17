import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {addressData} from '@playwright-test//fixtures/event/address'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {})

    test('Create event address success', async ({page}) => {
        await page.goto('/en/event/address/create')
        await page.waitForTimeout(2000)

        for (const prop in addressData) {
            const propKey = prop as keyof typeof addressData

            if (addressData[propKey].tinymce) {
                await page.evaluate(
                    (content) => {
                        (window as any).tinymce.activeEditor.setContent(`<strong>${content}</strong>`)
                    },
                    addressData[propKey].text
                )
            } else {
                await page.fill(
                    addressData[propKey].id,
                    addressData[propKey].text.toString()
                )
            }
        }

        await page.click('#create-event-address-button-action')
        await page.waitForTimeout(2000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
