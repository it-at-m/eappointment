var gulp = require('gulp');

// Default task
gulp.task('default', [
    'validate-swagger',
    'bootprint-swagger',
    'watch'
], function () {
});
