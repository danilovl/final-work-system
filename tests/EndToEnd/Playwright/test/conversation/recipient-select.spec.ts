import {expect} from '@playwright/test'
import {test} from '@playwright-test/fixtures/command'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Conversation recipients select whole category and single user', async ({page}) => {
        await page.goto('/en/conversation/create')

        const select = page.locator('#conversation_compose_message_conversation')
        const selectedCount = () => select.evaluate((element: HTMLSelectElement) => element.selectedOptions.length)

        await page.locator('#conversation_compose_message_conversation + .select2 .select2-selection').click()

        const group = page.locator('.select2-results__group[role="treeitem"]').first()
        await expect(group).toBeVisible()

        const groupSize = await select.locator('optgroup').first().locator('option').count()
        expect(groupSize).toBeGreaterThan(0)

        await group.click()
        await expect(group).toHaveClass(/select2-results__group--selected/)

        const selectedAfterGroupSelect = await selectedCount()
        expect(selectedAfterGroupSelect).toBe(groupSize)

        const groupOptions = page.locator('.select2-results__option--selectable')
        await expect(groupOptions.first()).toHaveClass(/select2-results__option--selected/)

        await group.click()
        await expect(group).not.toHaveClass(/select2-results__group--selected/)
        await expect(groupOptions.first()).not.toHaveClass(/select2-results__option--selected/)

        const selectedAfterGroupUnselect = await selectedCount()
        expect(selectedAfterGroupUnselect).toBe(0)

        const option = groupOptions.nth(1)
        await option.click()
        await expect(option).toHaveClass(/select2-results__option--selected/)

        const selectedAfterOptionSelect = await selectedCount()
        expect(selectedAfterOptionSelect).toBe(1)

        await option.click()
        await expect(option).not.toHaveClass(/select2-results__option--selected/)

        const selectedAfterOptionUnselect = await selectedCount()
        expect(selectedAfterOptionUnselect).toBe(0)
    })
}
