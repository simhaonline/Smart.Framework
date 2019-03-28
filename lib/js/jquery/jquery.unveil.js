/**
 * jQuery Unveil
 * A very lightweight jQuery plugin to lazy load images
 * https://github.com/luis-almeida
 * Licensed under the MIT license.
 * Copyright 2013 Luís Almeida
 *
 * (c) 2019 unix-world.org
 * r.20190328
 * contains fixes by unixman:
 * 	- optimized for latest jquery 3.3.1
 * 	- replaced 'threshold' option with a complex object option {threshold:0, attribute:''}
 */

;(function($) {

	$.fn.unveil = function(options, callback) {

		var defaults = {
			threshold: 0, // image offset treshold : INTEGER+
			attribute: '' // image attribute ; default is: 'data-src' with fallback
		};

		options = $.extend({}, defaults, options);

	//	var retina = window.devicePixelRatio > 1; // unused
		var $w = $(window);
		var th = Math.ceil(options.threshold > 0 ? options.threshold : 0) || 0;
		var dataDefAttr = 'data-src';
		var dataAttr = String(dataDefAttr);
		if(defaults.attribute) {
			defaults.attribute = String(defaults.attribute || '');
			if(defaults.attribute.indexOf('data-') === 0) {
				dataAttr = String(defaults.attribute);
			}
		}
		var images = this;
		var loaded;

		this.one('unveil', function(){
			var source = $(this).attr(String(dataAttr)) || '';
			if(dataAttr != dataDefAttr) {
				if(!source) {
					source = $(this).attr(String(dataDefAttr)) || '';
				}
			}
			var src = $(this).attr('src') || '';
			if((source) && (source != src)) {
				$(this).attr('src', String(source));
				if(typeof callback === 'function') {
					callback.call(this);
				}
			}
		});

		function unveil() {
			var inview = images.filter(function(){
				var $e = $(this);
				if($e.is(':hidden')) {
					return;
				}
				var wt = $w.scrollTop(),
					wb = wt + $w.height(),
					et = $e.offset().top,
					eb = et + $e.height();
				var shouldLoad = (eb >= wt - th && et <= wb + th);
				//console.log(shouldLoad, 'eb='+eb, 'wt='+wt, 'th='+th, 'et='+et, 'wb='+wb);
				return shouldLoad;
			});
			loaded = inview.trigger('unveil');
			images = images.not(loaded);
		}
		$w.on('scroll.unveil resize.unveil lookup.unveil', unveil);
		unveil();
		return this;
	};

})(window.jQuery);

// #END
