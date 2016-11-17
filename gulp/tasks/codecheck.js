var gulp = require('gulp');
var gutil = require('gulp-util');
var plumber = require('gulp-plumber');
var eslint = require('gulp-eslint');

gulp.task('codecheck', [], function (done) {
    gulp.src('js/**/*.js')
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError())
    ;
    done();
});
