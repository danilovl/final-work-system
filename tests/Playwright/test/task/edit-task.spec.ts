import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {taskData} from '@playwright-test//fixtures/task/task'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Edit task success ajax', async ({page}) => {
        await page.goto('/en/work/supervisor/list')

        await page.locator('.work-group-list').first().click()
        const groupHref = await page.locator('.btn.btn-primary.btn-xs').first().getAttribute('href') as string
        await page.goto(groupHref)

        await page.locator('.btn.btn-warning.btn-xs').first().click()

        await page.locator(taskData.name.id).clear()
        await page.fill(taskData.name.id, taskData.name.text)

        await page.locator(taskData.deadline.id).clear()
        await page.fill(taskData.deadline.id, taskData.deadline.text)
        await page.click(taskData.deadline.id)

        await page.evaluate((content) => {
            (window as any).tinymce.activeEditor.setContent(`<strong>${content}</strong>`)
        }, taskData.description.text)

        await page.click('#task-button-action')
        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })

    test('Edit success task', async ({page}) => {
        await page.goto('/en/work/supervisor/list')

        await page.locator('.work-group-list').first().click()
        const groupHref = await page.locator('.btn.btn-primary.btn-xs').first().getAttribute('href') as string
        await page.goto(groupHref)

        const editHref = await page.locator('.btn.btn-warning.btn-xs').first().getAttribute('href') as string
        await page.goto(editHref)

        await page.locator(taskData.name.id).clear()
        await page.fill(taskData.name.id, taskData.name.text)

        await page.locator(taskData.deadline.id).clear()
        await page.fill(taskData.deadline.id, taskData.deadline.text)
        await page.click(taskData.deadline.id)

        await page.evaluate((content) => {
            (window as any).tinymce.activeEditor.setContent(`<strong>${content}</strong>`)
        }, taskData.description.text)

        await page.click('#task-button-action')
        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
