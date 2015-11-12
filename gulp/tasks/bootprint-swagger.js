var gulp = require('gulp');
var bootprint = require('bootprint');
var bootprintSwagger = require('bootprint-swagger');
var SwaggerParser = require('swagger-parser');
var gutil = require('gulp-util');
var fs   = require('fs');

gulp.task('bootprint-swagger', ['validate-swagger'], function () {
    SwaggerParser.bundle('public/doc/swagger.yaml', {
        "cache": {
            "fs": false
        }
    })
    .then(function(api) {
        fs.writeFile('public/doc/swagger.json', JSON.stringify(api, null, "\t"), function (error) {
            if (error) {
                gutil.log(gutil.colors.red(error));
            } else {
                bootprint.load(bootprintSwagger)
                .merge({
                    less: {
                        main: [
                            'public/doc/style.less'
                        ]
                    }
                })
                .build('public/doc/swagger.json', 'public/doc/')
                .generate()
                .done();
                gutil.log("Bundled API %s, Version: %s", gutil.colors.magenta(api.info.title), api.info.version);
            }
        });
    })
    .catch(function(err) {
        gutil.log(gutil.colors.red.bold(err));
    });
});
