var gulp = require('gulp');

// Default task
gulp.task('default', [
    'yaml-swagger',
    'bootprint-swagger',
    'watch'
], function () {
});
