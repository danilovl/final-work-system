import {Browser, expect, Page} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import LoginPage from '@playwright-test/page/login-page'
import SiteCrawler from '@playwright-test/page/site-crawler'
import loginData from '@playwright-test/test-data/login'

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

async function createStudentWork(browser: Browser): Promise<void> {
    const baseUrl = test.info().project.use.baseURL as string
    const context = await browser.newContext({baseURL: baseUrl})
    const page = await context.newPage()
    const title = 'Coverage work ' + Math.random().toString(36).substring(2, 10)

    const loginPage = new LoginPage(page)
    await loginPage.login(loginData.supervisor.username, loginData.supervisor.password)

    await page.goto('/en/work/create')
    await page.fill('#work_title', title)
    await page.locator('#work_deadline').clear()
    await page.fill('#work_deadline', '2030-01-01')
    await page.evaluate(username => {
        const select = document.querySelector<HTMLSelectElement>('#work_author') as HTMLSelectElement
        const option = Array.from(select.options).find(item => item.text.trim().endsWith(`(${username})`)) as HTMLOptionElement
        select.value = option.value
        select.dispatchEvent(new Event('change', {bubbles: true}))
    }, loginData.student.username)
    await page.evaluate(() => {
        const select = document.querySelector<HTMLSelectElement>('#work_status') as HTMLSelectElement
        select.value = '1'
        select.dispatchEvent(new Event('change', {bubbles: true}))
    })
    await page.click('#work-button-action')
    await expect(page.locator('.alert-success')).toBeVisible()

    const workRow = page.locator('tr[data-delete-row^="work-"]', {hasText: title})
    await expect.poll(async () => {
        await page.goto('/en/work/supervisor/list')

        return workRow.count()
    }).toBe(1)

    const detailHref = await workRow.locator('a[href*="/work/detail/"]:not(.btn)').getAttribute('href') as string
    const workHash = detailHref.split('/').pop() as string

    await page.goto(`/en/work/${workHash}/task/create`)
    await page.fill('#task_name', 'Coverage task')
    await page.locator('#task_deadline').clear()
    await page.fill('#task_deadline', '2030-01-01')
    await page.click('#task-button-action')
    await expect(page.locator('.alert-success')).toBeVisible()

    await page.goto(detailHref)
    const conversationAction = await page
        .locator('form[action*="/conversation/create/work/"]')
        .first()
        .getAttribute('action') as string
    await page.request.post(conversationAction)

    await context.close()
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
        const visitedPage = await crawler.visit(path)
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

            await createStudentWork(browser)
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
