const passwordData = {
    old: {
        id: '#profile_change_password_current_password',
        text: Cypress.env('loginData').supervisor.password
    },
    new: {
        id: '#profile_change_password_plainPassword_first',
        text: Cypress.env('loginData').supervisor.password
    },
    repeatNew: {
        id: '#profile_change_password_plainPassword_second',
        text: Cypress.env('loginData').supervisor.password
    }
}

export {passwordData}
