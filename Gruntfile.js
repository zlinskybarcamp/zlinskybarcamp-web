module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        copy: {
            main: {
                files: [
                    {expand: true, cwd: 'bower_components/bootstrap/fonts/', src: ['**'], dest: 'www/fonts/'},
                ],
            },
        },
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
                        'assets/less/flash.less'
                    ],
                    'www/css/admin.css': [
                        'assets/bower_components/bootstrap/dist/css/bootstrap.css',
                        'assets/bower_components/happy/dist/happy.css',
                        'assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css',
                        'assets/bower_components/ublaboo-datagrid/assets/dist/datagrid.css',
                        'assets/bower_components/ublaboo-datagrid/assets/dist/datagrid-spinners.css',
                        'assets/bower_components/bootstrap-select/dist/css/bootstrap-select.css',
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
                        'assets/bower_components/jquery/dist/jquery.js',
                        'assets/bower_components/nette-forms/src/assets/netteForms.js',
                        'assets/bower_components/nette.ajax.js/nette.ajax.js',
                        'assets/bower_components/happy/dist/happy.js',
                        'assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.js',
                        'assets/bower_components/jquery-ui-sortable/jquery-ui-sortable.js',
                        'assets/bower_components/ublaboo-datagrid/assets/dist/datagrid.js',
                        'assets/bower_components/ublaboo-datagrid/assets/dist/datagrid-instant-url-refresh.js',
                        'assets/bower_components/ublaboo-datagrid/assets/dist/datagrid-spinners.js',
                        'assets/bower_components/bootstrap/dist/js/bootstrap.js',
                        'assets/bower_components/bootstrap-select/dist/js/bootstrap-select.js',
                        'assets/bower_components/clipboard/dist/clipboard.js'
                    ]
                }
            }
        },
        watch: {
            js: {
                files: 'assets/js/*.js',
                tasks: ['uglify']
            },
        },
    });

    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['copy', 'less', 'uglify']);
};