describe('Login test', () => {
    beforeEach(() => {
        cy.clearCookies()
        cy.visit(Cypress.env('domain'))
    })

    it('Login failed', () => {
        cy.login(
            Cypress.env('loginData').failed.username,
            Cypress.env('loginData').failed.password,
        )

        cy
            .get('#error-login-message')
            .should('be.visible')
    })

    it('Login succeed', () => {
        cy.login(
            Cypress.env('loginData').supervisor.username,
            Cypress.env('loginData').supervisor.password
        )

        cy
            .get('#sidebar-menu')
            .should('be.visible')
    })
})