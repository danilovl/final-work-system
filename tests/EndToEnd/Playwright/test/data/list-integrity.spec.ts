import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import ListPage from '@playwright-test/page/list-page'

const paginatedLists = [
    {name: 'tasks', url: '/en/work/task/list', attribute: 'data-delete-row', prefix: 'task-'},
    {name: 'events', url: '/en/event/list', attribute: 'data-delete-row', prefix: 'event-'},
    {name: 'authors', url: '/en/user/author/list', attribute: 'id', prefix: 'heading-user-description-'},
    {name: 'works', url: '/en/work/supervisor/list', attribute: 'data-delete-row', prefix: 'work-'}
]

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    for (const list of paginatedLists) {
        test(`List ${list.name} shows every row exactly once across pages`, async ({page}) => {
            const listPage = new ListPage(page)
            const pages = await listPage.allPagesRowIds(list.url, list.attribute, list.prefix)
            const ids = pages.flat()

            expect(new Set(ids).size).toBe(ids.length)
        })

        test(`List ${list.name} keeps the same order on reload`, async ({page}) => {
            const listPage = new ListPage(page)
            const firstLoad = await listPage.rowIds(list.url, list.attribute, list.prefix)
            const secondLoad = await listPage.rowIds(list.url, list.attribute, list.prefix)

            expect(secondLoad).toEqual(firstLoad)
        })
    }

    test('Work titles in list match work detail', async ({page}) => {
        await page.goto('/en/work/supervisor/list')

        const links = await page
            .locator('tr[data-delete-row^="work-"] a[href*="/work/detail/"]:not(.btn)')
            .evaluateAll(elements => elements.slice(0, 5).map(element => ({
                href: element.getAttribute('href') ?? '',
                title: (element.textContent ?? '').trim()
            })))

        expect(links.length).toBeGreaterThan(0)

        for (const link of links) {
            await page.goto(link.href)

            await expect(page.locator('#work-main-info .x_title h2')).toContainText(link.title)
        }
    })
}
