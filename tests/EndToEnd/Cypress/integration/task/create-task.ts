import {taskData} from '@cypress-test/fixtures/task/task'

describe('Create task test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create task success ajax', () => {
        cy.visit(`${Cypress.env('domain')}/en/work/supervisor/list`)

        cy
            .get('.work-group-list')
            .first()
            .click()

        cy
            .get('.btn.btn-primary.btn-xs')
            .first()
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(`${Cypress.env('domain')}${href}`)
            })

        cy
            .get('#task-create')
            .click()

        cy.wait(1000)

        cy
            .get(taskData.name.id)
            .type(taskData.name.text)

        cy
            .get(taskData.deadline.id)
            .type(taskData.deadline.text)
            .click()

        cy.window().then((win) => {
            (win as any)
                .tinymce
                .activeEditor
                .setContent(`<strong>${taskData.description.text}</strong>`)
        })

        cy
            .get('#task-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })

    it('Create success task', () => {
        cy.visit(`${Cypress.env('domain')}/en/work/supervisor/list`)

        cy
            .get('.work-group-list')
            .first()
            .click()

        cy
            .get('.btn.btn-primary.btn-xs')
            .first()
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(`${Cypress.env('domain')}${href}`)
            })

        cy
            .get('#task-create')
            .should('have.attr', 'href')
            .then((href) => {
                cy.visit(`${Cypress.env('domain')}${href}`)
            })

        cy.wait(1000)

        cy
            .get(taskData.name.id)
            .type(taskData.name.text)

        cy
            .get(taskData.deadline.id)
            .type(taskData.deadline.text)

        cy.window().then((win) => {
            (win as any)
                .tinymce
                .activeEditor
                .setContent(`<strong>${taskData.description.text}</strong>`)
        })

        cy
            .get('#task-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
