var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');

gulp.task('scss', [
    //'watch'
], function () {
    gulp.src('scss/index.scss')
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        //.pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./public/_css/'));
});
