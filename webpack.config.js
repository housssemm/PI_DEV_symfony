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
    .addAliases({
        '@pose': path.resolve(__dirname, 'src/ia_cam/src')
    })
    .splitEntryChunks()
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
    // Add this configuration for dev-server
    .configureDevServerOptions(options => {
        options.port = 3000; // Try a different port
        options.host = 'localhost';
        options.allowedHosts = 'all';
        options.https = false;
    })
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
