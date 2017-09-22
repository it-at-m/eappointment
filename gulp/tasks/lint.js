/* global require */
var gulp = require('gulp');
var plumber = require('gulp-plumber');
var eslint = require('gulp-eslint');

gulp.task('lint', [], (done) => {
    return gulp.src('js/**/*.js')
        .pipe(plumber())
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError())
    ;
    done();
});
