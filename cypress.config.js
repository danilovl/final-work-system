module.exports = {
    e2e: {
        baseUrl: "http://nginx:80",
        supportFile: 'cypress/support/index.js',
        specPattern: [
            "cypress/integration/create-user.js",
            "cypress/integration/create-user-group.js",
            "cypress/integration/create-work.js",
            "cypress/integration/create-task.js",
            "cypress/integration/create-conversation.js",
            "cypress/integration/edit-task.js",
            "cypress/integration/edit-work.js",
            "cypress/integration/edit-work-author.js",
            "cypress/integration/delete-task.js",
            "cypress/integration/delete-work.js"
        ]
    },
    env: {
        domain: "http://nginx:80",
        loginData: {
            failed: {
                username: "admin",
                password: "adminadmin"
            },
            student: {
                username: "student",
                password: "studentstudent"
            },
            supervisor: {
                username: "supervisor",
                password: "supervisorsupervisor"
            }
        }
    }
};
