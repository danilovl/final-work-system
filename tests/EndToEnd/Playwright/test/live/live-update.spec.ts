import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import LoginPage from '@playwright-test/page/login-page'
import {createStudentWork} from '@playwright-test/page/student-work'
import loginData from '@playwright-test/test-data/login'
import {setEditorContent} from '@playwright-test/page/tinymce'

const LIVE_TIMEOUT = 15000

export default function createTests() {
    test.beforeEach(async ({loginStudent}) => {
    })

    test('Student receives new message and widget counters without reload', async ({page, browser}) => {
        const baseUrl = test.info().project.use.baseURL as string
        const {conversationPath} = await createStudentWork(browser, baseUrl)

        await page.goto(conversationPath)
        await page.waitForLoadState('load')

        const chat = page.locator('#chat')
        const conversationBadge = page.locator('#top-nav-unread-conversation-messages .badge')
        const systemEventBadge = page.locator('#top-nav-unread-system-events .badge')
        const conversationCountBefore = Number((await conversationBadge.innerText()).trim())
        const systemEventCountBefore = Number((await systemEventBadge.innerText()).trim())
        const navigations: string[] = []
        page.on('framenavigated', frame => {
            if (frame === page.mainFrame()) {
                navigations.push(frame.url())
            }
        })

        const supervisorContext = await browser.newContext({baseURL: baseUrl})
        const supervisorPage = await supervisorContext.newPage()
        const loginPage = new LoginPage(supervisorPage)
        await loginPage.login(loginData.supervisor.username, loginData.supervisor.password)

        const text = 'Live message ' + Math.random().toString(36).substring(2, 10)
        await supervisorPage.goto(conversationPath)
        await setEditorContent(supervisorPage, text)
        await supervisorPage.locator('.chat-message-form input[type="submit"]').click()
        await expect(supervisorPage.locator('#chat')).toContainText(text)
        await supervisorContext.close()

        await expect(chat).toContainText(text, {timeout: LIVE_TIMEOUT})
        await expect(conversationBadge).toHaveText(String(conversationCountBefore + 1), {timeout: LIVE_TIMEOUT})
        await expect(page.locator('#top-nav-unread-conversation-messages .msg_list')).toContainText(text, {timeout: LIVE_TIMEOUT})
        await expect.poll(async () => Number((await systemEventBadge.innerText()).trim()), {timeout: LIVE_TIMEOUT})
            .toBeGreaterThan(systemEventCountBefore)

        expect(navigations).toEqual([])
    })
}
