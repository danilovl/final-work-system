const Encore = require('@symfony/webpack-encore');
const MergeIntoSingleFilePlugin = require('webpack-merge-and-include-globally');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]'
    })
    .copyFiles([{
        from: './node_modules/tinymce',
        to: './tinymce/[path][name].[ext]',
        includeSubdirectories: true,
        pattern: /.*/
    }])
    .copyFiles([{
        from: './assets/tinymce/css',
        to: './tinymce/css/[name].[ext]',
        pattern: /.*/
    }])
    .copyFiles([{
        from: './assets/tinymce/js/langs',
        to: './tinymce/langs/[name].[ext]',
        pattern: /.*/
    }])
    .enableVersioning()
    .addStyleEntry('app/css', [
        'bootstrap/dist/css/bootstrap.css',
        'font-awesome/css/font-awesome.min.css',
        'pnotify/dist/pnotify.css',
        'pnotify/dist/pnotify.buttons.css',
        'pnotify/dist/pnotify.nonblock.css',
        'malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css',
        'switchery/standalone/switchery.css',
        'select2/dist/css/select2.min.css',
        'icheck/skins/flat/green.css',
        'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css',
        './assets/css/main.css'
    ])
    .addStyleEntry('app_security/css', [
        'bootstrap/dist/css/bootstrap.min.css',
        'font-awesome/css/font-awesome.min.css',
        './assets/css/main.css'
    ])
    .addPlugin(
        new MergeIntoSingleFilePlugin({
            files: {
                'js/main.js': [
                    './node_modules/jquery/dist/jquery.min.js',
                    './node_modules/jquery-ui-dist/jquery-ui.min.js',
                    './node_modules/inputmask/dist/jquery.inputmask.js',
                    './node_modules/smartresize.js/dist/smartresize.min.js',
                    './node_modules/bootstrap/dist/js/bootstrap.min.js',
                    './node_modules/tinymce/tinymce.min.js',
                    './node_modules/tinymce/themes/modern/theme.js',
                    './node_modules/fastclick/lib/fastclick.js',
                    './node_modules/pnotify/dist/pnotify.js',
                    './node_modules/pnotify/dist/pnotify.buttons.js',
                    './node_modules/pnotify/dist/pnotify.nonblock.js',
                    './node_modules/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js',
                    './node_modules/select2/dist/js/select2.full.min.js',
                    './node_modules/moment/min/moment-with-locales.js',
                    './node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
                    './node_modules/switchery-latest/dist/switchery.min.js',
                    './node_modules/icheck/icheck.min.js',
                    './node_modules/sticky-js/dist/sticky.min.js',
                    './node_modules/nprogress/nprogress.js',
                    './node_modules/bootstrap-progressbar/bootstrap-progressbar.min.js',
                    './node_modules/jszip/dist/jszip.js',
                    './assets/js/collection/jquery.collection.js',
                    './assets/js/optgroup/select2.optgroupSelect.js',
                    './assets/js/jszip/jszip-utils.js',
                    './assets/js/main.js'
                ]
            },
            transform: {
                'js/main.js': code => {
                    return code;
                },
            },
        })
    )
    .addPlugin(
        new MergeIntoSingleFilePlugin({
            files: {
                'js/login.js': [
                    './node_modules/jquery/dist/jquery.min.js',
                    './node_modules/bootstrap/dist/js/bootstrap.min.js'
                ]
            }
        })
    )
    .splitEntryChunks()
    .enableStimulusBridge('./assets/controllers.json')
    .addEntry('app', './assets/app.js')
    .enableVueLoader(() => {}, { version: 3 })
    .addEntry('app_vue_calendar_manage', './assets/vue/app-calendar-manage.ts')
    .addEntry('app_vue_calendar_reservation', './assets/vue/app-calendar-reservation.ts')
    .addEntry('app_vue_calendar_event_detail', './assets/vue/app-calendar-event-detail.ts')
    .enableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

Encore.addLoader({
    test: /\.ts$/,
    loader: 'ts-loader',
    options: {
        appendTsSuffixTo: [/\.vue$/],
        transpileOnly: true
    }
});

module.exports = Encore.getWebpackConfig();
