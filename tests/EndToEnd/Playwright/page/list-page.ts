import type {Page} from '@playwright/test'

export default class ListPage {
    constructor(public readonly page: Page) {
    }

    async rowIds(url: string, attribute: string, prefix: string): Promise<string[]> {
        await this.page.goto(url)

        const values = await this.page
            .locator(`[${attribute}^="${prefix}"]`)
            .evaluateAll(
                (elements, name) => elements.map(element => element.getAttribute(name as string) ?? ''),
                attribute
            )

        return [...new Set(values)]
    }

    async allPagesRowIds(url: string, attribute: string, prefix: string, maxPages = 50): Promise<string[][]> {
        const pages: string[][] = []

        for (let pageNumber = 1; pageNumber <= maxPages; pageNumber++) {
            const separator = url.includes('?') ? '&' : '?'
            const ids = await this.rowIds(`${url}${separator}page=${pageNumber}`, attribute, prefix)
            const previous = pages.at(-1)
            const isRepeatedLastPage = previous !== undefined && previous.join() === ids.join()

            if (ids.length === 0 || isRepeatedLastPage) {
                break
            }

            pages.push(ids)
        }

        return pages
    }
}
