var gulp = require('gulp');
var fs   = require('fs');
var SwaggerParser = require('swagger-parser');
var gutil = require('gulp-util');

gulp.task('validate-swagger', ['routes-swagger'], function () {
    SwaggerParser.validate('public/doc/swagger.yaml', {
        "cache": {
            "fs": false
        },
        "$refs": {
            "circular": "ignore"
        }
    })
    .then(function(api) {
        gutil.log("Validated API %s, Version: %s", gutil.colors.magenta(api.info.title), api.info.version);
    })
    .catch(function(err) {
        gutil.log(gutil.colors.red.bold(err));
    });
});
