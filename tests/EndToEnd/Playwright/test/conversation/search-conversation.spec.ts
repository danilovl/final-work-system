import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Search conversation success', async ({page}) => {
        await page.goto('/en/conversation/list')
        await page.waitForLoadState('networkidle')

        await page.fill('#simple_search_search', 'test')
        await page.click('#simple-search-button-action')
        await page.waitForURL(/simple_search/)

        await page.click('#clear-simple-search-button-action')
        await page.waitForURL(url => !url.search.includes('simple_search'))
    })
}
