
// jQuery Tabs 1.0
// (c) 2016 unix-world.org
// r.160904
// based on: http://www.jacklmoore.com/notes/jquery-tabs/

// Depends: jQuery, SmartJS_CoreUtils, SmartJS_CryptoHash, SmartJS_BrowserUtils
// TODO:
	// - if caching enabled and tab loaded don't load it again
	// finalize load by ajax error and load bar img


var SmartSimpleTabs = new function() { // START CLASS

	this.initTabs = function(tabs_id, selected) {

		var theTabsEl = $('#' + tabs_id);

		theTabsEl.addClass('simple_tabs').data('tabs-init', true); // apply tab styles

		$('#' + tabs_id + ' div:first ul:first').each(function(){

			var $exttabs = []; // register external tabs if any

			var $links = $(this).find('a'); // find tab header links

			// Hide the remaining content
			$links.each(function(idx, el) {
				$(this).addClass('simple_tabs');
				var crrSha1 = '';
				var crrHash = '' + this.hash;
				var CrrHref = '' + this.href;
				if(crrHash) {
					$(crrHash).data('tabs-mode', 'internal').hide();
				} else {
					crrSha1 = tabs_id + '__AjxExt__' + SmartJS_CryptoHash.sha1(CrrHref);
					$exttabs.push([CrrHref, crrSha1]); // build references to external tabs
					this.href = '#' + crrSha1;
					theTabsEl.append('<div id="' + crrSha1 + '" data-tabs-mode="external" data-tabs-loaded="" data-tabs-url="' + CrrHref + '">... loading ...</div>');
					$('#' + crrSha1).hide();
				} //end if
			});

			// If the location.hash matches one of the links, use that as the active tab.
			// If no match is found, use the first link as the initial active tab.
			var $active = $($links.filter('[href="'+location.hash+'"]')[0] || $links[0]);
			$active.addClass('simple_tabs_active');
			activateTab($active[0].hash);

	//console.log($exttabs);

			// Bind the click event handler
			$(this).on('click', 'a', function(e){

				// Make the old tab inactive.
				$active.removeClass('simple_tabs_active');
				$($active[0].hash).hide();

				// Update the variables with the new link and content
				$active = $(this);
				$active.addClass('simple_tabs_active');

				activateTab(this.hash);

				// Prevent the anchor's default click action
				e.preventDefault();

			});

		});

	// xhr on fail: SmartJS_BrowserUIUtils.DialogAlert('<h1>WARNING: Asyncronous Load Timeout or URL is broken !</h1>', '$(\'#smartframeworkcomponents_jquery_tabs_loader\').remove();', 'TAB #' + (parseInt($(ui.tab).index()) + 1) + ' :: ' + $(ui.tab).text());

		return theTabsEl;

	} //END FUNCTION

	var activateTab = function(the_hash) {
		//--
		var $content = $(the_hash);
		//--
		if($content.data('tabs-mode') == 'external') {
			$content.show();
			$content.empty().html('...loading...');
			if($content.data('tabs-url') != '') {
				var ajx = SmartJS_BrowserUtils.Ajax_XHR_Request_From_URL($content.data('tabs-url'), 'GET', 'html');
				ajx.done(function(msg) { // instead of .success() (which is deprecated or removed from newest jQuery)
					$content.empty().html(''+msg);
				}).fail(function(msg) { // instead of .error() (which is deprecated or removed from newest jQuery)
					alert('Tab load failed');
				});
			} //end if
		} else if($content.data('tabs-mode') == 'internal') {
			$content.show();
		} else {
			console.log('Failed to activate Tab: ' + the_hash + ' / Data: ' + $content.data('tabs-mode'));
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS

// END
