import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Delete event calendar success', async ({page}) => {
        await page.goto('/en/event/calendar/manage')
        await page.waitForLoadState('networkidle')

        await page.locator('.fc-event-draggable').first().click()
        await page.click('#event-delete')


        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
