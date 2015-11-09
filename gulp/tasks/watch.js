var gulp = require('gulp');

// Rerun the task when a file changes
gulp.task('watch', function() {
    gulp.watch([
        'public/doc/swagger.yaml'
    ], ['yaml-swagger']);
    gulp.watch([
      'public/doc/swagger.json'
  ], ['bootprint-swagger']);
});
