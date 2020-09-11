import {workData} from '../fixtures/work';

describe('Create work test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create work success', () => {
        cy.visit(Cypress.env('domain') + '/en/work/create')

        for (let prop in workData) {
            cy
                .get(workData[prop].id)
                .type(workData[prop].text)
        }

        cy
            .get('#work-button-action')
            .click()
    })
})