/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    // Metadata.
    pkg: grunt.file.readJSON('package.json'),
    banner: '/**\n' + 
      ' * <%= pkg.title || pkg.name %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
      '<%= pkg.homepage ? " * " + pkg.homepage + "\\n" : "" %>' +
      ' * Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author %>;' + ' License: <%= pkg.license %>\n' +
      ' */',
    cssmin: {
      combine: {
         options: {
          banner: '<%= banner %>'
        },
        files: {
          'css/dist/cmb.min.css': [ 'css/src/layout.css', 'css/src/generic.css', 'css/src/repeatable.css', 'css/src/group.css', 'css/src/file.css', 'css/src/misc-fields.css' ]
        }
      }
    }
  });

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-cssmin');

  // Default task.
  grunt.registerTask('default', [ 'cssmin' ] );

};
