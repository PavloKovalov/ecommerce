require.config({
	paths: {
        Underscore: 'libs/underscore/underscore',
        Backbone: 'libs/backbone/backbone'
    }
});

require([
	'modules/taxes/application',
    'order!libs/underscore/underscore-min',
    'order!libs/backbone/backbone-min'
    ], function(App){
		App.initialize();
	}
);