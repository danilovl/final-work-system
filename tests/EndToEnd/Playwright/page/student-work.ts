import {Browser, expect} from '@playwright/test'
import LoginPage from '@playwright-test/page/login-page'
import loginData from '@playwright-test/test-data/login'

export type StudentWork = {
    workPath: string
    conversationPath: string
}

export async function createStudentWork(browser: Browser, baseUrl: string): Promise<StudentWork> {
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

    const workPath = await workRow.locator('a[href*="/work/detail/"]:not(.btn)').getAttribute('href') as string
    const workHash = workPath.split('/').pop() as string

    await page.goto(`/en/work/${workHash}/task/create`)
    await page.fill('#task_name', 'Coverage task')
    await page.locator('#task_deadline').clear()
    await page.fill('#task_deadline', '2030-01-01')
    await page.click('#task-button-action')
    await expect(page.locator('.alert-success')).toBeVisible()

    await page.goto(workPath)
    const conversationForm = page
        .locator('form[action*="/conversation/create/work/"]')
        .first()
    const conversationAction = await conversationForm.getAttribute('action') as string
    const csrfToken = await conversationForm
        .locator('input[name="action_form[_token]"]')
        .getAttribute('value') as string
    const conversationResponse = await page.request.post(conversationAction, {
        form: {'action_form[_token]': csrfToken},
        headers: {Origin: new URL(baseUrl).origin}
    })
    const conversationPath = new URL(conversationResponse.url()).pathname

    expect(conversationPath).toContain('/conversation/detail/')

    await context.close()

    return {workPath, conversationPath}
}
