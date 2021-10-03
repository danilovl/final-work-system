import {taskData} from '../fixtures/task';

describe('Edit task test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Edit task success ajax', () => {
        cy.visit(Cypress.env('domain') + `/en/work/supervisor/list`)

        cy
            .get('.work-group-list')
            .first()
            .click()

        cy
            .get('.btn.btn-primary.btn-xs')
            .first()
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(Cypress.env('domain') + href)
            })

        cy
            .get('.btn.btn-warning.btn-xs')
            .first()
            .click()

        cy
            .get(taskData.name.id)
            .clear()
            .type(taskData.name.text)

        cy
            .get(taskData.deadline.id)
            .clear()
            .type(taskData.deadline.text)
            .click()

        cy.window().then((win) => {
            win
                .tinymce
                .activeEditor
                .setContent(`<strong>${taskData.description.text}</strong>`)
        })

        cy
            .get('#task-button-action')
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })

    it('Edit success task', () => {
        cy.visit(Cypress.env('domain') + `/en/work/supervisor/list`)

        cy
            .get('.work-group-list')
            .first()
            .click()

        cy
            .get('.btn.btn-primary.btn-xs')
            .first()
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(Cypress.env('domain') + href)
            })

        cy
            .get('.btn.btn-warning.btn-xs')
            .first()
            .then(function (item) {
                cy.visit(item.prop('href'))
            })

        cy
            .get(taskData.name.id)
            .clear()
            .type(taskData.name.text)

        cy
            .get(taskData.deadline.id)
            .clear()
            .type(taskData.deadline.text)

        cy.window().then((win) => {
            win
                .tinymce
                .activeEditor
                .setContent(`<strong>${taskData.description.text}</strong>`)
        })

        cy
            .get('#task-button-action')
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
