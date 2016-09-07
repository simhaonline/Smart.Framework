
// jQuery Tabs 1.0
// (c) 2016 unix-world.org
// r.160907
// based on: http://www.jacklmoore.com/notes/jquery-tabs/

// Depends: jQuery, SmartJS_CoreUtils, SmartJS_CryptoHash, SmartJS_BrowserUtils


var SmartSimpleTabs = new function() { // START CLASS


	this.initTabs = function(tabs_id, prevent_reload, selected) {
		//-- get tabs element
		var theTabsEl = $('#' + tabs_id);
		//-- apply tab styles
		theTabsEl.removeAttr('style').addClass('simple_tabs_container').data('tabs-active', 'yes');
		//-- fix from: ' div:first ul:first'
		$('#' + tabs_id + ' ul:first').removeAttr('style').addClass('simple_tabs_head').each(function(){
			//-- process tabs
			var $exttabs = []; // register external tabs if any
			var $links = $(this).find('a'); // find tab header links
			var crr_sel_tab_by_id = '' + location.hash; // if hash provided, use it to select tab
			var crr_sel_tab_id = 0;
			var crr_tab = 0;
			//--
			$links.each(function(idx, el){
				$(this).removeAttr('style').addClass('simple_tabs_inactive');
				var crrHash = '' + this.hash;
				var CrrHref = '' + this.href;
				if(crrHash) {
					$(crrHash).data('tabs-num', crr_tab).data('tabs-mode', 'internal').removeAttr('style').addClass('simple_tabs_content').hide();
				} else {
					var crrSha1 = tabs_id + '__AjxExt__' + SmartJS_CryptoHash.sha1(CrrHref);
					crrHash = '#' + crrSha1;
					this.href = crrHash;
					$exttabs.push([CrrHref, crrSha1]); // build references to external tabs
					theTabsEl.append('<div id="' + crrSha1 + '" class="simple_tabs_content" data-tabs-num="' + crr_tab + '" data-tabs-mode="external" data-tabs-loaded="" data-tabs-url="' + CrrHref + '">... loading ...</div>');
					$('#' + crrSha1).hide();
				} //end if
				if(crr_sel_tab_id === selected) {
					if(!crr_sel_tab_by_id) {
						crr_sel_tab_by_id = crrHash; // update selected tab by ID only if not already selected by hash
					} //end if
				} //end if
				crr_sel_tab_id++;
				crr_tab++;
			});
			//--
			//console.log($exttabs);
			//-- if the location.hash matches one of the links, use that as the active tab ; if no match is found, use the first link as the initial active tab.
			var $active = $($links.filter('[href="'+crr_sel_tab_by_id+'"]')[0] || $links[0]);
			$active.addClass('simple_tabs_active');
			displayTabContent($active[0].hash, prevent_reload);
			//-- bind the click event handler
			$links.each(function(idx, el){
				//--
				$(this).click(function(evt){
					//-- prevent the anchor's default click action
					evt.preventDefault();
					//-- test if tabs active
					if(theTabsEl.data('tabs-active') != 'yes') {
						return;
					} //end if
					//-- make the old tab inactive
					$active.removeClass('simple_tabs_active');
					$($active[0].hash).hide();
					//-- update the variables with the new link and content
					$active = $(this);
					$active.addClass('simple_tabs_active');
					//-- activate tab
					displayTabContent(this.hash, prevent_reload);
					//--
				});
				//--
			});
			//--
		});
		//--
		return theTabsEl;
		//--
	} //END FUNCTION


	this.activateTabs = function(tabs_id, activation) {
		//-- get tabs element
		var theTabsEl = $('#' + tabs_id);
		//-- apply tab styles
		if(activation === false) {
			theTabsEl.data('tabs-active', 'no');
		} else {
			theTabsEl.data('tabs-active', 'yes');
		} //end if else
		//--
		$('#' + tabs_id + ' ul:first').each(function(){
			//--
			var $links = $(this).find('a'); // find tab header links
			//--
			$links.each(function(idx, el){
				//--
				if(activation === false) {
					$(this).addClass('simple_tabs_disabled');
				} else {
					$(this).removeClass('simple_tabs_disabled');
				} //end if else
				//--
			});
			//--
		});
		//--
		return theTabsEl;
		//--
	} //END FUNCTION


	var displayTabContent = function(the_hash, prevent_reload) {
		//--
		var $content = $(the_hash);
		//--
		if($content.data('tabs-mode') == 'external') {
			//--
			$content.show();
			//--
			if($content.data('tabs-url') != '') {
				//--
				if(($content.data('tabs-loaded') != 'yes') || (prevent_reload !== true)) {
					//--
					$content.empty().html('<div class="simple_tabs_loader"><img src="' + SmartJS_BrowserUtils_LoaderImg + '" alt="... loading Tab data ..."></div>');
					//--
					setTimeout(function() {
						var ajx = SmartJS_BrowserUtils.Ajax_XHR_Request_From_URL($content.data('tabs-url'), 'GET', 'html');
						ajx.done(function(msg) { // instead of .success() (which is deprecated or removed from newest jQuery)
							$content.data('tabs-loaded', 'yes').empty().html('' + msg);
						}).fail(function(msg) { // instead of .error() (which is deprecated or removed from newest jQuery)
							SmartJS_BrowserUIUtils.DialogAlert('<h1>WARNING: Asyncronous Load Timeout or URL is broken !</h1>', '$(\'' + the_hash + '\').empty();', 'TAB #' + ' :: ');
						});
					}, 500);
					//--
				} //end if else
				//--
			} //end if
			//--
		} else if($content.data('tabs-mode') == 'internal') {
			//--
			$content.show();
			//--
		} else {
			//--
			console.log('Failed to activate Tab: ' + the_hash + ' / Data-Mode: ' + $content.data('tabs-mode'));
			//--
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS


// END
