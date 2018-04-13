var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');

gulp.task('scss', [
    //'watch'
], function () {
    gulp.src('scss/ticketprinter.scss')
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        //.pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./public/_css/'));
});

gulp.task('scss-print', [
    //'watch'
], function () {
    gulp.src('scss/layout/print.scss')
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        //.pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./public/_css/'));
});
