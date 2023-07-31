describe('Create event calendar test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create event calendar success', () => {
        cy.visit(Cypress.env('domain') + '/en/event/calendar/manage')
        cy.wait(2000)

        cy
            .get('.fc-future')
            .first()
            .click()

        cy
            .get('#select2-event_address-container')
            .click()

        cy
            .get('li[id^=select2-event_address-result]')
            .first()
            .click()

        cy
            .get('#select2-event_participant-container')
            .click()

        cy
            .get('li[id^=select2-event_participant-result]')
            .first()
            .click()

        cy
            .get('#event_create')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
