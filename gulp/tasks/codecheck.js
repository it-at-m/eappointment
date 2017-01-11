/* global require */
var gulp = require('gulp');
var eslint = require('gulp-eslint');

gulp.task('codecheck', [], function (done) {
    gulp.src('js/**/*.js')
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError())
    ;
    done();
});
