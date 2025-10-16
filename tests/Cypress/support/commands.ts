export {}

interface LoginData {
    failed: {
        username: string
        password: string
    }
    student: {
        username: string
        password: string
    }
    supervisor: {
        username: string
        password: string
    }
}

declare global {
    namespace Cypress {
        interface Chainable {
            login(username: string, password: string): Chainable<void>
            loginSupervisor(): Chainable<void>
            loginStudent(): Chainable<void>
        }

        interface Cypress {
            env(key: 'loginData'): LoginData
            env(key: 'domain'): string
        }
    }
}

Cypress.Commands.add('login', (username: string, password: string) => {
    cy.clearCookies()
    cy.visit(Cypress.env('domain') + '/en/login')

    cy
        .get('#username')
        .type(username)

    cy
        .get('#password')
        .type(password)

    cy
        .get('#_submit')
        .click()
})

Cypress.Commands.add('loginSupervisor', () => {
    cy.login(
        Cypress.env('loginData').supervisor.username,
        Cypress.env('loginData').supervisor.password
    )
})

Cypress.Commands.add('loginStudent', () => {
    cy.login(
        Cypress.env('loginData').student.username,
        Cypress.env('loginData').student.password
    )
})