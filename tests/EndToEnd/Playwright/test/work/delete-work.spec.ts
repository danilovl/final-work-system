import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {workData} from '@playwright-test/fixtures/work/work'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Delete work success ajax', async ({page}) => {
        await page.goto('/en/work/supervisor/list')

        await page.click('#toggle-search-work-form')
        await page.fill('#work_search_shortcut', workData.shortcut.text)
        const searchResponsePromise = page.waitForResponse(response => {
            return response.request().method() === 'POST' && response.url().includes('/work/supervisor/list')
        })

        await page.click('#search-work-button')
        await searchResponsePromise
        await page.waitForLoadState('networkidle')

        await page.locator('.work-group-list').first().click()
        await page.click('#toggle-search-work-form')

        await page.locator('.btn.btn-danger.btn-xs').first().click()
        await page.locator('.btn.btn-danger.delete-element').first().click()


        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
