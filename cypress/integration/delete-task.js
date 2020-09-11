describe('Delete task test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Edit task success ajax', () => {
        cy.visit(Cypress.env('domain') + `/en/work/task/list`)

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