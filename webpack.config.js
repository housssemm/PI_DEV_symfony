const Encore = require('@symfony/webpack-encore');
const path = require('path');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .addEntry('pose_detection', './src/ia_cam/src/index.js')
    .addEntry('livestream', './assets/livestream.js')
    .addAliases({
        '@pose': path.resolve(__dirname, 'src/ia_cam/src')
    })
    .splitEntryChunks()

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel(null)
    .enableSassLoader()
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            plugins: [
                require('autoprefixer')
            ]
        }
    })
    .configureDevServerOptions(options => {
        options.port = 3000; // Try a different port
        options.host = 'localhost';
        options.allowedHosts = 'all';
        options.https = false;
    })
    // .configureDevServerOptions(options => {
    //
    //     options.host = '172.20.10.4'; // Allow connections from other devices
    //     options.port = 3000;      // Or any port you want
    //     options.allowedHosts = 'all'; // Accept requests from any host
    // })
    // .configureDevServerOptions(options => {
    //     options.https = {
    //         key: './172.20.10.4-key.pem',
    //         cert: './172.20.10.4.pem',
    //     };
    //     options.host = '172.20.10.4'; // L’IP de ta machine
    //     options.port = 3000;
    //     options.allowedHosts = 'all'; // Autorise tous les clients sur le réseau
    // })

    // Add this to copy static assets
    .copyFiles({
        from: './assets/sounds',
        to: 'sounds/[path][name].[ext]'
    })
    // Enable CSS handling
    .enableSassLoader()
    .enablePostCssLoader()
    // Configure CSS extraction
    .configureCssLoader(options => {
        options.modules = true;
    })
    // Ensure styles are copied to the build directory
    .copyFiles({
        from: './src/ia_cam/src',
        to: 'styles/[path][name].[ext]',
        pattern: /\.(css)$/
    });

module.exports = Encore.getWebpackConfig();
