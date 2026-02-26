beforeEach(() => {
    cy.intercept({url: '*', middleware: true}, (req) => {
        req.headers['X-E2E-TEST'] = '1'
    })
})