import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Date picker selects date and closes on typing, Escape and Tab', async ({page}) => {
        await page.goto('/en/work/create')
        await page.addStyleTag({content: '.sf-toolbar { display: none !important; }'})

        const input = page.locator('#work_deadline')
        const calendar = page.locator('[data-vc-input]').first()
        const openCalendar = async () => {
            await input.click()
            await expect(calendar).toBeVisible()
            await expect(calendar).not.toHaveAttribute('data-vc-calendar-hidden')
        }

        await openCalendar()

        const dayCell = calendar.locator('[data-vc-date]:not([data-vc-date-month="prev"]):not([data-vc-date-month="next"])').nth(10)
        const expectedDate = await dayCell.getAttribute('data-vc-date')
        await dayCell.locator('[data-vc-date-btn]').click()

        await expect(input).toHaveValue(expectedDate as string)
        await expect(calendar).toHaveAttribute('data-vc-calendar-hidden')

        await openCalendar()
        await page.keyboard.press('Escape')
        await expect(calendar).toHaveAttribute('data-vc-calendar-hidden')

        await openCalendar()
        await page.keyboard.press('Tab')
        await expect(calendar).toHaveAttribute('data-vc-calendar-hidden')

        await openCalendar()
        await input.fill('2030-01-15')
        await expect(calendar).toHaveAttribute('data-vc-calendar-hidden')
        await expect(input).toHaveValue('2030-01-15')
    })
}
