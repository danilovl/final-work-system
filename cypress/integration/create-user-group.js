import {userGroupData} from '../fixtures/user-group';

describe('Create user group test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create user group success', () => {
        cy.visit(Cypress.env('domain') + '/en/user/group/create')

        cy
            .get(userGroupData.name.id)
            .clear()
            .type(userGroupData.name.text)

        cy
            .get('#group-button-action')
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})