describe('Change status task test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Change status task success ajax', () => {
        cy.visit(Cypress.env('domain') + '/en/work/task/list')

        cy
            .get('.switchery')
            .each((element) => {
                cy
                    .wrap(element)
                    .click()

                cy.wait(1000)

                cy
                    .get('.alert-success')
                    .should('be.visible')
            });
    })
})
