var Encore = require('@symfony/webpack-encore');

Encore
// directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('page', './assets/js/page.js')
    .addEntry('fastFinder', './assets/js/fastFinder.js')
    .addEntry('idleTimeout', './assets/js/idleTimeout.js')
//    .addEntry('sideBar', './assets/js/sideBar.js')
//    .addEntry('headerMenu', './assets/js/headerMenu.js')
    .addEntry('notificationTray', './assets/js/notificationTray.js')
//    .addEntry('app', './assets/js/app.js')
//    .addEntry('default', './assets/themes/default/default.js')
    .addEntry('container','./assets/js/container.js')
    .addEntry('pagination','./assets/js/pagination.js')
    .addEntry('photoLoader','./assets/js/photoLoader.js')
    .addStyleEntry('css/core', './assets/css/core.scss')
    .addStyleEntry('css/fastFinder', './assets/css/fastFinder/fastFinder.css')
    .splitEntryChunks()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())

    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    .enableReactPreset()
    .configureBabel((babelConfig) => {
        if (Encore.isProduction()) {
            babelConfig.plugins.push(
                'transform-react-remove-prop-types'
            );
        }
        babelConfig.plugins.push(
            'babel-plugin-transform-object-rest-spread'
        );
        const preset = babelConfig.presets.find(([name]) => name === "@babel/preset-env");
        if (preset !== undefined) {
            preset[1].useBuiltIns = "usage";
            preset[1].corejs = '3.0.0';
        }
    })
    .copyFiles([
        {from: './assets/static', to: 'static/[path][name].[ext]', pattern: /\.(png|gif|jpg|jpeg|svg)$/},
        {from: './node_modules/ckeditor/', to: 'ckeditor/[path][name].[ext]', pattern: /\.(js|css)$/, includeSubdirectories: false},
        {from: './node_modules/ckeditor/adapters', to: 'ckeditor/adapters/[path][name].[ext]'},
        {from: './node_modules/ckeditor/lang', to: 'ckeditor/lang/[path][name].[ext]'},
        {from: './node_modules/ckeditor/plugins', to: 'ckeditor/plugins/[path][name].[ext]'},
        {from: './node_modules/ckeditor/skins', to: 'ckeditor/skins/[path][name].[ext]'}
    ])


    // enables Sass/SCSS support
    .enableSassLoader()
    .enablePostCssLoader()
    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()
    .enableSingleRuntimeChunk()
;

module.exports = Encore.getWebpackConfig();
