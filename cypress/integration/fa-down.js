describe('Fa down test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Fa down test', () => {
        cy.visit(Cypress.env('domain') + '/en/work/supervisor/list')

        cy
            .get('.fa-chevron-down')
            .each(($el, index, list) => {
                cy
                    .wrap($el)
                    .click()
            })
    })
})