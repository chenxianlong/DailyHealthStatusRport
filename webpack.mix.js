const mix = require('laravel-mix');
const CompressionPlugin = require('compression-webpack-plugin');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

const javascriptOutputPathPrefix = 'js/';
const stylesheetOutputPathPrefix = 'css/';

if (mix.inProduction()) {
    mix.version();
    mix.webpackConfig({
        plugins: [
            new CompressionPlugin({
                filename: '[path].gz[query]',
                algorithm: "gzip",
                test: /\.(js|css|html|svg)$/,
                compressionOptions: { level: 9 },
                threshold: 10240,
                minRatio: 0.8,
                deleteOriginalAssets: false,
            }),
            new CompressionPlugin({
                filename: '[path].br[query]',
                algorithm: 'brotliCompress',
                test: /\.(js|css|html|svg)$/,
                compressionOptions: { level: 11 },
                threshold: 10240,
                minRatio: 0.8,
                deleteOriginalAssets: false,
            }),
        ],
    });
} else {
    mix.setPublicPath('public/development');
    mix.setResourceRoot('/development');
}

mix.js('resources/js/app.js', javascriptOutputPathPrefix)
    .sass('resources/sass/app.scss', stylesheetOutputPathPrefix);
