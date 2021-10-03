import {successUserData} from '../fixtures/user';

delete successUserData.email;
delete successUserData.username;

describe('Edit work user test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Edit work user success ajax', () => {
        cy.visit(Cypress.env('domain') + `/en/work/supervisor/list`)

        cy
            .get('.work-group-list')
            .first()
            .click()

        cy
            .get('.btn.btn-primary.btn-xs')
            .first()
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(Cypress.env('domain') + href)
            })

        cy
            .get('#toggle-admin-panel')
            .click()

        cy
            .get('#work-author-edit')
            .click()

        for (let prop in successUserData) {
            cy
                .get(successUserData[prop].id)
                .clear()
                .type(successUserData[prop].text)
        }

        cy
            .get('#user-button-action')
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })

    it('Edit work user success', () => {
        cy.visit(Cypress.env('domain') + `/en/work/supervisor/list`)

        cy
            .get('.work-group-list')
            .first()
            .click()

        cy
            .get('.btn.btn-primary.btn-xs')
            .first()
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(Cypress.env('domain') + href)
            })

        cy
            .get('#work-open-admin-panel')
            .click()

        cy
            .get('#work-author-edit')
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(Cypress.env('domain') + href)
            })

        for (let prop in successUserData) {
            cy
                .get(successUserData[prop].id)
                .clear()
                .type(successUserData[prop].text)
        }

        cy
            .get('#user-button-action')
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
