import {addressData} from '@cypress-test/fixtures/event/address'

describe('Create event address test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create event address success', () => {
        cy.visit(`${Cypress.env('domain')}/en/event/address/create`)

        cy
            .get('form')
            .should('be.visible')

        for (let prop in addressData) {
            if (addressData.hasOwnProperty(prop)) {
                const propKey = prop as keyof typeof addressData
                const data = addressData[propKey]

                if (data.tinymce) {
                    cy.window().then((win) => {
                        (win as any).tinymce.activeEditor.setContent(`<strong>${data.text}</strong>`)
                    })
                } else {
                    cy.get(data.id).type(data.text)
                }
            }
        }

        cy
            .get('#create-event-address-button-action')
            .click()

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
