var gulp = require('gulp');

// Rerun the task when a file changes
gulp.task('watch', function() {
    gulp.watch([
        'public/doc/style.less',
        'public/doc/swagger.yaml',
        'public/doc/*.json',
        '!public/doc/swagger.json'
    ], [
        'bootprint-swagger'
    ]);
});
