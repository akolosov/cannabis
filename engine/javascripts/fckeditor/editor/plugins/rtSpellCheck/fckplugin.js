/*
	Real time spellchecker
	Interface to work with the Firefox 2 Spellchecker for textareas
  
  File Author:
  		Alfonso Martínez de Lizarrondo (Uritec) alfonso -at- uritec dot net

	version 0.1 - 18/10/2006
		it works
	version 0.2 - 21/10/2006 
		save the state using FCKStorage object (included here)
		FCKStorage upgraded to use Client Side Storage (Fx2)
	version 0.3 - 21/10/2006 
		FCKStorage upgraded to use IE userData Behavior (Persistance)
	version 0.4 - 20/11/2006
		if the button isn't present in the toolbar, then ignore previous state and remain disabled.
	version 0.5 - 10/03/2007
		updated to work with FCKeditor 2.4.x
		now the globalStorage should work in localhost
		updated the gecko version detection so it works with only 3 parts (eg 1.4.0)
	version 0.6 - 31/03/2007
		Added the protection proposed by Paul Neumeyer in FCKStorage.GetValue
		Clean up of Lint warnings

Usage:
1. 
	Extract the plugin file under the plugins directory so it ends up as editor/plugins/rtSpellCheck/fckplugin.js

2.
	Include the plugin in your config file:
	FCKConfig.Plugins.Add( 'rtSpellCheck' ) ;

3.
	Replace the default 'SpellCheck' button in your toolbar with 'rtSpellCheck'

That's all the changes required, it uses your skin button and current translations.

Now users with IE or Firefox<2.0 will get the previous behavior, no change for them.
But for Firefox 2 users will get the spellchecker underlines removed by default, and clicking the spellcheck button will toggle the state:
if it's enabled then Firefox will do the spellchecking and the browser context menu with spell suggestions will be available
if it's disabled then they get the same behavior as non Firefox 2 users.

The state of the button is preserved across sessions.
*/


/*	Start storage	*/
var FCKStorage = new Object() ;

/* Check for availability of the globalStorage object and check than we can use it */
if ( typeof(globalStorage) == "object")
{
	try
	{
		//	fails in file://
		var domain = document.location.host.toLowerCase() ;
		if ( domain.indexOf( '.' ) == -1)
			domain += '.localdomain' ;
		var storage = globalStorage[ domain ];
		FCKStorage.storage = storage;
	}
	catch(e) {}
}

/*
	According to the capabilities of each browser we'll define the proper internal functions
*/
if (typeof FCKStorage.storage != "undefined")
{
	/*
		Client Side Storage
		http://www.whatwg.org/specs/web-apps/current-work/#scs

		Available in Firefox 2.0
		fails in file://
		https://bugzilla.mozilla.org/show_bug.cgi?id=357323
	*/
	FCKStorage._setValue = function( name, value ) 
	{
		this.storage.setItem( name, value );
	};

	FCKStorage._getValue = function( name ) 
	{
		var oItem = this.storage.getItem( name ) ;
		if ( !oItem ) 
			return null ;
		return oItem.toString();
	};
	/* end of CSStorage */
} 
	else 
{
	var head = document.getElementsByTagName( 'HEAD' )[0] ;
	if ( typeof(head.addBehavior) != 'undefined' )
	{
		/* 
			Use IE Persistance
			http://msdn.microsoft.com/workshop/author/behaviors/reference/behaviors/userdata.asp
			Available in IE5
		*/
		// Create an element (doesn't have to be a real one, so let's create a really fake one
		var oStore = document.createElement( 'URITEC:STORAGE' ) ;
		// id to improve performance
		oStore.id = 'myStorage' ;
		// the magic part
		oStore.addBehavior( '#default#userData' ) ;
		// the element has to be present in the document (but the body isn't ready so we add it to the head
		head.appendChild( oStore ) ;
		// let's keep a reference
		FCKStorage.oStore = oStore ;

		// The functions.
		FCKStorage._setValue = function( name, value ) 
		{
			this.oStore.setAttribute(name, value) ;
			this.oStore.save( 'oXMLStore' ) ;
		};

		FCKStorage._getValue = function( name ) 
		{
			this.oStore.load( 'oXMLStore' ) ;
			return this.oStore.getAttribute( name ).toString();
		};
		/* end of IE persistance */
	} 
		else 
	{
		/* 
			then maybe flash? 
		*/

		/* 
			Cookies
			Better than nothing, but limited to 2K and they are sent back to the server.
		*/
		FCKStorage.getCookieVal = function(offset) {
			var endstr = document.cookie.indexOf(';', offset) ;
			if (endstr == -1)
				endstr = document.cookie.length ;
			return unescape(document.cookie.substring(offset, endstr)) ;
		};

		FCKStorage._getValue = function(name) {
			var arg = name + '=' ;
			var alen = arg.length ;
			var clen = document.cookie.length ;
			var i = 0;
			while (i < clen) {
				var j = i + alen;
				if (document.cookie.substring(i, j) == arg)
					return this.getCookieVal(j);
				i = document.cookie.indexOf(' ', i) + 1 ;
				if (i === 0) break ;
			}
			return null ;
		};

		FCKStorage._setValue = function(name, value) {
			var expdate = new Date();
			expdate.setTime(expdate.getTime() + (10*365 * 24 * 60 * 60 * 1000)) ; 

			document.cookie = name + '=' + escape(value) + '; expires=' + expdate.toGMTString() ;
		};
		/* end of cookie storage*/
	}
}



