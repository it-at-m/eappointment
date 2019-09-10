/* global require */
var gulp = require('gulp');
var gutil = require('gulp-util');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var plumber = require('gulp-plumber');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var notifier = require('node-notifier');
var crypto = require('crypto');
var bundler = require('../browserify.js');
var vendorlist = require('../vendorlist.js');
var eventstream = require('event-stream');
var rename = require('gulp-rename');

gulp.task('js', ['lint'], function () {
    var streams = ['./js/index.js'].map(function(filename) {
        return bundler(filename)
            .external(vendorlist)
            .bundle()
            .on('error', function (message) {
                gutil.log('[browserify] ' +  gutil.colors.red(message));
                notifier.notify({
                    "title": "zmsbot-Build-Error",
                    "message" : "Error: " + message
                });
            })
           .pipe(source(filename))
           .pipe(buffer())
           .pipe(plumber())
           .pipe(sourcemaps.init({
               'loadMaps': true,
               'identityMap': true,
               'debug': true
           }))
           .pipe(plumber())
           .pipe(uglify())
           .pipe(rename({dirname:''}))
           .pipe(sourcemaps.write('./', {
               sourceMappingURL: function (file) {
                   //gutil.log('[sourcemaps] Rewrite path ' +  gutil.colors.green(file.relative));
                   // Avoid caching of source
                   return file.relative + '.map?build=' + crypto.createHash('sha1').update(file.contents).digest('hex');
               },
               'debug': true
           }))
           .pipe(gulp.dest('./public/_js/'))
           //.on('end', cb)
    })
    return eventstream.merge.apply(null, streams)
});
