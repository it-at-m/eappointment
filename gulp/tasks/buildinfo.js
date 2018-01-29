var gulp = require('gulp');
var gutil = require('gulp-util');
var notifier = require('node-notifier');
var fs = require('fs');

gulp.task('buildinfo', [], function () {
    var filesize = fs.statSync('public/_js/index.js').size;
    var filesizeReact = fs.statSync('public/_js/reactcomponents.js').size;
    gutil.log("[Build] index.js with " + gutil.colors.green((filesize / 1024) + " kb"));
    gutil.log("[Build] reactcomponents.js with " + gutil.colors.green((filesizeReact / 1024) + " kb"));
    notifier.notify({
        "title": "zmsadmin-Build",
        "message" : "index.js with " + filesize + " bytes."
    });
});
