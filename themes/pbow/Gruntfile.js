/**
 * Starter-Theme's Gruntfile
 * http://urbaninsight.com
 * Author: Lehel @Urban insight, Inc.",
 * Copyright 2014-2015 Urban Insight, Inc.
 * Licensed under MIT  
 */

module.exports = function(grunt) {

  // Project configuration.

  var autoprefixer = require('autoprefixer-core');
  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    jshint: {
      // You get to make the name
      // The paths tell JSHint which files to validate
      all: ['Gruntfile.js', 'js/**/*.js'],
      options: {
        globals: {
          jQuery: true
        }
      },
    }, 

    watch: {
        scripts: {
            files: ["js/*.js"],
            tasks: ["jshint"]
        },
        less: {
            files: ["css/*.less", "less/*.less", "bootstrap/less/*.less"],
            tasks: ["less","autoprefixer"], 
            options: {
                nospawn: true
            }
        }
    },

    uglify: {
      build: {
        src: 'scripts/custom.js',
        dest: 'scripts/custom.min.js'
      }
    },

    // Copy has a lot of dependencies and info files makes drupal development slow
    // copy: {
    //   main: {
    //     expand: true,
    //     cwd: 'bower_components',
    //     src: 'bootstrap/**',
    //     dest: ''
    //   },
    // },

    less: {
        development: {
            options: {
                compress: true,
                yuicompress: false,
                optimization: 2,
                cleancss:false,  
                paths: ["css"],
                syncImport: false,
                strictUnits:false,
                strictMath: true,
                strictImports: true,
                ieCompat: false
            },
            files: {
                "css/style.css": "less/style.less",
                "css/printer-version.css": "less/printer-version.less",
                "css/print.css": "less/print.less"
            }
        }
    },

    autoprefixer: {
        options: {
            browsers: ['last 2 versions', 'ie 8', 'ie 9', '> 1%']
        },
        css: {
            src: 'css/style.css',
            dest: 'css/style.css'
        }
    },

  });

  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-contrib-jshint');

  // Default task(s).
  grunt.registerTask("default", ["watch"]);

  // Othertasks
  grunt.registerTask('evaljs', ['jshint']);
  grunt.registerTask('ugly', ['uglify']);

};
