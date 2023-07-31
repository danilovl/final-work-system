describe('Delete user profile image test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Delete user profile image success', () => {
        cy.visit(Cypress.env('domain') + '/en/user/profile/image')

        cy
            .get('#delete-profile-image')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
