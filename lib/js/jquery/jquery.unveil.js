/**
 * jQuery Unveil
 * A very lightweight jQuery plugin to lazy load images
 * https://github.com/luis-almeida
 * Licensed under the MIT license.
 * Copyright 2013 Luís Almeida
 *
 * (c) 2019 unix-world.org
 * Contains many fixes and portions of code optimized for latest jquery 3.3.1 by unixman
 * r.20190320
 */

;(function($) {

	$.fn.unveil = function(threshold, callback) {

		var $w = $(window);
		var th = threshold || 0;
	//	var retina = window.devicePixelRatio > 1; // unused
		var images = this;
		var loaded;

		this.one('unveil', function(){
			var source = $(this).attr('data-src') || '';
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
