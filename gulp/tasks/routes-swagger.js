var gulp = require('gulp');
var fs   = require('fs');
var gutil = require('gulp-util');
var swaggerJSDoc = require('swagger-jsdoc');

gulp.task('routes-swagger', [], function () {
    var options = {
        swaggerDefinition: {
            info: {
                title: 'Routes only, do not use, only for reference',
                version: '1.0.0',
            },
        },
        apis: ['./routing.php'], // Path to the API docs
    };
    var swaggerSpec = swaggerJSDoc(options);
    fs.writeFile('public/doc/routes.json', JSON.stringify(swaggerSpec.paths, null, "\t"), function (error) {
        if (error) {
            gutil.log(gutil.colors.red(error));
        }
    });
});
