'use strict';

module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        // Metadata.
        pkg: grunt.file.readJSON('package.json'),
        banner: '/* <%= pkg.title || pkg.name %> - v<%= pkg.version %> - ' +
                '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
                '   Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %>;' +
                ' Licensed <%= pkg.license %> */\n\n',

        // Task configuration.
        copy: {
            main: {
                files: [
                    {
                        src: '../src/jquery.<%= pkg.name %>.js',
                        dest: '../dist/jquery.<%= pkg.name %>.js'
                    },
                    {
                        src: '../src/jquery.<%= pkg.name %>.css',
                        dest: '../dist/jquery.<%= pkg.name %>.css'
                    },
                    {
                        src: '../src/angularjs.<%= pkg.name %>.js',
                        dest: '../dist/angularjs.<%= pkg.name %>.js'
                    },
                ]
            }
        },

        uglify: {
            options: {
                banner: '<%= banner %>'
            },
            jquery: {
                src: '../dist/jquery.<%= pkg.name %>.js',
                dest: '../dist/jquery.<%= pkg.name %>.min.js'
            },
            angularjs: {
                src: '../dist/angularjs.<%= pkg.name %>.js',
                dest: '../dist/angularjs.<%= pkg.name %>.min.js'
            },
        },

        cssmin: {
            options: {
                banner: '<%= banner %>'
            },
            dist: {
                src: '../dist/jquery.<%= pkg.name %>.css',
                dest: '../dist/jquery.<%= pkg.name %>.min.css'
            }
        },

        qunit: {
            files: ['tests/jquery-plugin/**/*.html']
        },

        karma: {
            unit: {
                configFile: 'tests/karma.conf.js',
                singleRun: true,
                autoWatch: false,
                options: {
                    files: [
                        'bower_components/jquery/dist/jquery.js',
                        'bower_components/moment/min/moment-with-locales.js',
                        'bower_components/bootstrap/dist/js/bootstrap.js',
                        'bower_components/eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker.js',
                        'bower_components/angular/angular.js',
                        'bower_components/angular-mocks/angular-mocks.js',
                        '../src/jquery.<%= pkg.name %>.js',
                        '../src/angularjs.<%= pkg.name %>.js',
                        'tests/angular-wrapper/**/*.js',
                    ],
                }
            }
        },
    });

    // These plugins provide necessary tasks.
    grunt.loadNpmTasks('grunt-contrib-qunit');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-karma');

    // Default task.
    grunt.registerTask('default', ['copy', 'uglify', 'cssmin']);
};
