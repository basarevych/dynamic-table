'use strict';

module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        // Metadata.
        pkg: grunt.file.readJSON('package.json'),
        banner: '/* <%= pkg.title || pkg.name %> - v<%= pkg.version %> - ' +
                '<%= grunt.template.today("yyyy-mm-dd") %> */\n\n',

        copy: {
            prepare: {
                files: [
                    /*
                     * Add your non-JS/CSS dependencies here
                     */

                    { // Copy Bootstrap fonts to common assets dir
                        expand: true,
                        cwd: 'bower_components/bootstrap/dist/',
                        src: 'fonts/**',
                        dest: 'assets/common/',     
                    },
                ]
            },
            dev: {
                files: [
                    {
                        expand: true,
                        cwd: 'assets/common/',
                        src: '**',
                        dest: '../public.dev/',
                    },
                    {
                        expand: true,
                        cwd: 'assets/override.dev/',
                        src: '**',
                        dest: '../public.dev/',
                    },
                ]
            },
            prod: {
                files: [
                    {
                        expand: true,
                        cwd: 'assets/common/',
                        src: '**',
                        dest: '../public.prod/',
                    },
                    {
                        expand: true,
                        cwd: 'assets/override.prod/',
                        src: '**',
                        dest: '../public.prod/',
                    },
                ]
            }
        },

        concat: {
            options: {
                banner: '<%= banner %>',
                stripBanners: true
            },
            vendorjs: {
                src: [
                    /*
                     * Add your JS dependencies here, order is respected
                     */
                    'bower_components/jquery/dist/jquery.js',
                    'bower_components/jquery-form/jquery.form.js',
                    'bower_components/jquery.cookie/jquery.cookie.js',
                    'bower_components/moment/min/moment-with-locales.js',
                    'bower_components/bootstrap/dist/js/bootstrap.js',
                    'bower_components/eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker.js',
                    '../vendor/basarevych/dynamic-table/src/jquery.dynamic-table.js',
                ],
                dest: '../public.dev/js/vendor.js'
            },
            vendorcss: {
                src: [
                    /*
                     * Add your CSS dependencies here, order is respected
                     */
                    'bower_components/bootstrap/dist/css/bootstrap.css',
                    'bower_components/bootstrap/dist/css/bootstrap-theme.css',
                    'bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css',
                    '../vendor/basarevych/dynamic-table/src/jquery.dynamic-table.css',
                ],
                dest: '../public.dev/css/vendor.css'
            },
            appjs: {
                src: [
                    'js/**/*.js'
                ],
                dest: '../public.dev/js/app.js'
            },
            appcss: {
                src: [
                    'css/**/*.css'
                ],
                dest: '../public.dev/css/app.css'
            },
        },

        uglify: {
            options: {
                banner: '<%= banner %>'
            },
            vendorjs: {
                src: '<%= concat.vendorjs.dest %>',
                dest: '../public.prod/js/vendor.min.js'
            },
            appjs: {
                src: '<%= concat.appjs.dest %>',
                dest: '../public.prod/js/app.min.js'
            },
        },

        cssmin: {
            options: {
                banner: '<%= banner %>'
            },
            vendorcss: {
                src: '<%= concat.vendorcss.dest %>',
                dest: '../public.prod/css/vendor.min.css'
            },
            appcss: {
                src: '<%= concat.appcss.dest %>',
                dest: '../public.prod/css/app.min.css'
            },
        },

        watch: {
            files: [ 'assets/**/*', 'css/**/*', 'js/**/*' ],
            tasks: ['dev'],
        },
    });

    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('dev', ['copy:prepare', 'copy:dev', 'concat']);
    grunt.registerTask('prod', ['copy:prepare', 'copy:prod', 'concat', 'uglify', 'cssmin']);
};
