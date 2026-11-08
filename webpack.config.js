// Webpack Encore v7+ is an ES Module. When imported via CommonJS require(),
// the API is exposed on the .default property.
const _encore = require('@symfony/webpack-encore');
const Encore = ('default' in _encore && _encore.default) ? _encore.default : _encore;

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    .configureWatchOptions((watchOptions) => {
        watchOptions.poll = 250;
        watchOptions.ignored = [
            "**/*.php",
            "**/node_modules",
            "**/public",
            "**/var",
            "**/vendor",
            "**/*.log",
        ];
    })

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
    .enablePostCssLoader()

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // Note: In Babel 8, useBuiltIns/corejs were removed from @babel/preset-env.
    // Modern browsers natively support ES2020+ features, eliminating the need for legacy polyfills.

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    .copyFiles({
        from: 'node_modules/tinymce/skins',
        to: 'skins/[path]/[name].[ext]'
    })
    .copyFiles({
        from: 'node_modules/tinymce/models',
        to: 'models/[path]/[name].[ext]'
    })
    .copyFiles({
        from: 'node_modules/tinymce/icons',
        to: 'icons/[path]/[name].[ext]'
    })
    .copyFiles({
        from: 'node_modules/tinymce/plugins',
        to: 'plugins/[path]/[name].[ext]'
    })
;

module.exports = Encore.getWebpackConfig({
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
      ],
      theme: {
        extend: {},
      },
      plugins: [],
});
