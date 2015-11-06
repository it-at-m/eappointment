var gulp = require('gulp');
var bootprint = require('bootprint');
var bootprintSwagger = require('bootprint-swagger');

// Default task
gulp.task('bootprint-swagger', [
    //'clean'
], function () {
    // Load bootprint
    bootprint
      // Load bootprint-swagger
      .load(bootprintSwagger)
      // Customize configuration, override any options
      .merge({ /* Any other configuration */})
      // Specify build source and target
      .build('public/doc/swagger.json', 'public/doc/')
      // Generate swagger-documentation into "target" directory
      .generate()
      .done(console.log);
});
