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
                        src: 'js/jquery.<%= pkg.name %>.js',
                        dest: 'dist/jquery.<%= pkg.name %>.js'
                    },
                    {
                        src: 'css/jquery.<%= pkg.name %>.css',
                        dest: 'dist/jquery.<%= pkg.name %>.css'
                    },
                    {
                        src: 'js/angularjs.<%= pkg.name %>.js',
                        dest: 'dist/angularjs.<%= pkg.name %>.js'
                    },
                ]
            }
        },

        uglify: {
            options: {
                banner: '<%= banner %>'
            },
            jquery: {
                src: 'dist/jquery.<%= pkg.name %>.js',
                dest: 'dist/jquery.<%= pkg.name %>.min.js'
            },
            angularjs: {
                src: 'dist/angularjs.<%= pkg.name %>.js',
                dest: 'dist/angularjs.<%= pkg.name %>.min.js'
            },
        },

        cssmin: {
            options: {
                banner: '<%= banner %>'
            },
            dist: {
                src: 'dist/jquery.<%= pkg.name %>.css',
                dest: 'dist/jquery.<%= pkg.name %>.min.css'
            }
        },

        qunit: {
            files: ['test/qunit/**/*.html']
        },
    });

    // These plugins provide necessary tasks.
    grunt.loadNpmTasks('grunt-contrib-qunit');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    // Default task.
    grunt.registerTask('default', ['qunit', 'copy', 'uglify', 'cssmin']);
};
