import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import loginData from '@playwright-test/test-data/login'

export default function createTests() {
    test.beforeEach(async ({loginStudent}) => {
    })

    test('Student profile page is readable', async ({page}) => {
        const response = await page.goto('/en/user/profile')

        expect(response).not.toBeNull()
        expect(response!.status()).toBe(200)
        await expect(page.locator('body')).toContainText(loginData.student.username)
    })

    test('Student user detail API returns own data', async ({page}) => {
        await page.goto('/en/user/profile')

        const response = await page.request.get('/api/key/users/detail')
        expect(response.status()).toBe(200)

        const user = await response.json()
        expect(user.username).toBe(loginData.student.username)
    })

    test('Student work list shows own works only once', async ({page}) => {
        await page.goto('/en/work/author/list')

        const ids = await page
            .locator('tr[data-delete-row^="work-"]')
            .evaluateAll(elements => elements.map(element => element.getAttribute('data-delete-row') ?? ''))

        expect(new Set(ids).size).toBe(ids.length)
    })
}
