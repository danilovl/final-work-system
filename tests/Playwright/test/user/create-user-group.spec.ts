import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'
import {userGroupData} from '@playwright-test/fixtures/user/user-group'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {})

    test('Create user group success', async ({page}) => {
        await page.goto('/en/user/group/create')

        await page.locator(userGroupData.name.id).clear()
        await page.locator(userGroupData.name.id).fill(userGroupData.name.text.toString())

        await page.locator('#group-button-action').click()
        await page.waitForTimeout(1000)

        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
