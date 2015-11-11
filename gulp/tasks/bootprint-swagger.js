var gulp = require('gulp');
var bootprint = require('bootprint');
var bootprintSwagger = require('bootprint-swagger');

gulp.task('bootprint-swagger', [], function () {
    bootprint
      .load(bootprintSwagger)
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
});
