import {conversationData} from '@cypress-test/fixtures/conversation/conversation'

describe('Create conversation test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create conversation success', () => {
        cy.visit(`${Cypress.env('domain')}/en/conversation/create`)
        cy.wait(2000)

        cy
            .get(conversationData.messageName.id)
            .type(conversationData.messageName.text)

        cy
            .get('.select2-search__field')
            .click()

        cy
            .get('input[class="select2-search__field"]')
            .type(conversationData.composeMessage.text + '{enter}')

        cy
            .get('input[class="select2-search__field"]')
            .type('{enter}')

        cy
            .get(conversationData.bodyMessage.id)
            .click()

        cy.window().then((win) => {
            (win as any).tinymce.activeEditor.setContent(
                `<strong>${conversationData.bodyMessage.text}</strong>`
            )
        })

        cy
            .get('#btn-conversation-create')
            .click()

        cy.url().should('include', '/conversation/list')
    })
})
