describe('Delete conversation test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Delete conversation success', () => {
        cy.visit(Cypress.env('domain') + '/en/conversation/list')
        cy.wait(2000)

        cy
            .get('.btn.btn-danger')
            .first()
            .click()
            .invoke('attr', 'data-target')
            .then((dataTarget) => {
                cy
                    .get(dataTarget + ' .delete-element')
                    .should('be.visible')
                    .click()
            });

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
