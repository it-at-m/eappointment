var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var sassGlob = require('gulp-sass-glob');

gulp.task('scss', [
    //'watch'
], function () {
    gulp.src('scss/admin.scss')
        .pipe(sourcemaps.init())
        .pipe(sassGlob())
        .pipe(sass().on('error', sass.logError))
        //.pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./public/_css/'));
});

gulp.task('scss-print', [
    //'watch'
], function () {
    gulp.src('scss/print.scss')
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        //.pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./public/_css/'));
});
