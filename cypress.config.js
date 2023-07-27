module.exports = {
    e2e: {
        baseUrl: 'http://nginx:80',
        supportFile: 'cypress/support/index.js',
        specPattern: [
            'cypress/integration/common/check-url.js',
            'cypress/integration/user/edit-profile.js',
            'cypress/integration/user/create-user.js',
            'cypress/integration/user/create-user-group.js',
            'cypress/integration/work/create-work.js',
            'cypress/integration/task/create-task.js',
            'cypress/integration/conversation/create-conversation.js',
            'cypress/integration/conversation/search-conversation.js',
            'cypress/integration/task/edit-task.js',
            'cypress/integration/task/change-status-task.js',
            'cypress/integration/task/search-task.js',
            'cypress/integration/work/edit-work.js',
            'cypress/integration/work/edit-work-author.js',
            'cypress/integration/conversation/delete-conversation.js',
            'cypress/integration/task/delete-task.js',
            'cypress/integration/work/delete-work.js',
            'cypress/integration/user/change-password.js',
            'cypress/integration/security/login.js'
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
