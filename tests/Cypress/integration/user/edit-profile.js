import {contactInformationData, personalInformationData, messageData, tabData} from '../../fixtures/user/user-profile';

describe('Edit user profile test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Edit user profile success', () => {
        cy.visit(Cypress.env('domain') + '/en/user/profile/edit')

        for (let prop in contactInformationData) {
            cy
                .get(contactInformationData[prop].id)
                .clear()
                .type(contactInformationData[prop].text)
        }

        cy
            .get(tabData.tabPersonal.id)
            .click()

        for (let prop in personalInformationData) {
            cy
                .get(personalInformationData[prop].id)
                .clear()
                .type(personalInformationData[prop].text)
        }

        cy
            .get(tabData.tabMessage.id)
            .click()

        cy.window().then((win) => {
            for (let prop in messageData) {
                win
                    .tinymce
                    .get()
                    .find(editor => editor.id === messageData[prop].id)
                    .setContent(`<strong>${messageData[prop].text}</strong>`)

            }
        })

        cy
            .get('#user-edit-profile-button-action')
            .click()

        cy.wait(1000)

        cy
            .get('.alert-success')
            .should('be.visible')
    })
})
