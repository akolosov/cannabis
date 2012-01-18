// Глобальные флаги, определяющие тип браузера
var isO				= (BrowserDetect.browser == "Opera");
var isO6			= isO && (BrowserDetect.version >= 6) && (BrowserDetect.version < 7);
var isO7			= isO && (BrowserDetect.version >= 7) && (BrowserDetect.version < 8);
var isO8			= isO && (BrowserDetect.version >= 8) && (BrowserDetect.version < 9);
var isO9			= isO && (BrowserDetect.version >= 9) && (BrowserDetect.version < 10);
var isIE			= (BrowserDetect.browser == "Explorer");
var isIE3			= isIE  && (BrowserDetect.version >= 3) && (BrowserDetect.version < 4);
var isIE4			= isIE  && (BrowserDetect.version >= 4) && (BrowserDetect.version < 5);
var isIE5			= isIE  && (BrowserDetect.version >= 5) && (BrowserDetect.version < 6);
var isIE6			= isIE  && (BrowserDetect.version >= 6) && (BrowserDetect.version < 7);
var isIE7			= isIE  && (BrowserDetect.version >= 7) && (BrowserDetect.version < 8);
var isOldIE			= (isIE3)  || (isIE4)  || (isIE5);
var isNS			= (BrowserDetect.browser == "Netscape");
var isNS3			= isNS && (BrowserDetect.version >= 3) && (BrowserDetect.version < 4);
var isNS4			= isNS && (BrowserDetect.version >= 4) && (BrowserDetect.version < 5);
var isNS5			= isNS && (BrowserDetect.version >= 5) && (BrowserDetect.version < 6);
var isNS6			= isNS && (BrowserDetect.version >= 6) && (BrowserDetect.version < 7);
var isNS7			= isNS && (BrowserDetect.version >= 7) && (BrowserDetect.version < 8);
var isMZ			= (BrowserDetect.browser == "Mozilla") || (BrowserDetect.browser == "Firefox");
var isMZ1			= isMZ && (BrowserDetect.version >= 1) && (BrowserDetect.version < 2);
var isMZ2			= isMZ && (BrowserDetect.version >= 2) && (BrowserDetect.version < 3);
var isMZ3			= isMZ && (BrowserDetect.version >= 3) && (BrowserDetect.version < 4);
var isKonq			= (BrowserDetect.browser == "Konqueror");
var isSafari		= (BrowserDetect.browser == "Safari");
var isDOM			= document.getElementById;
if (!_calendar) {
	var _calendar		= null;
}
if (!_timeout) {
	var _timeout		= 0;
}
if (!_limit) {
	var _limit			= 1;
}
var _ajaxURI			= null;

function defineTextElements(elementName) {
	if (isMZ || isO8) {
		this.top = $(elementName).scrollTop;
	}
	if ((!isO) || isO8) {
		this.elementText = $F(elementName).replace(/\r/g, "");
		this.selStart = $(elementName).selectionStart;
		this.selEnd = $(elementName).selectionEnd;
		this.selBefore = $F(elementName).substr(0, this.selStart);
		this.selAfter = $F(elementName).substr(this.selEnd);
		this.selText = $F(elementName).substr(this.selStart, this.selEnd - this.selStart);
		this.elementPreparedText = this.selBefore + this.selText + this.selAfter;
	}
}

function insertText(elementName, aText) {
	defineTextElements(elementName);
	if (aText) {
		aText = aText.replace("%%", this.selText);

		if ((isO) && (!isO8)) {
			$(elementName).value = $F(elementName) + aText;
		} else {
			$(elementName).value = this.selBefore + aText + this.selAfter;
		}
	}
}

function setCookie(cookieName, cookieValue) {
	document.cookie = cookieName+"="+escape(cookieValue);
	return true;
}

function confirmIt(url, target, top) {
	if (confirm('Вы точно уверены?')) {
		if (target == "_top") {
				document.location.href = url;
		} else {
				window.opener.document.location.href = url;
		}
	}
	return true;
}

function confirmItMessage(msg, url) {
	if (confirm(msg)) {
		document.location.href = url;
	}
	return true;
}

function openWindow(url, target) {
	window.open(url);
	return true;
}


function hideIt(elementName) {
	if ($(elementName)) {
		if (($(elementName).style.visibility == "") || ($(elementName).style.visibility == "hidden")) {
			$(elementName).style.display = "block"; 
			$(elementName).style.visibility = "visible";
		} else {
			$(elementName).style.display = "none";
			$(elementName).style.visibility = "hidden";
		}
	}
	return true;
}

