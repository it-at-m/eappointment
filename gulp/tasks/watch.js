var gulp = require('gulp');

// Rerun the task when a file changes
gulp.task('watch', function() {
	gulp.watch([
		'scss/**/*.scss'
		], ['scss']);
});

