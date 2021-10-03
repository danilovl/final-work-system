import {successUserData} from '../fixtures/user';

describe('Create user test', () => {
    const failedUserData = successUserData;

    failedUserData.email = {
        'id': '#user_email',
        'text': Math.random().toString(36).substr(2, 10) + '@gmail.com'
    }

    failedUserData.username = {
        'id': '#user_username',
        'text': Math.random().toString(36).substr(2, 10)
    }

    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create user success', () => {
        cy.visit(Cypress.env('domain') + '/en/user/create')

        for (let prop in successUserData) {
            cy
                .get(successUserData[prop].id)
                .type(successUserData[prop].text)
        }

        cy
            .get('#user-button-action')
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })

    it('Create user failed', () => {
        cy.visit(Cypress.env('domain') + '/en/user/create')

        for (let prop in failedUserData) {
            cy
                .get(failedUserData[prop].id)
                .type(failedUserData[prop].text)
        }

        cy
            .get('#user-button-action')
            .click()

        cy
            .get('.alert-danger')
            .should('be.visible')
    })
})
