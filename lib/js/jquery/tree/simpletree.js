// jQuery Simple Tree v.2013-12-06
// https://github.com/innoq/simpletree
// (c) 2013 innoQ Deutschland GmbH
// License: Apache License, Version 2.0

// modified by unixman, v.180521.1401

(function($) {

"use strict";

function CollapsibleTree(list, options) {

	var self = this;

	options = options || {};
	if(!options.signCollapsed) {
		options.signCollapsed = '▸';
	}
	if(!options.signExpanded) {
		options.signExpanded = '▾';
	}

	list = list.jquery ? list : $(list);
	list.addClass("simpletree-tree").on("click", "span.simpletree-toggle", function() {
		var context = { context: this, instance: self };
		return self.onToggle.apply(context, arguments);
	});
	list.attr('data-simpletree-sign-collapsed', String(options.signCollapsed));
	list.attr('data-simpletree-sign-expanded', String(options.signExpanded));

	if(!options.nocheck) {
		list.on("change", "input:checkbox", this.onChange);
	}

	$("li:has(ul)", list).prepend('<span class="simpletree-button simpletree-toggle">' + String(options.signExpanded) + '</span> ');
	$("li", list).not(":has(ul)").prepend('<span class="simpletree-notoggle">' + '&nbsp;' + '</span> ');

	var toggle = function(i, node) {
		var btn = $(node);
		self.toggle(btn, false);
	};
	$(".simpletree-toggle", list).each(toggle);
	$("input:checked").parents("li").children(".simpletree-toggle").each(toggle);

	list.data("simpletree", this);

}

CollapsibleTree.prototype.onToggle = function(ev) {
	var btn = $(this.context);
	this.instance.toggle(btn);
	// TODO: unselect hidden items?
};

CollapsibleTree.prototype.onChange = function(ev) {
	var checkbox = $(this);
	var active = checkbox.prop("checked");
	checkbox.closest("li").find("input:checkbox").prop("checked", active);
};

// `btn` is a jQuery object referencing a toggle button
// `animated` is passed through to `setState`
CollapsibleTree.prototype.toggle = function(btn, animated) {
	var item = btn.closest("li");
	var chkdata = item.attr('data-simpletree');
	//console.log('state=' + chkdata);
	var state = chkdata === 'collapsed' ? 'collapsed' : 'expanded';
	this.setState(item, state, animated);
};

// `item` is a jQuery object referencing the respective list item
// `state` is either "collapsed" or "expanded"
// `animated` (optional) can be used to suppress animations
CollapsibleTree.prototype.setState = function(item, state, animated) {
	animated = animated === false ? false : true;
	var collapse = state === "collapsed";
	var action = animated ? ["slideUp", "slideDown"] : ["hide", "show"];
	action = collapse ? action[0] : action[1];
	item.children("ul")[action]();
	item.attr('data-simpletree', collapse ? 'expanded' : 'collapsed');
	var list = item.parents('ul.simpletree-tree');
	if(list) {
		var attr_c = list.attr('data-simpletree-sign-collapsed');
		var attr_e = list.attr('data-simpletree-sign-expanded');
		item.children(".simpletree-toggle").text(collapse ? String(attr_c) : String(attr_e));
	}
};

// jQuery API wrapper
$.fn.simpletree = function(options) {
	this.each(function(i, node) {
		new CollapsibleTree(node, options);
	});
	return this;
};

}(jQuery));
