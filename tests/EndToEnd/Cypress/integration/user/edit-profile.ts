import {
    contactInformationData,
    personalInformationData,
    messageData,
    tabData
} from '@cypress-test/fixtures/user/user-profile'

describe('Edit user profile test', () => {
    beforeEach(() => {
        cy.loginSupervisor()
    })

    it('Edit user profile success', () => {
        cy.visit(`${Cypress.env('domain')}/en/user/profile/edit`)

        for (let prop in contactInformationData) {
            const propKey = prop as keyof typeof contactInformationData

            cy
                .get(contactInformationData[propKey].id)
                .clear()
                .type(contactInformationData[propKey].text.toString())
        }

        cy
            .get(tabData.tabPersonal.id)
            .click()

        for (let prop in personalInformationData) {
            const propKey = prop as keyof typeof personalInformationData

            cy
                .get(personalInformationData[propKey].id)
                .clear()
                .type(personalInformationData[propKey].text.toString())
        }

        cy
            .get(tabData.tabMessage.id)
            .click()

        cy.window().then((win) => {
            for (let prop in messageData) {
                const propKey = prop as keyof typeof messageData
                const editorId = messageData[propKey].id

                const editor = (win as any).tinymce.get(editorId)
                if (editor) {
                    const text = messageData[propKey].text.toString()

                    editor.setContent(`<strong>${text}</strong>`);
                }
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