// Public functions:
FCKStorage.SetValue = function( module, name, value )
{
	this._setValue('FCK' + module + name, value) ;
};

// The internal functions always store a string!!!
// we'll try to return a boolean if possible or a string
FCKStorage.GetValue = function( module, name, defaultValue )
{
	try {
		var value = this._getValue('FCK' + module + name) ;
		if ( value === null ) 
			return defaultValue ;
		else
		{	
			if ( value == 'true' ) return true ;
			if ( value == 'false' ) return false ;
			// Return the string
			return value ;
		}
	} catch ( e ) {
		return defaultValue ;
	}
};

/* end storage */



/* Start utilities */

// Utility function to wrap a call to an object's function 
function hitch(obj, methodName) 
{
  return function() { obj[methodName].apply(obj, arguments); } ; 
}

// Get gecko version as a number rv:1.8.1 for Firefox 2.0 will give back 10801
function getGeckoVersion() 
{
	var oVersion = navigator.userAgent.match( /rv:(\d+).(\d+)(?:.(\d+))?/ ) ;
	var iMajor = parseInt(oVersion[1], 10);
	var iMinor = parseInt(oVersion[2], 10);
	var iRev = (oVersion.length>3 ? parseInt(oVersion[3], 10) : 0);
	return (iMajor*100 + iMinor) *100 + iRev ;
}

/* end utilities */



// Real time spell checker command
var rtSpellCheck = function(name) 
{ 
	this.Name = name; 
	this.storedMenu = null;
	// how was the last time?
	this.active = FCKStorage.GetValue(this.Name, 'active', false) ;

	// Refresh the status after the html has been loaded (or switching back from source mode)
	FCK.Events.AttachEvent( 'OnAfterSetHTML', hitch(this, 'Refresh') ) ;
};

// The button has been pressed, do our work
rtSpellCheck.prototype.Execute = function() 
{
	// Flip state
	this.active = !this.active;
	FCKStorage.SetValue(this.Name, 'active', this.active); //it's saved as a string

	this.Refresh();

	// Refresh toolbar icon
	FCKToolbarItems.GetItem( this.Name ).RefreshState() ;
};

// Make the proper changes to the document
rtSpellCheck.prototype.Refresh = function() 
{
	if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
		return ;

	// Check if the button is present in the toolbar:
	var oItem = FCKToolbarItems.GetItem( this.Name ) ;
	if (!(oItem && oItem._UIButton))
		this.active = false;
	
	// Groovy underlines
	FCK.EditorDocument.body.spellcheck = this.active ;

	if (this.active) {
		// Remove the context menu object so we get the browser default context menu
		this.storedMenu = FCK.EditorDocument._FCKContextMenu ;
		FCK.EditorDocument._FCKContextMenu = null ;
	} 
	else 
	{
		// Restore the context menu handling
		if (this.storedMenu !== null)
		{
			FCK.EditorDocument._FCKContextMenu = this.storedMenu ;
			this.storedMenu = null ;
		}
	}
};



// Manage the plugins' button behavior 
rtSpellCheck.prototype.GetState = function() 
{ 
	// Is it active?
	return (this.active ? FCK_TRISTATE_ON : FCK_TRISTATE_OFF) ; 
};




// Lets choose the proper spellchecker:
// rv:1.8.1 for Firefox 2.0
// it would be better to base it on the features, but as the body isn't loaded we can't try to check anything on it.
if ( FCKBrowserInfo.IsGecko && getGeckoVersion()>=10801)
{
	// Register the related command. 
	FCKCommands.RegisterCommand( 'rtSpellCheck', new rtSpellCheck( 'rtSpellCheck' ));

	// Create the "rtSpellCheck" toolbar button.
	FCKToolbarItems.RegisterItem( 'rtSpellCheck', new FCKToolbarButton( 'rtSpellCheck', FCKLang.SpellCheck , '', null, false, false, 13) ) ;
}
else
{
	// button that calls back to the original SpellCheck Command
	FCKToolbarItems.RegisterItem( 'rtSpellCheck'	, new FCKToolbarButton( 'SpellCheck', FCKLang.SpellCheck, null, null, false, false, 13 ) ) ;
}
