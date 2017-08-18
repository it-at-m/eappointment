/* global require */
var gulp = require('gulp');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var plumber = require('gulp-plumber');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var crypto = require('crypto');
var bundler = require('../browserify.js');
var vendorlist = require('../vendorlist.js');

gulp.task('vendor', [], function () {
    var bundlerInstance = bundler('');
    vendorlist.forEach(function(lib) {
        bundlerInstance.require(lib);
    })
    return bundlerInstance
        .bundle()
        .pipe(source('vendor.js'))
        .pipe(buffer())
        .pipe(plumber())
        .pipe(sourcemaps.init({
            'loadMaps': true,
            'identityMap': true,
            'debug': true
        }))
        .pipe(uglify())
        .pipe(sourcemaps.write('./', {
            sourceMappingURL: function (file) {
                //gutil.log('[sourcemaps] Rewrite path ' +  gutil.colors.green(file.relative));
                // Avoid caching of source
                return file.relative + '.map?build=' + crypto.createHash('sha1').update(file.contents).digest('hex');
            },
            'debug': true
        }))
        .pipe(gulp.dest('./public/_js/'))
})
