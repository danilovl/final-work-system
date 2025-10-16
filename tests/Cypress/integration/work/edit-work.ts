import {workData} from '@cypress-test/fixtures/work/work'

describe('Edit work test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Edit work success ajax', () => {
        cy.visit(Cypress.env('domain') + '/en/work/supervisor/list')

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
            .get('#work-edit')
            .click()

        for (let prop in workData) {
            const propKey = prop as keyof typeof workData

            cy
                .get(workData[propKey].id)
                .clear()
                .type(workData[propKey].text.toString())
        }

        cy
            .get('#work-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })

    it('Edit work success', () => {
        cy.visit(Cypress.env('domain') + '/en/work/supervisor/list')

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
            .get('#work-edit')
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(Cypress.env('domain') + href)
            })

        for (let prop in workData) {
            const propKey = prop as keyof typeof workData

            cy
                .get(workData[propKey].id)
                .clear()
                .type(workData[propKey].text.toString())
        }

        cy
            .get('#work-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
