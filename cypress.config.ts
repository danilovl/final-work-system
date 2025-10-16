import {defineConfig} from 'cypress';

export default defineConfig({
    e2e: {
        baseUrl: 'http://nginx:80',
        setupNodeEvents(on, config) {
            on('before:browser:launch', (browser: Cypress.Browser, launchOptions) => {
                if (browser.name === 'chrome') {
                    launchOptions.args.push('--disable-dev-shm-usage');
                }
                return launchOptions;
            });

            return config;
        },
        supportFile: 'tests/Cypress/support/index.ts',
        specPattern: [
            'tests/Cypress/integration/common/check-url.ts',
            'tests/Cypress/integration/user/edit-profile.ts',
            'tests/Cypress/integration/user/change-profile-image.ts',
            'tests/Cypress/integration/user/delete-profile-image.ts',
            'tests/Cypress/integration/user/create-user.ts',
            'tests/Cypress/integration/user/create-user-group.ts',
            'tests/Cypress/integration/work/create-work.ts',
            'tests/Cypress/integration/task/create-task.ts',
            'tests/Cypress/integration/conversation/create-conversation.ts',
            'tests/Cypress/integration/conversation/search-conversation.ts',
            'tests/Cypress/integration/event/create-event-address.ts',
            'tests/Cypress/integration/event/create-event-calendar.ts',
            'tests/Cypress/integration/task/edit-task.ts',
            'tests/Cypress/integration/task/change-status-task.ts',
            'tests/Cypress/integration/task/search-task.ts',
            'tests/Cypress/integration/work/edit-work.ts',
            'tests/Cypress/integration/work/edit-work-author.ts',
            'tests/Cypress/integration/event/delete-event-calendar.ts',
            'tests/Cypress/integration/event/delete-event-address.ts',
            'tests/Cypress/integration/conversation/delete-conversation.ts',
            'tests/Cypress/integration/task/delete-task.ts',
            'tests/Cypress/integration/work/delete-work.ts',
            'tests/Cypress/integration/user/change-password.ts',
            'tests/Cypress/integration/security/login.ts'
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
});
