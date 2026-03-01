describe('Change user profile image test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Change user profile image success', () => {
        cy.visit(`${Cypress.env('domain')}/en/user/profile/image`)

        cy
            .get('#user_profile_image_uploadMedia')
            .selectFile('tests/EndToEnd/Cypress/fixtures/user/image/profile.jpg')

        cy
            .get('#upload-profile-image-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
