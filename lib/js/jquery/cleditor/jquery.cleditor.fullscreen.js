
// v.150729

(function($) {

	//Style for fullscreen mode
	var fullscreen = 'display:block; position:fixed; left:5px; top:20px; width: ' + (parseInt($(window).width()) - 20) + 'px; height: ' + (parseInt($(window).height()) - 40) + 'px; z-index: 2147403000;';
	var fullscreenAreaIframe = 'width:98%; height:92%;';
	var style_main = '';
	var style_iframe = '';
	var style_area = '';

	// Define the fullscreen button
	$.cleditor.buttons.fullscreen = {
		name: 'fullscreen',
		image: 'fullscreen.png',
		title: 'Fullscreen',
		command: '',
		popupName: '',
		popupClass: '',
		popupContent: '',
		getPressed: fullscreenGetPressed,
		buttonClick: fullscreenButtonClick,
	};

	// Add the button to the default controls before the bold button
	$.cleditor.defaultOptions.controls = $.cleditor.defaultOptions.controls.replace("source", "source | fullscreen");

	function fullscreenGetPressed(data) {
		//--
		return data.editor.$main.hasClass('fullscreen');
		//--
	} //END FUNCTION

	function fullscreenButtonClick(e, data) {
		//--
		var main = data.editor.$main;
		var iframe = data.editor.$frame;
		var area = data.editor.$area;
		//--
		if(main.hasClass('fullscreen')) {
			//--
			if(typeof SmartJS_BrowserUtils != 'undefined') {
				SmartJS_BrowserUtils.Overlay_Hide();
			} //end if
			//--
			main.attr('style', style_main).removeClass('fullscreen');
			iframe.attr('style', style_iframe); //.removeClass('fullscreenAreaIframe');
			area.attr('style', style_area); //.removeClass('fullscreenAreaIframe');
			//--
		} else {
			//--
			style_main = main.attr('style');
			style_iframe = iframe.attr('style');
			style_area = area.attr('style');
			//--
			main.attr('style', fullscreen).addClass('fullscreen');
			fullscreenAreaIframe = 'width:' + parseInt(main.width() - 5) + 'px; height:' + parseInt(main.height() - 30) + 'px;';
			iframe.attr('style', fullscreenAreaIframe); //.removeClass('fullscreen');
			area.attr('style', fullscreenAreaIframe); //.removeClass('fullscreen');
			//--
			if(typeof SmartJS_BrowserUtils != 'undefined') {
				SmartJS_BrowserUtils.Overlay_Show();
				SmartJS_BrowserUtils.Overlay_Clear();
			} //end if
			//--
		} //end if else
		//--
		area.show(); // force refresh buttons
		area.hide();
		iframe.show();
		data.editor.focus();
		//--
		return false;
		//--
	} //END FUNCTION

})(jQuery);
