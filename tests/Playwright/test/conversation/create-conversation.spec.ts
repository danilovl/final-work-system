import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {conversationData} from '@playwright-test/fixtures/conversation/conversation'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {})

    test('Create conversation success', async ({page}) => {
        await page.goto('/en/conversation/create')
        await page.waitForTimeout(2000)

        await page.fill(
            conversationData.messageName.id,
            conversationData.messageName.text
        )

        await page.click('.select2-search__field')
        await page.fill(
            'input.select2-search__field',
            conversationData.composeMessage.text
        )
        await page.keyboard.press('Enter')
        await page.keyboard.press('Enter')

        await page.click(conversationData.bodyMessage.id)

        await page.evaluate((content) => {
            (window as any).tinymce.activeEditor.setContent(`<strong>${content}</strong>`)
        }, conversationData.bodyMessage.text)

        await page.click('#btn-conversation-create')

        await expect(page).toHaveURL(/.*\/conversation\/list/)
    })
}
