import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Theme choice is kept between pages and applied before render', async ({page}) => {
        await page.addInitScript(() => {
            document.addEventListener('DOMContentLoaded', () => {
                (window as any).themeAtDomReady = document.documentElement.getAttribute('data-bs-theme')
                ;(window as any).sidebarAtDomReady = document.documentElement.getAttribute('data-fws-sidebar')
            })
        })

        const root = page.locator('html')

        await page.goto('/en')
        await page.click('.theme-toggle')
        await page.click('[data-theme-area-param="content"][data-theme-value-param="dark"]')
        await expect(root).toHaveAttribute('data-bs-theme', 'dark')

        await page.click('.theme-toggle')
        await page.click('[data-theme-area-param="sidebar"][data-theme-value-param="light"]')
        await expect(root).toHaveAttribute('data-fws-sidebar', 'light')

        await page.goto('/en/conversation/list')
        await expect(root).toHaveAttribute('data-bs-theme', 'dark')
        await expect(root).toHaveAttribute('data-fws-sidebar', 'light')

        const themeAtDomReady = await page.evaluate(() => (window as any).themeAtDomReady)
        const sidebarAtDomReady = await page.evaluate(() => (window as any).sidebarAtDomReady)

        expect(themeAtDomReady).toBe('dark')
        expect(sidebarAtDomReady).toBe('light')

        await page.click('.theme-toggle')
        await expect(page.locator('[data-theme-area-param="content"][data-theme-value-param="dark"]')).toHaveClass(/active/)
        await page.click('[data-theme-area-param="sidebar"][data-theme-value-param="auto"]')
        await expect(root).not.toHaveAttribute('data-fws-sidebar', /.+/)

        await page.click('.theme-toggle')
        await page.click('[data-theme-area-param="content"][data-theme-value-param="light"]')
        await page.reload()
        await expect(root).toHaveAttribute('data-bs-theme', 'light')
    })

    test('Sidebar menu collapses and expands', async ({page}) => {
        const body = page.locator('body')
        const sidebar = page.locator('.left_col').first()

        await page.goto('/en')
        await expect(body).toHaveClass(/nav-md/)

        await page.click('#menu_toggle')
        await expect(body).toHaveClass(/nav-sm/)

        const collapsedBox = await sidebar.boundingBox()
        expect(collapsedBox?.width ?? 0).toBeLessThan(100)

        await page.click('#menu_toggle')
        await expect(body).toHaveClass(/nav-md/)

        const expandedBox = await sidebar.boundingBox()
        expect(expandedBox?.width ?? 0).toBeGreaterThan(200)
    })
}
