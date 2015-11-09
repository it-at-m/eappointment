var gulp = require('gulp');
var bootprint = require('bootprint');
var bootprintSwagger = require('bootprint-swagger');

gulp.task('bootprint-swagger', [], function () {
    require('bootprint')
      .load(require('bootprint-swagger'))
      .build('public/doc/swagger.json', 'public/doc/')
      .generate()
      .done(console.log);
});