function hideItAndSetCookie(elementName) {
	if ($(elementName)) {
		if (($(elementName).style.visibility == "") || ($(elementName).style.visibility == "hidden")) {
			$(elementName).style.display = "block"; 
			$(elementName).style.visibility = "visible";
			setCookie(elementName+"_visibility", "true");
		} else {
			$(elementName).style.display = "none";
			$(elementName).style.visibility = "hidden";
			setCookie(elementName+"_visibility", "false");
		}
	}
	return true;
}

function showNow(elementName) {
	if ($(elementName)) {
		$(elementName).style.display = "block"; 
		$(elementName).style.visibility = "visible";
	}
	return true;
}

function hideNow(elementName) {
	if ($(elementName)) {
		$(elementName).style.display = "none";
		$(elementName).style.visibility = "hidden";
	}
	return true;
}

function changeImage(elementName, imageName) {
	if ($(elementName) && $(imageName)) {
		if (($(elementName).style.visibility == "") || ($(elementName).style.visibility == "hidden")) {
			$(imageName).src = "images/tree_expand.png"; 
		} else {
			$(imageName).src = "images/tree_collapse.png"; 
		}
	}
	return true;
}

function addCode(elementName, codeType) {
	switch (codeType) {
		case 'nextAction':
			insertText(elementName, '$this->setNextStep("%%");\n');
			break;

		case 'nextUser':
			insertText(elementName, '$this->setNextUser("%%");\n');
			break;

		default:
			break;
	}
	$(elementName).focus();
}
	
// This function gets called when the end-user clicks on some date.
function selected(cal, date) {
	cal.sel.value = date; // just update the date in the input field.
	if (cal.dateClicked) {
		cal.callCloseHandler();
	}
}

function closeHandler(cal) {
	cal.hide();
	cal.destroy();
	_calendar = null;
}

function showCalendar(id, format, showsTime, showsOtherMonths) {
	var el = document.getElementById(id);
	if (_calendar != null) {
		_calendar.callCloseHandler();
	} else {
		var cal = new Calendar(1, null, selected, closeHandler);
		// uncomment the following line to hide the week numbers
		// cal.weekNumbers = false;
		if (typeof showsTime == "string") {
			cal.showsTime = true;
			cal.time24 = (showsTime == "24");
		}
		if (showsOtherMonths) {
			cal.showsOtherMonths = true;
		}

		_calendar = cal;                  // remember it in the global var
		cal.setRange(2000, 2100);        // min/max year allowed.
		cal.create();
	}
	_calendar.setDateFormat(format);    // set the specified date format
	_calendar.parseDate(el.value);      // try to parse the text in field
	_calendar.sel = el;                 // inform it what input field we use

	// the reference element that we pass to showAtElement is the button that
	// triggers the calendar.  In this example we align the calendar bottom-right
	// to the button.
	_calendar.showAtElement(el.nextSibling, "Bl");        // show the calendar
	
	return false;
}

function $(name) {
	return document.getElementById(name);
}

function $F(name) {
	return document.getElementById(name).value;
}

function fixPNG(element) {
	//Если браузер IE версии 5.5-6
	if (/MSIE (5\.5|6).+Win/.test(navigator.userAgent))	{
		var src;
		
		if (element.tagName=='IMG') { //Если текущий элемент картинка (тэг IMG)
			if (/\.png$/.test(element.src)) { //Если файл картинки имеет расширение PNG
			  src = element.src;
			  element.src = "images/blank.gif"; //заменяем изображение прозрачным gif-ом
			}
		} else { //иначе, если это не картинка а другой элемент
		//если у элемента задана фоновая картинка, то присваеваем значение свойства background-шmage переменной src
			src = element.currentStyle.backgroundImage.match(/url\("(.+\.png)"\)/i);
			if (src) {
			  src = src[1]; //берем из значения свойства background-шmage только адрес картинки
			  element.runtimeStyle.backgroundImage="none"; //убираем фоновое изображение
			}
		}
		//если, src не пуст, то нужно загрузить изображение с помощью фильтра AlphaImageLoader
		if (src) {
			element.runtimeStyle.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "',sizingMethod='scale')";
		}
	}
}

function isValidDate(dateStr) {
	if (dateStr) {
		var datePat = /^(\d{1,2})(\/|-|\.)(\d{1,2})(\/|-|\.)(\d{4})$/;
		var matchArray = dateStr.match(datePat);
	
		if (matchArray == null) {
			window.alert("Пожалуйста введите дату в формате 'дд/мм/гггг'!");
			return false;
		}
	
		day = matchArray[1];
		month = matchArray[3];
		year = matchArray[5];
	
		if (month < 1 || month > 12) { // check month range
			window.alert("Месяц может быть в диапазоне от 01 до 12!");
			return false;
		}
	
		if (day < 1 || day > 31) {
			window.alert("День может быть в диапазоне от 01 до 31!");
			return false;
		}
	
		if ((month==4 || month==6 || month==9 || month==11) && day==31) {
			window.alert("В указаном месяце не 31 день!");
			return false;
		}
	
		if (month == 2) {
			var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
			if (day > 29 || (day==29 && !isleap)) {
				window.alert("В феврале " + year + " не " + day + " день!");
				return false;
			}
		}
	}
	return true;
}

