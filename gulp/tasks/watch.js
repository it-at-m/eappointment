var gulp = require('gulp');

// Rerun the task when a file changes
gulp.task('watch', function() {
	gulp.watch([
		'scss/**/*.scss'
		], ['scss']);
	gulp.watch([
		'js/**/*.js'
		], ['js']);
	gulp.watch([
		'public/_js/index.js'
		], ['buildinfo']);
});

