Object.extend(Form.Methods, {
	validate: function(element) {
		hasErrors = false;
		$A(element.getElementsByTagName('input')).concat($A(element.getElementsByTagName('textarea')),
														 $A(element.getElementsByTagName('select'))).each(
			function(input) {
				validations = input.className.match(/isValid\w+/g);
				if (validations) {
					validations.each(function (validation) {
						if(!input[validation]()) {
							hasErrors = true;
							input.addClassName('hasError');
							if (Effect) {
								new Effect.Highlight(input.id);
							}
						} else {
							input.removeClassName('hasError');
						}
					});
				}
			}
		);

		if (hasErrors) {
			element.addClassName('formHasErrors');
			Form.Methods.validationErrorCallback(element);
			return false;
		} else {
			element.removeClassName('formHasErrors');
			return true;
		}
	},

	validationErrorCallback: function(element) {
		element.getElementsByClassName('hasError')[0].focus();
	},

	validateBeforeSubmit: function(element) {
		if ($(element).validate()) {
			$(element).submit();
			return true;
		} else {
			return false;
		}
	}

});

Object.extend(Form.Element.Methods, {
	setModified: function(element) {
		$(element).addClassName('modified');
	},

	setUnModified: function(element) {
		$(element).removeClassName('modified');
	},

	isModified: function(element) {
		return $(element).hasClassName('modified');
	},

	isRequired: function(element) {
		return $(element).hasClassName('isValidRequired');
	},

	isEmpty: function(element) {
		return $F(element) == '';
	},

	isNotEmpty: function(element) {
		return !$F(element) == '';
	},

	isValid: function(element) {
		idValid = true;
		element.className.match(/isValid\w{1,}/g).each(function(className) {
			if(!$(element)[className]()) idValid = false;
		});
		return idValid;
	},

	isValidEmpty: function(element) {
		return Form.Element.Methods.isEmpty(element);
	},

	isValidNotEmpty: function(element) {
		return Form.Element.Methods.isNotEmpty(element);
	},

	isValidRequired: function(element) {
		return Form.Element.Methods.isNotEmpty(element);
	},

	isValidBoolean: function(element) {
		return !!$F(element).match(/^(0|1|true|false)$/);
	},

	isValidEmail: function(element) {
		return !!$F(element).match(/(^[a-z]([a-z_\.]*)@([a-z_\.]*)([.][a-z]{2,})(([.][a-z]{2,})?)$)/i); 
	},

	isValidInteger: function(element) {
		return !!$F(element).match(/(^-?\d+$)/);
	},

	isValidNumeric: function(element) {
		return !!$F(element).match(/(^-?\d\d*[\.|,]\d*$)|(^-?\d\d*$)|(^-?[\.|,]\d\d*$)/);
	},

	isValidAplhaNumeric: function(element) {
		return !!$F(element).match(/^[_\-a-z0-9]+$/gi);
	},

	// 00.00.0000 00:00:00 to 31.12.9999 59:59:59
	isValidDatetime: function(element) {
		dt = $F(element).match(/^(\d{2})[-\.\/](\d{2})[-\.\/](\d{4})\s(\d{1,2}):(\d{1,2}):(\d{1,2})$/);
		return dt && !!(dt[3]<=9999 && dt[2]<=12 && dt[1]<=31 && dt[4]<=59 && dt[5]<=59 && dt[6]<=59) || false;
	},

	// 00.00.0000 to 31.12.9999
	isValidDate: function(element) {
		d = $F(element).match(/^(\d{2})[-\.\/](\d{2})[-\.\/](\d{4})$/);
		return d && !!(d[3]<=9999 && d[2]<=12 && d[1]<=31) || false;
	},

	// 00:00:00 to 59:59:59
	isValidTime: function(element) {
		t = $F(element).match(/^(\d{1,2}):(\d{1,2}):(\d{1,2})$/);
		return t && !!(t[1]<=24 && t[2]<=59 && t[3]<=59) || false;
	},

	// 0.0.0.0 to 255.255.255.255
	isValidIPv4: function(element) { 
		ip = $F(element).match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/);
		return ip && !!(ip[1]<=255 && ip[2]<=255 && ip[3]<=255 && ip[4]<=255) || false;
	}
});

Element.addMethods();
