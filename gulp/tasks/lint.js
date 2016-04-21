var gulp = require('gulp');
var gutil = require('gulp-util');
var plumber = require('gulp-plumber');
var gulpJshint = require('gulp-jshint');
var jshintStylish = require('jshint-stylish');

gulp.task('lint', [], function () {
    gulp.src('js/**/*.js')
        .pipe(plumber())
        .pipe(gulpJshint({
            'esnext': true
        }))
        .pipe(gulpJshint.reporter(jshintStylish))
    ;
});
