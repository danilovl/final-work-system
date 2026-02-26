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
        supportFile: 'tests/EndToEnd/Cypress/support/index.ts',
        specPattern: [
            'tests/EndToEnd/Cypress/integration/common/check-url.ts',
            'tests/EndToEnd/Cypress/integration/user/edit-profile.ts',
            'tests/EndToEnd/Cypress/integration/user/change-profile-image.ts',
            'tests/EndToEnd/Cypress/integration/user/delete-profile-image.ts',
            'tests/EndToEnd/Cypress/integration/user/create-user.ts',
            'tests/EndToEnd/Cypress/integration/user/create-user-group.ts',
            'tests/EndToEnd/Cypress/integration/work/create-work.ts',
            'tests/EndToEnd/Cypress/integration/task/create-task.ts',
            'tests/EndToEnd/Cypress/integration/conversation/create-conversation.ts',
            'tests/EndToEnd/Cypress/integration/conversation/search-conversation.ts',
            'tests/EndToEnd/Cypress/integration/event/create-event-address.ts',
            'tests/EndToEnd/Cypress/integration/event/create-event-calendar.ts',
            'tests/EndToEnd/Cypress/integration/task/edit-task.ts',
            'tests/EndToEnd/Cypress/integration/task/change-status-task.ts',
            'tests/EndToEnd/Cypress/integration/task/search-task.ts',
            'tests/EndToEnd/Cypress/integration/work/edit-work.ts',
            'tests/EndToEnd/Cypress/integration/work/edit-work-author.ts',
            'tests/EndToEnd/Cypress/integration/event/delete-event-calendar.ts',
            'tests/EndToEnd/Cypress/integration/event/delete-event-address.ts',
            'tests/EndToEnd/Cypress/integration/conversation/delete-conversation.ts',
            'tests/EndToEnd/Cypress/integration/task/delete-task.ts',
            'tests/EndToEnd/Cypress/integration/work/delete-work.ts',
            'tests/EndToEnd/Cypress/integration/user/change-password.ts',
            'tests/EndToEnd/Cypress/integration/security/login.ts'
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
