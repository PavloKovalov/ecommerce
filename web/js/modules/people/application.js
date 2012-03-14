define([
  'Underscore',
  'Backbone',
  'modules/people/router', // Request router.js
], function(_, Backbone, Router ){
	if (!window.console) {
		window.console = {
		log: function(){
				return false;
			}
		};
	}

	var initialize = function(){
		Router.initialize();
	}

	return { 
		initialize: initialize
	};
});