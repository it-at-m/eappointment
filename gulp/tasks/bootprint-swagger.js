var gulp = require('gulp');
var bootprint = require('bootprint');
var bootprintSwagger = require('bootprint-swagger');
var SwaggerParser = require('swagger-parser');
var gutil = require('gulp-util');
var fs   = require('fs');
var Handlebars = require("handlebars");

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
                .merge({
                    handlebars: {
                        partials: 'public/doc/partials',
                        helpers: {
                            'eachSortKey': function(context, key, options) {
                                // Sort by property of object
                                var ret = "";
                                var data;
                                if (typeof context !== "object") {
                                    return ret;
                                }
                                var keys = context.slice(0);
                                keys.sort(function (a,b) {
                                    a = String(a[key]).toLowerCase();
                                    b = String(b[key]).toLowerCase();
                                    return a.localeCompare(b);
                                }).forEach(function (obj, index) {
                                    if (options.data) {
                                        data = Handlebars.createFrame(options.data || {});
                                        data.index = index;
                                        data.key = obj[key];
                                        data.length = keys.length;
                                        data.first = index === 0;
                                        data.last = index === keys.length - 1;

                                    }
                                    ret = ret + options.fn(obj, {data: data})
                                });
                                return ret
                            }
                        }
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
