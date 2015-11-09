var gulp = require('gulp');
var yaml = require('js-yaml');
var fs   = require('fs');

gulp.task('yaml-swagger', [], function () {
    fs.readFile('public/doc/swagger.yaml', function(error, data) {
        if (error) {
            console.log(error);
        } else {
            var doc = yaml.safeLoad(data);
            fs.writeFile('public/doc/swagger.json', JSON.stringify(doc), function (error) {
                if (error) {
                    console.log(error);
                }
            });
        }
    });
});
