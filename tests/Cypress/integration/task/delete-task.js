describe('Delete task test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Delete task success ajax', () => {
        cy.visit(Cypress.env('domain') + '/en/work/task/list')

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
