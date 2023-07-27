import {passwordData} from '../../fixtures/user/user-change-password';

describe('Change user password test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Change user password success', () => {
        cy.visit(Cypress.env('domain') + '/en/user/profile/change-password')

        for (let prop in passwordData) {
            cy
                .get(passwordData[prop].id)
                .type(passwordData[prop].text)
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
