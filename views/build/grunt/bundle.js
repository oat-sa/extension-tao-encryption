module.exports = function(grunt) {
    'use strict';

    var requirejs   = grunt.config('requirejs') || {};
    var clean       = grunt.config('clean') || {};
    var copy        = grunt.config('copy') || {};

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);
    var out         = 'output';

    /**
     * Remove bundled and bundling files
     */
    clean.taoencryptionbundle = [out];

    /**
     * Compile tao files into a bundle
     */
    requirejs.taoencryptionbundle = {
        options: {
            baseUrl : '../js',
            dir : out,
            mainConfigFile : './config/requirejs.build.js',
            paths : {
                'taoEncryption' : root + '/taoEncryption/views/js',
            },
            modules : [{
                name: 'taoEncryption/controller/routes',
                include : ext.getExtensionsControllers(['taoEncryption']),
                exclude : ['mathJax'].concat(libs)
            }]
        }
    };

    /**
     * copy the bundles to the right place
     */
    copy.taoencryptionbundle = {
        files: [
            { src: [out + '/taoEncryption/controller/routes.js'],  dest: root + '/taoEncryption/views/js/controllers.min.js' },
            { src: [out + '/taoEncryption/controller/routes.js.map'],  dest: root + '/taoEncryption/views/js/controllers.min.js.map' }
        ]
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    // bundle task
    grunt.registerTask('taoencryptionbundle', ['clean:taoencryptionbundle', 'requirejs:taoencryptionbundle', 'copy:taoencryptionbundle']);
};
