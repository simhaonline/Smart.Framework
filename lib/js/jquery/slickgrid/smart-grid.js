
// JS: Smart Grid Object (SlickGrid + NavPager)
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2016.09.15

// DEPENDS: jQuery, jQuery-Growl, SmartJS_CoreUtils, SmartJS_BrowserUtils, jQuery SlickGrid, jQuery SimplePagination

var Smart_Grid = function(gridID, infoListID, jsonURL, cookieStorePrefix, sortColumn, sortDir, sortType, formNameID, showLoadNotification, notifCssGrowlClass, evcode) { // OBJECT-CLASS

// -> dynamic (new)

var _class = this; // self referencing

//-- SETTINGS
//gridID = 'MyGrid'; 				// the HTML ID of the Grid Element (div)
//infoListID = 'MyListInfo'; 		// the HTML ID of the InfoList Element
//jsonURL = 'data/grid/json';		// the URL to Json Data to load into Grid
//cookieStorePrefix = 'myCookie';	// the Cookie name to be used to save Grid status
//sortColumn = 'id'; 				// sorting field;
//sortDir = 'ASC'; 					// sorting direction: ASC / DESC
//sortType = 'numeric'; 			// sorting type: 'text' / 'numeric';
//formNameID = 'filtering'; 		// filter form HTML ID or null
//--

//-- PUBLIC-PROTECTED (read-only) VARS
this.grid = null;
this.navPager = null;
this.data = [];
//--

//-- PRIVATE
var itemsPerPage = 1;
var itemsTotal = 0;
var crrOffset = 0;
var cookieURL = '' + cookieStorePrefix + '_URL';
var cookieWidths = '' + cookieStorePrefix + '_Wdts';
var crrSavedURL = '';
var clientSort = false;
//--

//--
this.resetGrid = function() {
	//--
	SmartJS_BrowserUtils.setCookie(cookieURL, '&', false, '/'); // delete cookie is not good enough because requires a page refresh, thus the solution is to change cookie value ...
	//--
	crrSavedURL = '';
	//--
} //END FUNCTION
//--

//--
this.reloadGrid = function(yredirect) {
	//--
	if((typeof yredirect != 'undefined') && (yredirect != '') && (yredirect !== null)) {
		self.location = '' + yredirect;
	} else {
		self.location = self.location; // self.location.reload(false); does not work
	} //end if
	//--
} //END FUNCTION
//--

//--
this.loadGridData = function(offs) {
	//--
	if(_class.grid === null) {
		alert('ERROR: Smart_Grid :: Grid was not initialized ... use Smart_Grid.initGrid(columns, options); to init this grid first !');
		return;
	} //end if
	//--
	if(showLoadNotification !== false) {
		SmartJS_BrowserUtils.Overlay_Show('<div align="center"><b>... loading data ...</b></div>', '', notifCssGrowlClass);
	} //end if
	//--
	var fdata = '';
	if((formNameID !== null) && (formNameID != '')) {
		fdata = '' + $('#' + formNameID).serialize();
	} //end if
	//--
	var crrCookieURL = SmartJS_BrowserUtils.getCookie(cookieURL);
	//--
	if(location.hash) { // ex: #!&id=test
		crrSavedURL = '' + location.hash.substring(2); // remove #! from hash
		//location.hash=''; // this leaves the # at the end and breaks self.location = ...
		var clean_uri = '' + location.protocol + '//' + location.host + location.pathname + location.search; // location.host returns also host and also the port
		try {
			self.history.replaceState({}, document.title, '' + clean_uri);
		} catch(err) {
			self.location = '' + clean_uri;
		} //end try catch
	} else if((crrSavedURL === '') && (crrCookieURL != '')) {
		crrSavedURL = '' + crrCookieURL;
	} else {
		crrSavedURL = 'sortby=' + encodeURIComponent(sortColumn) + '&sortdir=' + encodeURIComponent(sortDir) + '&sorttype=' + encodeURIComponent(sortType) + '&ofs=' + parseInt(offs) + '&' + fdata;
	} //end if else
	//--
	SmartJS_BrowserUtils.setCookie(cookieURL, crrSavedURL, false, '/');
	//--
	var crrURL = '' + jsonURL + '&' + crrSavedURL;
	//--
	SmartJS_BrowserUtils.Ajax_XHR_Request_From_URL(crrURL, 'POST', 'json').done(function(msg) { // {{{JQUERY-AJAX}}}
		//--
		if((msg.hasOwnProperty('status')) && (msg.status === 'OK') && (msg.hasOwnProperty('rowsList')) && (msg.hasOwnProperty('crrOffset')) && (msg.hasOwnProperty('itemsPerPage')) && (msg.hasOwnProperty('totalRows'))) {
			//--
			_class.data = msg.rowsList;
			crrOffset = parseInt(msg.crrOffset);
			itemsPerPage = parseInt(msg.itemsPerPage);
			itemsTotal = parseInt(msg.totalRows);
			sortColumn = '' + msg.sortBy;
			sortDir = '' + msg.sortDir;
			sortType = '' + msg.sortType;
			clientSort = false;
			if(msg.hasOwnProperty('clientSort')) { // if the server-side did not provide the sorting
				if(msg.clientSort === sortColumn) {
					clientSort = true;
				} //end if
			} //end if
			//--
			if((formNameID !== null) && (formNameID != '')) {
				if(msg.hasOwnProperty('filter')) {
					$.each(msg.filter, function(index, value) {
						try {
							$('#' + formNameID).find(':input[name=' + encodeURIComponent(index) + ']').val(value); // https://api.jquery.com/input-selector/
						} catch(err) {
							console.log('WARNING: Smart Grid Failed to Set a Filter value: [' + value + '] on Control: [' + index + ']' + '\nDetails: ' + err);
						} //end try catch
					});
				} //end if
			} //end if
			//--
			if(itemsPerPage <= 0) {
				alert('ERROR: Smart_Grid :: Invalid Value for Smart_Grid.itemsPerPage ... Must be > 0 !');
			} else {
				if(_class.navPager !== null) {
					_class.navPager.pagination('updateItemsOnPage', itemsPerPage);
					_class.navPager.pagination('updateItems', itemsTotal);
					_class.navPager.pagination('drawPage', (parseInt(crrOffset / itemsPerPage) + 1));
					_class.navPager.pagination('redraw');
				} //end if
			} //end if else
			//-- sort
			if(clientSort === true) { // if this is not already done via server-side, do it client-side
				if(sortType === 'numeric') {
					_class.data.sort(SmartJS_CoreUtils.numericSort(sortColumn));
				} else {
					_class.data.sort(SmartJS_CoreUtils.textSort(sortColumn));
				} //end if else
				if(sortDir === 'DESC') {
					_class.data.reverse(); // this is made server-side
				} //end if else
			} //end if
			//--
			_class.grid.invalidate();
			_class.grid.removeAllRows();
			if(sortDir === 'DESC') {
				_class.grid.setSortColumn(sortColumn, false);
			} else {
				_class.grid.setSortColumn(sortColumn, true);
			} //end if else
			_class.grid.setData(_class.data);
			_class.grid.render();
			//--
			if((infoListID !== null) && (infoListID != '')) {
				$('#' + infoListID).text('' + crrOffset + ' - ' + parseInt(crrOffset + itemsPerPage) + ' / ' + itemsTotal);
			} //end if
			//--
			if((typeof evcode != 'undefined') && (evcode != 'undefined') && (evcode != null) && (evcode != '')) {
				try {
					eval('(function(){ ' + evcode + ' })();'); // sandbox
				} catch(err) {
					alert('ERROR: JS-Eval Error on Smart Grid CallBack Function' + '\nDetails: ' + err);
				} //end try catch
			} //end if
			//--
			SmartJS_BrowserUtils.Overlay_Hide();
			//--
		} else {
			//--
			SmartJS_BrowserUtils.alert_Dialog('ERROR: Smart_Grid :: Invalid Data Format while trying to get Data via AJAX !' + '<hr>' + 'Details:<br>' + msg.status + ': ' + msg.error, '', 'ERROR', 550, 225);
			//--
			SmartJS_BrowserUtils.Overlay_Hide();
			//--
		} //end if else
		//--
	}).fail(function(msg) {
		//--
		SmartJS_BrowserUtils.alert_Dialog('ERROR: Smart_Grid :: Invalid Server Response via AJAX !' + '<hr>' + msg.responseText, '', 'ERROR', 750, 425);
		//--
		SmartJS_BrowserUtils.Overlay_Hide();
		//--
	});
	//--
} //END FUNCTION
//--

//--
this.initGrid = function(columns, options) {
	//--
	_class.grid = new Slick.Grid($('#' + gridID), _class.data, columns, options);
	//--
	var theSavedWidths = SmartJS_BrowserUtils.getCookie(cookieWidths);
	if(theSavedWidths != '') {
		var arrSavedWidths = theSavedWidths.split(';');
		$.each(columns, function(index, value){
			//alert(columns[index].width);
			var theCrrWidth = parseInt(arrSavedWidths[index]);
			if(theCrrWidth < 5) {
				theCrrWidth = 5
			} else if(theCrrWidth > 1500) {
				theCrrWidth = 1500
			} //end if else
			columns[index].width = theCrrWidth;
		});

	} //end if
	//--
	_class.grid.autosizeColumns();
	//--
	_class.grid.onColumnsResized = function(colWidths) {
		var savedWidths = '';
		for (var j = 0; j < colWidths.length; j++) {
			savedWidths += colWidths[j] + ';';
		} //end for
		SmartJS_BrowserUtils.setCookie(cookieWidths, savedWidths, false, '/');
	};
	//--
	_class.grid.onSort = function(sortCol, sortAsc) {
		//--
		sortColumn = sortCol.field;
		//--
		if(sortCol.sortNumeric) {
			sortType = 'numeric';
		} else {
			sortType = 'text';
		} //end if else
		//--
		if(sortAsc !== true) {
			sortDir = 'DESC';
		} else {
			sortDir = 'ASC';
		} //end if
		//--
		_class.loadGridData(0);
		//--
	};
	//--
} //END FUNCTION
//--

//--
this.initNavPager = function(pager_id) {
	//--
	_class.navPager = $('#' + pager_id);
	//--
	_class.navPager.pagination({
		cssStyle: 'light-theme',
		displayedPages: 10,
		edges: 3,
		onPageClick: function(pageNumber, event) {
			if(typeof event != 'undefined') {
				if(event.type == 'click') {
					crrOffset = parseInt((pageNumber - 1) * itemsPerPage);
					_class.loadGridData(crrOffset);
				} //end if
			} //end if
			return false;
		},
		itemsOnPage: itemsPerPage,
		items: itemsTotal
	});
	//--
} //END FUNCTION
//--

} //END OBJECT-CLASS
//==

/* Server-Side Data Definition:
{
	"status":"OK",
	"crrOffset":0,
	"itemsPerPage":25,
	"sortBy":"id",
	"sortDir":"ASC",
	"sortType":"numeric", // or "text"
	"filter":{
		"id":"",
		"name":"",
		//... populate the rest of filters
	},
	"totalRows":5000,
	"rowsList":[
		{
			"id":"1",
			"name":"Name 1"
		},
		{
			"id":"2",
			"name":"Name 2"
		},
		// ...
	]
}
*/

// #END
