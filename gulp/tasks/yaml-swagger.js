var gulp = require('gulp');
var yaml = require('js-yaml');
var fs   = require('fs');
var SwaggerParser = require('swagger-parser');


gulp.task('yaml-swagger', [], function () {
    fs.readFile('public/doc/swagger.yaml', function(error, data) {
        if (error) {
            console.log(error);
        } else {
            var doc = yaml.safeLoad(data);
            var savedoc = yaml.safeLoad(data);
            SwaggerParser.validate(doc)
            .then(function(api) {
                console.log("Validated API %s, Version: %s", api.info.title, api.info.version);
                fs.writeFile('public/doc/swagger.json', JSON.stringify(savedoc), function (error) {
                    if (error) {
                        console.log(error);
                    }
                });
            })
            .catch(function(err) {
                console.error(err);
            });

        }
    });
});
