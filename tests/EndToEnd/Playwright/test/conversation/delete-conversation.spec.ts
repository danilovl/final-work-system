import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Delete conversation success', async ({page}) => {
        await page.goto('/en/conversation/list')
        await page.waitForLoadState('networkidle')

        const deleteButton = page.locator('.btn.btn-danger').first()
        const dataTarget = await deleteButton.getAttribute('data-bs-target')

        await deleteButton.click()
        await page.locator(`${dataTarget} .delete-element`).click()


        await expect(page.locator('.alert-success')).toBeVisible()
    })
}
