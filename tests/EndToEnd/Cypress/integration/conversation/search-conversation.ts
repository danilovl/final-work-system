describe('Search conversation test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Search conversation success', () => {
        cy.visit(`${Cypress.env('domain')}/en/conversation/list`)
        cy.wait(2000)

        cy.get('#simple_search_search').type('test')
        cy.get('#simple-search-button-action').click()

        cy.wait(1000)

        cy.get('#clear-simple-search-button-action').click()
        cy.wait(1000)
    })
})
