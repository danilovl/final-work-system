describe('Change status task test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Change status task success ajax', () => {
        cy.visit(`${Cypress.env('domain')}/en/work/task/list`)

        cy.get('.switchery').then(switches => {
            const count = Math.floor(switches.length / 2)

            for (let i = 1; i < count; i++) {
                cy.get('.switchery').eq(i).click()
                cy.get('.alert-success').should('be.visible')
                cy.wait(2000)
            }
        })
    })
})
