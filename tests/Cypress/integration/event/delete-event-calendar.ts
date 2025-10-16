describe('Delete event calendar test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Delete event calendar success', () => {
        cy.visit(`${Cypress.env('domain')}/en/event/calendar/manage`)
        cy.wait(2000)

        cy
            .get('.fc-draggable')
            .first()
            .click()

        cy
            .get('#event-delete')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
