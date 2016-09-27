
// [LIB - SmartFramework / JS / Validate Input (Fields)]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.7.2 r.2016.09.27 / smart.framework.v.2.3

// DEPENDS: SmartJS_CoreUtils

//==================================================================
//==================================================================

//=======================================
// CLASS :: Validate Input (Fields)
//=======================================

// added support for Integer Numbers
// added support for Number of Decimals to Place (0..4)

var SmartJS_FieldControl = new function() { // START CLASS

// :: static


// Validate Input Field as Integer Number
this.validate_Field_Integer = function(yObjInputField, yAllowNegatives) {
	//--
	var tmp_Value = '';
	tmp_Value = SmartJS_CoreUtils.format_number_int(yObjInputField.value, yAllowNegatives);
	tmp_Value = '' + tmp_Value.toString();
	//--
	yObjInputField.value = tmp_Value;
	//--
} //END FUNCTION


// Validate Input Field as Decimal(1..4) Number
this.validate_Field_Decimal = function(yObjInputField, yDecimalsDigits, yAllowNegatives, yAddThousandsSeparator) {
	//-- inits
	var tmp_Value = '';
	if(yObjInputField.value == '') {
		tmp_Value = '0';
	} else {
		tmp_Value = '' + yObjInputField.value;
	} //end if
	tmp_Value = tmp_Value.toString();
	//-- remove all spaces
	tmp_Value = SmartJS_CoreUtils.stringReplaceAll(' ', '', tmp_Value);
	//-- detect and trick the decimal and thousands separators
	var regex_dot = /\./g;
	var have_dot = regex_dot.test(tmp_Value);
	if(have_dot === true) {
		tmp_Value = SmartJS_CoreUtils.stringReplaceAll(',', '', tmp_Value); // remove thousands separator (comma) because there is already a dot there as decimal separator there (dot)
	} else {
		tmp_Value = SmartJS_CoreUtils.stringReplaceAll(',', '.', tmp_Value); // replace the wrong decimal separator (comma) with the real decimal separator (dot)
	} //end if
	//-- real format the value as decimal
	tmp_Value = SmartJS_CoreUtils.format_number_dec(tmp_Value, yDecimalsDigits, yAllowNegatives);
	//--
	if(yAddThousandsSeparator === true) {
		yObjInputField.value = '' + SmartJS_CoreUtils.add_number_ThousandsSeparator(tmp_Value);
	} else {
		yObjInputField.value = '' + tmp_Value;
	} //end if else
	//--
} //END FUNCTION

} //END CLASS

//==================================================================
//==================================================================

// #END
