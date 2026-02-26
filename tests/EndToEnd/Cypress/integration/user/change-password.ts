import {passwordData} from '@cypress-test/fixtures/user/user-change-password'

describe('Change user password test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Change user password success', () => {
        cy.visit(`${Cypress.env('domain')}/en/user/profile/change-password`)

        for (const prop in passwordData) {
            const propKey = prop as keyof typeof passwordData

            cy
                .get(passwordData[propKey].id)
                .type(passwordData[propKey].text)
        }

        cy
            .get('#user-change-password-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
