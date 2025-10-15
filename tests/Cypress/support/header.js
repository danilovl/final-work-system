beforeEach(() => {
    cy.intercept('*', req => {
        req.headers['X-E2E-TEST'] = '1';
    })
})
