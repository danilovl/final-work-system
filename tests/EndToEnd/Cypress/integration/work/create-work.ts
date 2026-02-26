import {workData} from '@cypress-test/fixtures/work/work'

describe('Create work test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create work success', () => {
        cy.visit(Cypress.env('domain') + '/en/work/create')

        for (let prop in workData) {
            const propKey = prop as keyof typeof workData

            cy
                .get(workData[propKey].id)
                .type(workData[propKey].text.toString())
        }

        cy
            .get('#work-button-action')
            .click()
    })
})
