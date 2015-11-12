var gulp = require('gulp');
var yaml = require('js-yaml');
var fs   = require('fs');
var SwaggerParser = require('swagger-parser');
var gutil = require('gulp-util');

gulp.task('yaml-swagger', [], function () {
    fs.readFile('public/doc/swagger.yaml', function(error, data) {
        if (error) {
            console.log(error);
        } else {
            var doc = yaml.safeLoad(data);
            var savedoc = yaml.safeLoad(data);
            SwaggerParser.validate(doc)
            .then(function(api) {
                gutil.log("Validated API %s, Version: %s", gutil.colors.magenta(api.info.title), api.info.version);
                fs.writeFile('public/doc/swagger.json', JSON.stringify(savedoc), function (error) {
                    if (error) {
                        gutil.log(gutil.colors.red(error));
                    }
                });
            })
            .catch(function(err) {
                gutil.log(gutil.colors.red.bold(err));
            });

        }
    });
});
