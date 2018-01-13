module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            main: {
                options: {
                    compress: true,
                    sourceMap: false
                },
                files: {
                    'www/css/main.css': [
                        'assets/css/pure/pure.css',
                        'www/css/fonts.css',
                        'www/plugins/slick/slick.css',
                        'assets/less/main.less',
                        'assets/css/flash.css'
                    ]
                }
            }
        },
        uglify: {
            options: {
                sourceMap: false,
                beautify: false
            },
            default: {
                files: {
                    'www/js/main.js': [
                        'assets/js/jquery-3.1.1.js',
                        'www/plugins/jquery-ui/jquery-ui.js',
                        'www/plugins/slick/slick.js',
                        'assets/js/main.js',
                        'vendor/nette/forms/src/assets/netteForms.js'
                    ],
                    'www/js/admin.js': [
                        'assets/js/jquery-3.1.1.js',
                        'vendor/nette/forms/src/assets/netteForms.js'
                    ]
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['less', 'uglify']);
};