module.exports = function(config){
    config.set({

        basePath : '..',

        frameworks: ['jasmine'],

        browsers : ['PhantomJS'],

        plugins : [
            'karma-phantomjs-launcher',
            'karma-jasmine'
        ],

    });
};
