module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        copy: {
            main: {
                src: 'vendor/nette/forms/src/assets/netteForms.min.js',
                dest: 'www/js/netteForms.min.js',
            },
        },
    });

    grunt.loadNpmTasks('grunt-contrib-copy');

    grunt.registerTask('default', ['copy']);
};