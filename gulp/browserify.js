/* global require, module */
var browserify = require('browserify');
var gutil = require('gulp-util');
var notifier = require('node-notifier');
var transform_stringify = require('stringify');
var transform_babelify = require('babelify');
var transform_shim = require('browserify-shim');

var bundlercreate = function (filename) {
    var bundler = browserify(filename, {
        'transform': [
            transform_stringify({
                extension: ['html'],
                minify: true
            }),
            transform_babelify.configure({
                'presets': ['env', 'react'],
                'plugins': []
            }),
            transform_shim
        ],
        'debug': true
    });
    bundler.on('log', function (message) {
        gutil.log('[browserify] ' +  message);
        //notifier.notify({
        //    "title": "zmsbot-Build-Log",
        //    "message" : "Info: " + message
        //});
    });
    bundler.on('error', function (message) {
        gutil.log('[browserify] ' +  gutil.colors.red(message));
        notifier.notify({
            "title": "zmsbot-Build-Error",
            "message" : "Error: " + message
        });
    });
    //bundler.on('file', function (file) { gutil.log('[browserify] ' +  gutil.colors.yellow(file)); })
    //bundler.on('package', function (pkg) { gutil.log('[browserify] Require: ' +  gutil.colors.blue(pkg.__dirname)); })
    //bundler.on('transform', function (tr, file) { gutil.log('[browserify] Transform: ' +  gutil.colors.yellow(file)); })
    return bundler
}

module.exports = bundlercreate;
