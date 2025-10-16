import { successUserData } from '@cypress-test/fixtures/user/user'

const { email, username, ...successData } = successUserData

describe('Edit work user test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Edit work user success ajax', () => {
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
            .get('#work-author-edit')
            .click()

        for (let prop in successData) {
            const propKey = prop as keyof typeof successData
            
            cy
                .get(successData[propKey].id)
                .clear()
                .type(successData[propKey].text.toString())
        }

        cy
            .get('#user-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })

    it('Edit work user success', () => {
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
            .get('#work-author-edit')
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(Cypress.env('domain') + href)
            })

        for (let prop in successData) {
            const propKey = prop as keyof typeof successData

            cy
                .get(successData[propKey].id)
                .clear()
                .type(successData[propKey].text.toString())
        }

        cy
            .get('#user-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
