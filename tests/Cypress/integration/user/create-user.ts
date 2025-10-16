import { successUserData } from '@cypress-test/fixtures/user/user'

describe('Create user test', () => {
    let failedUserData = { ...successUserData }

    failedUserData.email = {
        'id': '#user_email',
        'text': Math.random().toString(36).substring(2, 10)
    }

    failedUserData.username = {
        'id': '#user_username',
        'text': Math.random().toString(36).substring(2, 10)
    }

    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create user success', () => {
        cy.visit(`${Cypress.env('domain')}/en/user/create`)

        for (let prop in successUserData) {
            const propKey = prop as keyof typeof successUserData

            cy
                .get(successUserData[propKey].id)
                .type(successUserData[propKey].text.toString())
        }

        cy
            .get('#user-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })

    it('Create user failed', () => {
        cy.visit(`${Cypress.env('domain')}/en/user/create`)

        for (let prop in failedUserData) {
            const propKey = prop as keyof typeof failedUserData

            cy
                .get(failedUserData[propKey].id)
                .type(failedUserData[propKey].text.toString())
        }

        cy
            .get('#user-button-action')
            .click()

        cy
            .get('.alert-danger')
            .should('be.visible')
    })
})
