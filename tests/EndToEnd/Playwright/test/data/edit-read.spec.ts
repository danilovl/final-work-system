import {expect, Page} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

const uniqueText = (prefix: string): string => prefix + ' ' + Math.random().toString(36).substring(2, 10)

async function openFirstWorkDetail(page: Page): Promise<string> {
    await page.goto('/en/work/supervisor/list')

    await page.locator('.work-group-list').first().click()
    const detailHref = await page.locator('.btn.btn-primary.btn-xs').first().getAttribute('href') as string
    await page.goto(detailHref)

    return detailHref
}

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Edited work is read back in detail and in work list', async ({page}) => {
        const title = uniqueText('Read back work')
        const detailHref = await openFirstWorkDetail(page)

        await page.click('#work-open-admin-panel')
        const editHref = await page.locator('#work-edit').getAttribute('href') as string
        await page.goto(editHref)

        await page.locator('#work_title').clear()
        await page.fill('#work_title', title)
        await page.click('#work-button-action')
        await expect(page.locator('.alert-success')).toBeVisible()

        await page.goto(detailHref)
        await expect(page.locator('#work-main-info .x_title h2')).toContainText(title)

        await page.goto('/en/work/supervisor/list')
        await expect(page.locator('tr[data-delete-row^="work-"]', {hasText: title})).toHaveCount(1)
    })

    test('Edited task is read back in work detail and in task list', async ({page}) => {
        const name = uniqueText('Read back task')

        await page.goto('/en/work/task/list')
        const taskRow = page.locator('tr[data-delete-row^="task-"]').first()
        const detailHref = await taskRow.locator('a[href*="/work/detail/"]').first().getAttribute('href') as string
        const editHref = await taskRow.locator('a[href*="/task/edit/"]').getAttribute('href') as string
        await page.goto(editHref)

        await page.locator('#task_name').clear()
        await page.fill('#task_name', name)
        await page.click('#task-button-action')
        await expect(page.locator('.alert-success')).toBeVisible()

        await page.goto(detailHref + '?tab=task')
        await expect(page.locator('tr[data-delete-row^="task-"]', {hasText: name})).toHaveCount(1)

        await page.goto('/en/work/task/list')
        await expect(page.locator('tr[data-delete-row^="task-"]', {hasText: name})).toHaveCount(1)
    })
}
