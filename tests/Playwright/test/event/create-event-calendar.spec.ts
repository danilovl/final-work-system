import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createEventCalendarTests() {
    test.beforeEach(async ({loginSupervisor}) => {})

    test('Create event calendar success', async ({page}) => {
        await page.goto('/en/event/calendar/manage')
        await page.waitForTimeout(2000)

        await page.locator('.fc-day-future').first().click()

        await page.locator('#select2-event_address-container').click()
        await page.locator('li[id^="select2-event_address-result"]').first().click()

        await page.locator('#select2-event_participant-container').click()
        await page.locator('li[id^="select2-event_participant-result"]').first().click()

        await page.click('#event_create')
        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
