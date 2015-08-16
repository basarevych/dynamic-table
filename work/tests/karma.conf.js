module.exports = function(config){
    config.set({

        basePath : '..',

        frameworks: ['jasmine'],

        browsers : ['Firefox'],

        plugins : [
            'karma-firefox-launcher',
            'karma-jasmine'
        ],

    });
};
