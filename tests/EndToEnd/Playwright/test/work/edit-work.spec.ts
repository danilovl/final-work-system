import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {workData} from '@playwright-test/fixtures/work/work'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Edit work success ajax', async ({page}) => {
        await page.goto('/en/work/supervisor/list')

        await page.locator('.work-group-list').first().click()
        const groupHref = await page.locator('.btn.btn-primary.btn-xs').first().getAttribute('href') as string
        await page.goto(groupHref)

        await page.click('#toggle-admin-panel')
        await page.click('#work-edit')

        for (const prop in workData) {
            const propKey = prop as keyof typeof workData

            await page.locator(workData[propKey].id).clear()
            await page.fill(
                workData[propKey].id,
                workData[propKey].text.toString()
            )
        }

        await page.click('#work-button-action')
        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })

    test('Edit work success', async ({page}) => {
        await page.goto('/en/work/supervisor/list')

        await page.locator('.work-group-list').first().click()
        const groupHref = await page.locator('.btn.btn-primary.btn-xs').first().getAttribute('href') as string
        await page.goto(groupHref)

        await page.click('#work-open-admin-panel')
        const editHref = await page.locator('#work-edit').getAttribute('href') as string
        await page.goto(editHref)

        for (const prop in workData) {
            const propKey = prop as keyof typeof workData

            await page.locator(workData[propKey].id).clear()
            await page.fill(
                workData[propKey].id,
                workData[propKey].text.toString()
            )
        }

        await page.click('#work-button-action')
        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
