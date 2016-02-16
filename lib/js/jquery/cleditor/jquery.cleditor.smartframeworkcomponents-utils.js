
// CL Editor - NetVision Utils
// v.2015.02.15
// (c) unixman, iradu@unix-world.org

function Smart_CLEditor_Activate_HTML_AREA(area_id) {
	var the_area = $('#' + area_id);
	return the_area.cleditor({
		width: the_area.width(),
		height: the_area.height(),
		docCSSFile: 'lib/js/jquery/cleditor/jquery.cleditor.smartframeworkcomponents.css'
	})[0];
} //END FUNCTION

function Smart_CLEditor_Remove_HTML_AREA(area_id) {
	var tmp_text = '';
	area_id.execCommand('selectall');
	tmp_text = area_id.selectedText();
	area_id.clear();
	area_id.$area.removeData("cleditor");
	area_id.$main.remove();
	area_id = '';
	delete(area_id);
	return tmp_text;
} //END FUNCTION

//#END
