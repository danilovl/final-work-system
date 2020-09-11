import {workData} from '../fixtures/work';

describe('Edit work test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Edit work success ajax', () => {
        cy.visit(Cypress.env('domain') + `/en/work/detail/${Cypress.env('workData').hashId}`)

        cy
            .get('#toggle-admin-panel')
            .click()

        cy
            .get('#work-edit')
            .click()

        for (let prop in workData) {
            cy
                .get(workData[prop].id)
                .clear()
                .type(workData[prop].text)
        }

        cy
            .get('#work-button-action')
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })

    it('Edit work success', () => {
        cy.visit(Cypress.env('domain') + `/en/work/edit/${Cypress.env('workData').hashId}`)

        for (let prop in workData) {
            cy
                .get(workData[prop].id)
                .clear()
                .type(workData[prop].text)
        }

        cy
            .get('#work-button-action')
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})