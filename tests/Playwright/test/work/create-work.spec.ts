import {test} from '@playwright-test/fixtures/command';
import {workData} from '@playwright-test/fixtures/work/work';

export default function createTests() {
    test.beforeEach(async ({loginSupervisor}) => {});

    test('Create work success', async ({page}) => {
        await page.goto('/en/work/create');

        for (const prop in workData) {
            await page.fill(
                workData[prop as keyof typeof workData].id,
                workData[prop as keyof typeof workData].text.toString()
            );
        }

        await page.click('#work-button-action');
    });
}