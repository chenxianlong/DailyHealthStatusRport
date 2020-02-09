const mix = require('laravel-mix');

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
} else {
    mix.setPublicPath('public/development');
    mix.setResourceRoot('/development');
}

mix.js('resources/js/app.js', javascriptOutputPathPrefix)
    .sass('resources/sass/app.scss', stylesheetOutputPathPrefix);