function isValidDateTime(dateTimeStr) {
	if (dateTimeStr) {
		var dateTimePat = /^(\d{1,2})(\/|-|\.)(\d{1,2})(\/|-|\.)(\d{4})(\s)(\d{1,2})(\:)(\d{1,2})$/;
		var matchArray = dateTimeStr.match(dateTimePat);
	
		if (matchArray == null) {
			window.alert("Пожалуйста введите дату и время в формате 'дд/мм/гггг чч:мм'!");
			return false;
		}
	
		day = matchArray[1];
		month = matchArray[3];
		year = matchArray[5];
	
		hour = matchArray[7];
		minute = matchArray[9];

		if (month < 1 || month > 12) { // check month range
			window.alert("Месяц может быть в диапазоне от 01 до 12!");
			return false;
		}
	
		if (day < 1 || day > 31) {
			window.alert("День может быть в диапазоне от 01 до 31!");
			return false;
		}
	
		if ((month==4 || month==6 || month==9 || month==11) && day==31) {
			window.alert("В указаном месяце не 31 день!");
			return false;
		}
	
		if (month == 2) {
			var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
			if (day > 29 || (day==29 && !isleap)) {
				window.alert("В феврале " + year + " не " + day + " день!");
				return false;
			}
		}

		if (hour < 0 || hour > 23) {
			window.alert("Часы могут быть в диапазоне от 00 до 23!");
			return false;
		}

		if (minute < 0 || minute > 59) {
			window.alert("Минуты могут быть в диапазоне от 00 до 59!");
			return false;
		}
	
	}
	return true;
}

function showPopupWindow() {
	var win_id, options;
	var optionIndex = 0;

	if (arguments.length > 0) {
		if (typeof arguments[0] == "string" ) {
			win_id = arguments[0];
			optionIndex = 1;
		} else {
			win_id = arguments[0] ? arguments[0].id : null;
		}
	}

	if (!win_id) {
		win_id = "window_" + new Date().getTime();
	}

	if ((isOldIE)  || (typeof Prototype=='undefined') || (typeof Scriptaculous == 'undefined') || (typeof Window == 'undefined')) {
		document.location.href = arguments[optionIndex].url;
	} else {
		options = Object.extend ({
			className:		"dialog",
			resizable:		true,
			closable:		true,
			minimizable:	false,
			maximizable:	false,
			draggable:		true,
			wiredDrag:		true,
			center:			true,
			modal:			true,
			showEffect:		Element.show,
			hideEffect:		Element.hide,
			userData:		null,
			title:			"&nbsp;",
			url:			null,
			ajax:			true,
			onload:			Prototype.emptyFunction,
			width:			500,
			height:			500,
			destroyOnClose:	true,
			recenterAuto:	true,
			onClose:		Prototype.emptyFunction,
			onDestroy:		Prototype.emptyFunction
		}, arguments[optionIndex] || {});

		win = new Window(win_id, options);

		if (options.userData) {
			win.getContent().update(options.userData);
		}

		if ((options.ajax) && (options.url)) {
			win.setAjaxContent(options.url+'&ajax=true', { method: 'get', evalJSON: true, evalJS: true, evalScripts: true }, options.center, options.modal);
		} else if (options.center) {
			win.showCenter(options.modal);
		} else {
			win.show(options.modal);
		}

	}
}

function getCheckBoxesList(element) {
	var result = '';
	var checkboxes = $A(element.getElementsByTagName('input'));

	for (i = 0; i < checkboxes.length; i++) {
		if (checkboxes[i].checked) {
			result += checkboxes[i].value + ',';
		}
	}

	return result+'0';
}

function setCheckBoxesValue(element, value) {
	var result = '';
	var checkboxes = $A(element.getElementsByTagName('input'));

	for (i = 0; i < checkboxes.length; i++) {
		checkboxes[i].checked = value;
	}
}

function loadAJAX(element, url, params) {
	if (isOldIE) {
		document.location.href = url+'?'+params;
	} else {
		return (new Ajax.Updater(element, url, { method: 'get', parameters: params, evalJSON: true, evalJS: true, evalScripts: true } ));
	}
}

function reloadAJAX(element, url, params) {
	if (isOldIE) {
		window.location.reload();
	} else {
		if ((params) || (_ajaxURI)) {
			return loadAJAX(element, (url?url:'/index.php'), (params?params:_ajaxURI));
		}
	}
}
