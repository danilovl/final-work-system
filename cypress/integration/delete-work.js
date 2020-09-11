import {workData} from '../fixtures/work';

describe('Delete work test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Delete work success ajax', () => {
        cy.visit(Cypress.env('domain') + `/en/work/supervisor/list`)

        cy
            .get('#toggle-search-work-form')
            .click()

        cy
            .get('#work_search_shortcut')
            .type(workData.shortcut.text)

        cy
            .get('#search-work-button')
            .click()

        cy
            .get('.work-group-list')
            .first()
            .click()

        cy
            .get('.btn.btn-danger.btn-xs')
            .first()
            .click()

        cy
            .get('.btn.btn-danger.delete-element')
            .first()
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})