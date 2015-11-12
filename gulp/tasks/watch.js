var gulp = require('gulp');

// Rerun the task when a file changes
gulp.task('watch', function() {
    gulp.watch([
        'public/doc/style.less',
        'public/doc/swagger.yaml',
        'public/doc/schema/*.json',
        '!public/doc/swagger.json',
        'routing.php'
    ], [
        'bootprint-swagger'
    ]);
});
