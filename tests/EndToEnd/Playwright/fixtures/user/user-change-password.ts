import loginData from '../../test-data/login'

const passwordData = {
    old: {
        id: '#profile_change_password_current_password',
        text: loginData.supervisor.password
    },
    new: {
        id: '#profile_change_password_plainPassword_first',
        text: loginData.supervisor.password
    },
    repeatNew: {
        id: '#profile_change_password_plainPassword_second',
        text: loginData.supervisor.password
    }
}

export {passwordData}
