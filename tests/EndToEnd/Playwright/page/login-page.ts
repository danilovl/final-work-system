import type {Page} from '@playwright/test'

export default class LoginPage {
    constructor(public readonly page: Page) {
    }

    async login(username: string, password: string): Promise<void> {
        const context = this.page.context()
        const cookies = await context.cookies()
        const e2eCookies = cookies.filter(cookie => cookie.name === 'e2e_test')

        await context.clearCookies()
        await context.addCookies(e2eCookies)

        await this.page.goto('/en/login')
        await this.page.fill('#username', username)
        await this.page.fill('#password', password)
        await this.page.click('#_submit')
    }
}
