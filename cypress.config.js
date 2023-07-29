module.exports = {
    e2e: {
        baseUrl: 'http://nginx:80',
        supportFile: 'tests/Cypress/support/index.js',
        specPattern: [
            'tests/Cypress/integration/common/check-url.js',
            'tests/Cypress/integration/user/edit-profile.js',
            'tests/Cypress/integration/user/create-user.js',
            'tests/Cypress/integration/user/create-user-group.js',
            'tests/Cypress/integration/work/create-work.js',
            'tests/Cypress/integration/task/create-task.js',
            'tests/Cypress/integration/conversation/create-conversation.js',
            'tests/Cypress/integration/conversation/search-conversation.js',
            'tests/Cypress/integration/task/edit-task.js',
            'tests/Cypress/integration/task/change-status-task.js',
            'tests/Cypress/integration/task/search-task.js',
            'tests/Cypress/integration/work/edit-work.js',
            'tests/Cypress/integration/work/edit-work-author.js',
            'tests/Cypress/integration/conversation/delete-conversation.js',
            'tests/Cypress/integration/task/delete-task.js',
            'tests/Cypress/integration/work/delete-work.js',
            'tests/Cypress/integration/user/change-password.js',
            'tests/Cypress/integration/security/login.js'
        ]
    },
    env: {
        domain: 'http://nginx:80',
        loginData: {
            failed: {
                username: 'failed',
                password: 'failedfailed'
            },
            student: {
                username: 'student',
                password: 'studentstudent'
            },
            supervisor: {
                username: 'supervisor',
                password: 'supervisorsupervisor'
            }
        }
    }
};
