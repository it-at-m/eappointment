var gulp = require('gulp');
var fs   = require('fs');
var gutil = require('gulp-util');
var browserify = require('browserify');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var transform_stringify = require('stringify');
var plumber = require('gulp-plumber');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var transform_babelify = require('babelify');
var transform_shim = require('browserify-shim');
var notifier = require('node-notifier');
var crypto = require('crypto');

gulp.task('js', ['lint'], function () {
    //gutil.log('[browserify] ' +  'build js');
    var bundler = browserify('./js/index.js', {
        'transform': [
            transform_stringify({
                extension: ['html'],
                minify: true
            }),
            transform_babelify.configure({
                'presets': ['es2015'],
                'plugins': []
            }),
            transform_shim
        ],
        'debug': true
    });
    bundler.on('log', function (message) {
        gutil.log('[browserify] ' +  message);
        notifier.notify({
            "title": "zmsbot-Build-Log",
            "message" : "Info: " + message
        });
    });
    bundler.on('error', function (message) {
        gutil.log('[browserify] ' +  gutil.colors.red(message));
        notifier.notify({
            "title": "zmsbot-Build-Error",
            "message" : "Error: " + message
        });
    });
    bundler.bundle()
        .on('error', function (message) {
            gutil.log('[browserify] ' +  gutil.colors.red(message));
            notifier.notify({
                "title": "zmsbot-Build-Error",
                "message" : "Error: " + message
            });
        })
        .pipe(source('index.js'))
        .pipe(buffer())
        .pipe(plumber())
        .pipe(sourcemaps.init({
            'loadMaps': true,
            'identityMap': true,
            'debug': true
        }))
        .pipe(plumber())
        .pipe(uglify())
        .pipe(sourcemaps.write('./', {
            sourceMappingURL: function (file) {
                //gutil.log('[sourcemaps] Rewrite path ' +  gutil.colors.green(file.relative));
                // Avoid caching of source
                return file.relative + '.map?build=' + crypto.createHash('sha1').update(file.contents).digest('hex');
            },
            'debug': true
        }))
        .pipe(gulp.dest('./public/_js/'));
});
