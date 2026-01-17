import type {Page} from '@playwright/test'

export default class LoginPage {
    constructor(public readonly page: Page) {
    }

    async login(username: string, password: string): Promise<void> {
        await this.page.context().clearCookies()

        await this.page.goto('/en/login')
        await this.page.fill('#username', username)
        await this.page.fill('#password', password)
        await this.page.click('#_submit')
    }
}
