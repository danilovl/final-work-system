import {test} from '@playwright-test/fixtures/command'
import {workData} from '@playwright-test/fixtures/work/work'

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {
    })

    test('Create work success', async ({page}) => {
        await page.goto('/en/work/create')

        for (const prop in workData) {
            const propKey = prop as keyof typeof workData

            await page.fill(
                workData[propKey].id,
                workData[propKey].text.toString()
            )
        }

        await page.click('#work-button-action')
    })
}