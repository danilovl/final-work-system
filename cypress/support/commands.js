Cypress.Commands.add(`login`, (username, password) => {
    cy.clearCookies()
    cy.visit(Cypress.env('domain'))

    cy
        .get('#username')
        .type(username)

    cy
        .get('#password')
        .type(password)

    cy
        .get('#_submit')
        .click()
})

Cypress.Commands.add(`loginSupervisor`, () => {
    cy.login(
        Cypress.env('loginData').supervisor.username,
        Cypress.env('loginData').supervisor.password,
    )
})

Cypress.Commands.add(`loginStudent`, () => {
    cy.login(
        Cypress.env('loginData').student.username,
        Cypress.env('loginData').student.password,
    )
})

