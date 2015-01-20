'use strict';

module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        // Metadata.
        pkg: grunt.file.readJSON('package.json'),
        banner: '/* <%= pkg.title || pkg.name %> - v<%= pkg.version %> - ' +
                '<%= grunt.template.today("yyyy-mm-dd") %> */\n\n',

        // Task configuration.
        concat: {
            options: {
                banner: '<%= banner %>',
                stripBanners: true
            },
            vendorjs: {
                src: [
                    'bower_components/jquery/dist/jquery.js',
                    'bower_components/jquery-form/jquery.form.js',
                    'bower_components/jquery.cookie/jquery.cookie.js',
                    'bower_components/moment/min/moment-with-locales.js',
                    'bower_components/bootstrap/dist/js/bootstrap.js',
                    'bower_components/eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker.js',
                ],
                dest: 'public/js/vendor.js'
            },
            vendorcss: {
                src: [
                    'bower_components/bootstrap/dist/css/bootstrap.css',
                    'bower_components/bootstrap/dist/css/bootstrap-theme.css',
                    'bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css',
                ],
                dest: 'public/css/vendor.css'
            },
        },

        uglify: {
            options: {
                banner: '<%= banner %>'
            },
            vendorjs: {
                src: '<%= concat.vendorjs.dest %>',
                dest: 'public/js/vendor.min.js'
            },
        },

        cssmin: {
            options: {
                banner: '<%= banner %>'
            },
            vendorcss: {
                src: '<%= concat.vendorcss.dest %>',
                dest: 'public/css/vendor.min.css'
            }
        },
    });

    // These plugins provide necessary tasks.
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    // Default task.
    grunt.registerTask('default', ['concat', 'uglify', 'cssmin']);
};
