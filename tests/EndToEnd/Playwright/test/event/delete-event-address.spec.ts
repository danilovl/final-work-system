import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {addressData} from '@playwright-test//fixtures/event/address'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Delete event address success ajax', async ({page}) => {
        const name = 'Delete address ' + Math.random().toString(36).substring(2, 10)

        await page.goto('/en/event/address/create')
        await page.fill(addressData.name.id, name)
        await page.fill(addressData.street.id, addressData.street.text)
        await page.fill(addressData.latitude.id, addressData.latitude.text)
        await page.fill(addressData.longitude.id, addressData.longitude.text)
        await page.locator('#create-event-address-button-action').click()
        await page.waitForURL(/\/event\/address\/detail\//)

        const row = page.locator('tr', {hasText: name})

        for (let pageNumber = 1; pageNumber <= 20; pageNumber++) {
            await page.goto(`/en/event/address/list?page=${pageNumber}`)

            if (await row.count() > 0) {
                break
            }
        }

        await page.addStyleTag({content: '.sf-toolbar, .ui-pnotify { display: none !important; }'})
        await row.locator('.btn.btn-danger.btn-xs').click()
        await page.locator('.btn.btn-danger.delete-element:visible').click()

        await expect(page.locator('.alert-success')).toBeVisible()
        await expect(row).toHaveCount(0)
    })
}
