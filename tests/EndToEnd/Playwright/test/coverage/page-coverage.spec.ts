import {expect, Page} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import SiteCrawler from '@playwright-test/page/site-crawler'
import {createStudentWork} from '@playwright-test/page/student-work'

const roles = {
    student: {
        startPaths: [
            '/en',
            '/en/user/profile',
            '/en/user/profile/change-password',
            '/en/work/author/list',
            '/en/conversation/list',
            '/en/document/list',
            '/en/event/calendar/reservation'
        ],
        forbiddenPaths: [
            '/en/admin/dashboard',
            '/en/work/supervisor/list',
            '/en/work/opponent/list',
            '/en/work/consultant/list',
            '/en/work/task/list',
            '/en/work/create',
            '/en/work/category/list',
            '/en/document/list/owner',
            '/en/document/create',
            '/en/event/list',
            '/en/event/address/list',
            '/en/event/schedule/list',
            '/en/event/calendar/manage'
        ]
    },
    admin: {
        startPaths: [
            '/en',
            '/en/admin/dashboard',
            '/en/user/profile',
            '/en/conversation/list'
        ],
        forbiddenPaths: [
            '/en/work/supervisor/list',
            '/en/work/author/list',
            '/en/work/task/list',
            '/en/document/list',
            '/en/document/list/owner',
            '/en/event/list',
            '/en/event/calendar/manage',
            '/en/event/calendar/reservation'
        ]
    }
}

function createCrawler(page: Page): SiteCrawler {
    const baseUrl = test.info().project.use.baseURL as string

    return new SiteCrawler(page, baseUrl)
}

async function assertAllPagesOpen(page: Page, startPaths: string[]): Promise<void> {
    const crawler = createCrawler(page)
    const visited = await crawler.crawl(startPaths)
    const failed = visited.filter(visitedPage => visitedPage.status !== 200 || visitedPage.errors.length > 0)
    const report = JSON.stringify(visited, null, 4)

    await test.info().attach('visited-pages', {body: report, contentType: 'application/json'})

    expect(visited.length).toBeGreaterThanOrEqual(startPaths.length)
    expect(failed).toEqual([])
}

async function assertPagesForbidden(page: Page, forbiddenPaths: string[]): Promise<void> {
    const crawler = createCrawler(page)
    const statuses: Record<string, number> = {}

    for (const path of forbiddenPaths) {
        const visitedPage = await crawler.visit(path, '', false)
        statuses[path] = visitedPage.status
    }

    const expected = Object.fromEntries(forbiddenPaths.map(path => [path, 403]))

    expect(statuses).toEqual(expected)
}

export default function createTests() {
    test.describe('Student page coverage', () => {
        test.beforeEach(async ({loginStudent}) => {
        })

        test('Student opens every reachable page', async ({page, browser}) => {
            test.setTimeout(10 * 60 * 1000)

            const baseUrl = test.info().project.use.baseURL as string
            await createStudentWork(browser, baseUrl)
            await assertAllPagesOpen(page, roles.student.startPaths)
        })

        test('Student gets 403 on pages of other roles', async ({page}) => {
            await assertPagesForbidden(page, roles.student.forbiddenPaths)
        })
    })

    test.describe('Admin page coverage', () => {
        test.beforeEach(async ({loginAdmin}) => {
        })

        test('Admin opens every reachable page', async ({page}) => {
            test.setTimeout(10 * 60 * 1000)

            await assertAllPagesOpen(page, roles.admin.startPaths)
        })

        test('Admin gets 403 on pages of other roles', async ({page}) => {
            await assertPagesForbidden(page, roles.admin.forbiddenPaths)
        })
    })
}
