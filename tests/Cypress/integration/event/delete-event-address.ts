describe('Delete event address test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Delete event address success ajax', () => {
        cy.visit(`${Cypress.env('domain')}/en/event/address/list`)

        cy
            .get('.btn.btn-danger.btn-xs')
            .first()
            .click()

        cy
            .get('.btn.btn-danger.delete-element')
            .first()
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
