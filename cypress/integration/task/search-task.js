describe('Search task test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Search task success', () => {
        cy.visit(Cypress.env('domain') + '/en/work/task/list')
        cy.wait(2000)

        cy
            .get('#simple_search_search')
            .type('test')

        cy
            .get('#simple-search-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('#clear-simple-search-button-action')
            .click()

        cy.wait(1000)
    })
})
