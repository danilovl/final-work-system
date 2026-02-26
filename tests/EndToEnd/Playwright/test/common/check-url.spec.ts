import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Check static URL success', async ({page}) => {
        const urls = [
            '/en/reset-password/request',
            '/en/reset-password/check-email',
            '/en/article/category/list',
            '/en/user/profile',
            '/en/user/profile/edit',
            '/en/user/profile/image',
            '/en/user/profile/change-password',
            '/en/work/create',
            '/en/work/category/create',
            '/en/work/category/list',
            '/en/work/task/list',
            '/en/conversation/list',
            '/en/conversation/create',
            '/en/document/create',
            '/en/document/list/owner',
            '/en/document/category/create',
            '/en/document/category/list',
            '/en/event/calendar/manage',
            '/en/event/address/list',
            '/en/event/address/create',
            '/en/event/schedule/create',
            '/en/event/schedule/list',
            '/en/user/create'
        ]

        for (const url of urls) {
            const response = await page.goto(url)

            expect(response).not.toBeNull()
            expect(response!.status()).toBe(200)
        }
    })
}
