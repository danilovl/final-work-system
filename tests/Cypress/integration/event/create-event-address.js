import {addressData} from '../../fixtures/event/address';

describe('Create event address test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Create event address success', () => {
        cy.visit(Cypress.env('domain') + '/en/event/address/create')
        cy.wait(2000)

        for (let prop in addressData) {
            if (addressData[prop].tinymce) {
                cy.window().then((win) => {
                    win
                        .tinymce
                        .activeEditor
                        .setContent(`<strong>${addressData[prop].text}</strong>`)
                })
            } else {
                cy
                    .get(addressData[prop].id)
                    .type(addressData[prop].text)
            }
        }

        cy
            .get('#create-event-address-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
