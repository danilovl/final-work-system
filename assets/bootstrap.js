import { startStimulusApp, registerControllers } from 'vite-plugin-symfony/stimulus/helpers';

export const app = startStimulusApp();

registerControllers(
    app,
    import.meta.glob('./controllers/*_controller.js', {
        query: '?stimulus',
        eager: true
    })
);

app.debug = false;
