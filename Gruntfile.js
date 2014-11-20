'use strict';

module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    // Metadata.
    pkg: grunt.file.readJSON('package.json'),
    banner: '/*! <%= pkg.title || pkg.name %> - v<%= pkg.version %> - ' +
      '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
      '<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %>' +
      '* Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %>;' +
      ' Licensed <%= pkg.license %> */\n',

    // Task configuration.
    concat: {
      options: {
        banner: '<%= banner %>',
        stripBanners: true
      },
      js: {
        src: ['js/**/*.js'],
        dest: 'dist/jquery.<%= pkg.name %>.js'
      },
      css: {
        src: ['css/**/*.css'],
        dest: 'dist/jquery.<%= pkg.name %>.css'
      },
    },

    uglify: {
      options: {
        banner: '<%= banner %>'
      },
      dist: {
        src: '<%= concat.js.dest %>',
        dest: 'dist/jquery.<%= pkg.name %>.min.js'
      },
    },

    cssmin: {
      options: {
        banner: '<%= banner %>'
      },
      dist: {
        files: {
          'dist/jquery.<%= pkg.name %>.min.css': [
            '<%= concat.css.dest %>'
          ]
        }
      }
    },

    qunit: {
      files: ['test/qunit/**/*.html']
    },
  });

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-qunit');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-cssmin');

  // Default task.
  grunt.registerTask('default', ['qunit', 'concat', 'uglify', 'cssmin']);
};
