import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Sidebar menu opens and closes sub menu', async ({page}) => {
        await page.goto('/en')

        const menuItems = page.locator('#sidebar-menu li:has(> ul.child_menu)')
        const menuItemCount = await menuItems.count()

        expect(menuItemCount).toBeGreaterThan(0)

        for (let index = 0; index < menuItemCount; index++) {
            const menuItem = menuItems.nth(index)
            const subMenu = menuItem.locator('> ul.child_menu')
            const toggle = menuItem.locator('> a')

            await toggle.click()
            await expect(subMenu).toBeVisible()
            await expect(menuItem).toHaveClass(/active/)

            await toggle.click()
            await expect(subMenu).toBeHidden()
        }
    })
}
