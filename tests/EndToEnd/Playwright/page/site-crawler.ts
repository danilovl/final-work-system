import type {Page} from '@playwright/test'

type VisitedPage = {
    url: string
    from: string
    status: number
    errors: string[]
}

type QueuedPage = {
    url: string
    from: string
}

const SKIPPED_URL_PARTS = [
    '/logout',
    'delete',
    'ajax',
    'download',
    '/change',
    'notify',
    'viewed',
    'read-all',
    'switch',
    '_switch_user',
    '/away/to',
    '/_profiler',
    '/_wdt',
    '/api/'
]

const ADMIN_QUERY_KEYS = ['crudControllerFqcn', 'crudAction']

export default class SiteCrawler {
    private readonly origin: string

    constructor(public readonly page: Page, baseUrl: string, private readonly locale = 'en') {
        this.origin = new URL(baseUrl).origin
    }

    async crawl(startPaths: string[], maxPages = 150): Promise<VisitedPage[]> {
        const queue: QueuedPage[] = startPaths.map(path => ({url: new URL(path, this.origin).toString(), from: ''}))
        const seenKeys = new Set<string>()
        const visited: VisitedPage[] = []

        while (queue.length > 0 && visited.length < maxPages) {
            const {url, from} = queue.shift() as QueuedPage
            const key = this.pageKey(url)

            if (seenKeys.has(key)) {
                continue
            }

            seenKeys.add(key)

            const visitedPage = await this.visit(url, from)
            visited.push(visitedPage)

            if (visitedPage.status !== 200) {
                continue
            }

            const links = await this.collectLinks(url)
            const queuedLinks = links.map(link => ({url: link, from: url}))
            queue.push(...queuedLinks)
        }

        return visited
    }

    async visit(url: string, from = ''): Promise<VisitedPage> {
        const errors: string[] = []
        const onPageError = (error: Error): void => {
            errors.push(error.message)
        }

        this.page.on('pageerror', onPageError)
        const response = await this.page.goto(url)
        await this.page.waitForLoadState('load')
        this.page.off('pageerror', onPageError)

        const status = response === null ? 0 : response.status()

        return {url, from, status, errors}
    }

    private async collectLinks(currentUrl: string): Promise<string[]> {
        const hrefs = await this.page
            .locator('a[href]')
            .evaluateAll(elements => elements.map(element => (element as HTMLAnchorElement).href))

        const isAdminPage = this.isAdminUrl(currentUrl)
        const crawlable = hrefs.filter(href => this.isCrawlable(href))

        if (!isAdminPage) {
            return crawlable
        }

        return crawlable.filter(href => this.isAdminUrl(href))
    }

    private isAdminUrl(href: string): boolean {
        const url = new URL(href)

        return url.pathname.startsWith(`/${this.locale}/admin/`)
    }

    private isCrawlable(href: string): boolean {
        if (!href.startsWith(this.origin)) {
            return false
        }

        const url = new URL(href)
        const localePrefix = `/${this.locale}`
        const isLocalePath = url.pathname === localePrefix || url.pathname.startsWith(localePrefix + '/')

        if (!isLocalePath) {
            return false
        }

        const lowerHref = href.toLowerCase()

        return !SKIPPED_URL_PARTS.some(part => lowerHref.includes(part.toLowerCase()))
    }

    private pageKey(href: string): string {
        const url = new URL(href)
        const segments = url.pathname
            .split('/')
            .filter(segment => segment !== '')
            .map(segment => this.isHashid(segment) ? ':id' : segment)

        const adminQuery = ADMIN_QUERY_KEYS
            .map(name => url.searchParams.get(name) ?? '')
            .join('|')

        return segments.join('/') + '?' + adminQuery
    }

    private isHashid(segment: string): boolean {
        const hasHashidLength = /^[a-zA-Z0-9]{10,}$/.test(segment)
        const hasUpperCaseOrDigit = /[A-Z0-9]/.test(segment)

        return hasHashidLength && hasUpperCaseOrDigit
    }
}
