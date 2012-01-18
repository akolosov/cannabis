/****************
*  $Id: cms.js 99 2007-03-12 12:01:00Z wingedfox $
*  $HeadURL: https://svn.debugger.ru/repos/CompleteMenuSolution/tags/v0.5.12/cms.js $
*
*  The Complete Menu Solution project
*
*  @application Complete Menu Solution
*  @author Ilya Lebedev <ilya@lebedev.net>
*  @copyright (c) 2005-2006, Ilya Lebedev
*  @license Free for non-commercial use
*  @title Complete Menu Solution
*  @version $Rev: 99 $
*
******/
CompleteMenuSolution = function () {
  var self = this;
  /*
  *  Menu id
  *
  *  @type int
  *  @access private
  */
  var menuId = null;
  /*
  *  list of dependencies need to be checked before menu oreoaration
  *
  *  Record structure:
  *  0 - resource handler in global space (theme, modifier, etc)
  *  1 - resource name
  *  2 - optional load state
  *
  *  @type array
  *  @access private
  */
  var dependencies = [];
  /*
  *  List of active transitions
  *
  *  @type array
  *  @access private
  */
  var transitions = [];
  /*
  *  list of active modifiers
  *
  *  @type hash
  *  @access private
  */
  var modifiers = [];
  /*
  *  List of css classes used 
  *
  *  @type hash
  *  @access private
  */
  var cssClasses = {
    'root'         : 'CmsListMenu',
    'folder'       : 'CmsMenuItemFolder',
    'folderOpen'   : 'CmsMenuItemFolderExpanded',
    'folderClosed' : 'CmsMenuItemFolderCollapsed',
    'menuItem'     : 'CmsMenuItemFile',
    'evenLevel'    : 'CmsMenuItemEvenLevel',
    'oddLevel'     : 'CmsMenuItemOddLevel',
    'menuLevel'    : 'CmsMenuItemLevel'
  }
  /*
  *  Menu options
  *
  *  @type hash
  *  @access private
  */
  var menuOptions = { 'theme' : {
                        'name' : '',
                        'options' : {}
                      },
                      'transitions' : {
                      },
                      themeRootPath : null,       // if string - used as real path to the themes folder
                      maxDepth : 0,               // how deep menu would be converted. Useful in template definitions only.
                      maxOpenDepth : 0,           // how deep menu nodes will be open
                      forceSkipTransitions : false, // set true to skip transitions one time
                      interval : 10,               // transition interval
                      length : 100,               // transition length
                      openTimeout : 0,
                      closeTimeout: 0,
                      toggleMenuOnClick : 0,      // 3-state flag: 0 - don't toggle, 1 - toggle, 2 - open
                      closeSiblings : true,       // close same level submenus
                      incrementalConvert : true,  // convert tree by one level
                      handlers : {                // user's handlers
                        onOpen : [],
                        onClose : [],
                        onChangeState : []
                      },
                      stripCssClasses : {         // what classes should be removed from the nodes on conversion
                        'root' : [],
                        'ul'   : [],
                        'li'   : [],
                        'a'    : []
                      },
                                                  // CSS class, used as flag to force node open, applicable to <li>
                      flagOpenClass : cssClasses['folderOpen'],
                                                  // CSS class, used as flag to force node close, applicable to <li>
                                                  // has higher priority than flagFolderOpen
                      flagClosedClass : cssClasses['folderClosed'],
                      appendTemplateSuffix : false, // it's useful, when menu could contain the nested menu's
                                                    // mostly for template internals
                      dummy : null
                    };
  /*
  *  list of custom element properties
  *
  *  @type hash
  *  @access private
  */
  var keys = {
    'cmsSelf' : '__cmsSelf',       // set on the root 'ul', CMS object itself
    'openFlag' : '__isOpen',
    'interval' : '__interval',
    'timeout' : '__timeout',
    'isRoot' : '__isRoot',
    'isFolder' : '__isFolder',
    'parentNode' : '__parentNode',
    'submenu' : '__submenu',       // link to child 'ul' tag for li
                                   // array of child 'li' tags for ul
    'menuLevel' : '__menuLevel',
    'activator' : '__activator'    // link to 'a' tag
  }
  var ___________________________Publis____________________________;
  /*
  *  set single option in the menuOptions
  *
  *  @param string option name
  *  @param string option value
  *  @return boolean change state
  *  @access public
  */
  this.setMenuOption = function (n, v) {
    if (menuOptions[n] && typeof menuOptions[n] != typeof v) return false;
    menuOptions[n] = v;
    return true;
  }
  /*
  *  Initializes menu
  *
  *  @param string menu id
  *  @param hash menu options
  *  @access public
  */
  this.initMenu = function(mid, options) {
    menuId = mid;
    /*
    *  apply user options at the very beginning, to initialize theme and stuff
    */
    menuOptions.theme.merge(options.theme);
    if (options.themeRootPath) menuOptions.themeRootPath = options.themeRootPath;
    /*
    *  loading themes and stuff
    */
    loader.init(options);
    /*
    *  run menu conversion
    */
    convertMenuById();
  }
  /*
  *  Return path to theme or skin
  *
  *  @param boolean true if return path to skin
  *  @return string pathname
  *  @access public
  */
  this.getThemePath = function (skin) {
    if (!/^[-a-z0-9\/]*$/.test(name.toLowerCase())) return false;
    var sp = menuOptions.theme.name.split('/');
    return gluePath(menuOptions.themeRootPath?menuOptions.themeRootPath:gluePath(self.cmsRoot,'templates'),
                    (skin?menuOptions.theme.name:sp[0]));
  }
  /*
  *  Reinitializes submenu container
  *
  *  @param DOMNode li tag
  *  @access public
  */
  this.reinitSubmenu = function (el) {
    if (!el || !el.tagName) return;
    var omd = menuOptions.maxDepth;
    switch (el.tagName.toLowerCase()) {
//      case "ul" : convertMenuItem (el[keys['submenu']],el[keys['parentNode']][keys['menuLevel']]+1);
//                  break;
      case "li" : menuOptions.maxDepth = el[keys['parentNode']][keys['menuLevel']]+2;
                  convertMenu (el[keys['submenu']],el[keys['parentNode']][keys['menuLevel']]+1);
                  break;
    }
    menuOptions.maxDepth = omd;
  }
  var ______________________________Privates________________________________;
  /********************************************************
   * Menu initializing functions
   ********************************************************/
  /*
  *  Used to strip some css classes
  *
  *  @see menuOptions
  *  @param array className property exploded by space
  *  @param string one of menuOptions.stripCssClasses options
  *  @return array cleaned list of classes
  */
  var stripCssClasses = function (css, node) {
    try {
      for (var i=css.length;i>=0;i--) {
        if (menuOptions.stripCssClasses[node].indexOf(css[i])<0) continue;
        css.splice(i,1);
      }
    }catch(e){}
    return css;
  }
  /*
  *  Applies modifiers to specified node
  *
  *  @param DOMNode
  *  @param string optional modifier type
  *  @access private
  */
  var applyModifiers = function (node, type) {
    var run = {};
    /*
    *  if not set, we will use tag name
    */
    if (isUndefined(type) || 'string' != typeof type) type = node.tagName.toLowerCase();
    for(var mod=0, smL=modifiers.length; mod<smL; mod++) {
      if (self.modifier[modifiers[mod]].runat != type || !isUndefined(run[modifiers[mod]])) continue; 
      self.modifier[modifiers[mod]].mod.call(self.modifier[modifiers[mod]], node, keys, cssClasses, menuOptions);
      /*
      *  don't call modifier twice
      */
      run[modifiers[mod]] = true;
    }
    /*
    *  clear things
    */
    run = null;
  }
  /*
  *  main loader function
  *
  *  @param string absolute path from site root
  *  @access private
  */
  var loader = new function () {
    var ls = this;
    var options = null;
    /*
    *  check if theme if already loaded
    */
    var head = document.getElementsByTagName('head')[0];
    /*
    *  adds a link to external stylesheet to <head>
    *
    *  @param string stylesheet pathname
    *  @return void
    *  @access private
    */
    var applySS = function (sn) { if (!isUndefined(self.loadedStylesheets[sn])) return; head.appendChild(document.createElementExt('link',{'param': { 'rel': 'stylesheet', 'type': 'text/css', 'href': sn}})); self.loadedStylesheets[sn] = true; }
    /*
    *  adds a link to JS theme definition to <head>
    *
    *  @param string javascript pathname
    *  @access private
    */
    var applyJS = function (sn) { if (!isUndefined(self.loadedJS[sn])) return; head.appendChild(document.createElementExt('script',{'param': {'type': 'text/javascript', 'defer': true, 'src': sn}})); self.loadedJS[sn] = true;}
    /*
    *  Runs each 10ms, 10sec long until find loaded transition, then initializes it
    *
    *  @param string transition name
    *  @param int counter
    *  @access private
    */
    this.transitionOnload = function (name,cntr) { if (cntr>=10000) { self.transition[name] = true; return;} if (!self.transition[name]) { setTimeout(function(){ls.transitionOnload(name,cntr+10)},10); return; } transitions[transitions.length] = self.transition[name]; if ('function' == typeof self.transition[name].init) self.transition[name].init.call(self.transition[name],menuOptions, cssClasses, keys); }
    /*
    *  Runs each 10ms, 10sec long until find loaded theme, then initializes it
    *
    *  @param string theme name
    *  @param int counter
    *  @access private
    */
    this.themeOnload = function (name) {
      /*
      *  Adding the default transition
      */
      transitions = [self.transition['default']];
      /*
      *  now remerge user options to override theme defaults
      */
      menuOptions.merge(options);
      /*
      *  Checking for available transitions and load them
      */
      for (var i in menuOptions.transitions) {
        if (!menuOptions.transitions.hasOwnProperty(i)) continue;
          /*
          *  Loading transitions
          */
          if (!self.transition[i]) applyJS (gluePath(self.cmsRoot,'transitions',i+'.js'));
          playTimeout(this.transitionOnload,1,[i,0]);
      }
      /*
      *  Checking for available transitions and load them
      */
      if (menuOptions.modifiers && menuOptions.modifiers.length>0) {
        for (var i=0, tL=menuOptions.modifiers.length; i<tL; i++) {
          /*
          *  Loading modifiers
          */
          if (!self.modifier[menuOptions.modifiers[i]]) {
            if (isUndefined (self.modifier[menuOptions.modifiers[i]])) self.modifier[menuOptions.modifiers[i]] = menuOptions.modifiers[i];
            applyJS(gluePath(self.cmsRoot,'modifiers',menuOptions.modifiers[i]+'.js'));
          }
          dependencies[dependencies.length] = ['modifier', menuOptions.modifiers[i]];
          modifiers.push(menuOptions.modifiers[i]);
        }
      }
    }
    /*
    *  Initialize theme
    *
    *
    *
    */
    this.init = function (o) {
      options = o;
      /*
      *  load theme files
      */
      applySS (gluePath(self.getThemePath(),'layout.css'));
      applySS (gluePath(self.getThemePath(true),'design.css'));
      applyJS (gluePath(self.getThemePath(),'template.js'));
    
      var sp = menuOptions.theme.name.split('/');
      if (isUndefined (self.theme[sp[0]])) self.theme[sp[0]] = sp[0];
      dependencies[dependencies.length] = ['theme', sp[0]];
    }
  }

  /*
  *  Mouse event handlers
  */
  /**
  *  Menu item mouse events handler
  *
  *  @param {EventTarget} e
  *  @access protected
  */
  var menuItemEventHandler = function (e) {
    /*
    *  skip root node is needed for nesting independent menus
    */
    var root = getParent(e.srcElement || e.target, keys.isRoot, true);
    if (root[keys.cmsSelf] != self) return;
    var el = getParent(e.srcElement || e.target, 'li');
    /*
    *  check if found node belongs to current tree
    */
    if (!getParent(el,root)) return;
    root = null;

    /*
    *  for people who use incrementalConvert == true and like to force submenus to be open
    */
    var cel = el;
    while (el && !el[keys['parentNode']] 
           && cel != (cel = getParent(el,keys['isFolder'],true))) // this check is required, when independent unordered list
                                                                  // is nested within menu. simply, it prevent hangs, 
                                                                  // when the current list item could not be converted
      self.reinitSubmenu(cel);
    /*
    *  if we still could now find element to process
    */
    if (!el) return;
    switch (e.type.toLowerCase()) {
      case "mouseover" :
      case "mouseout" :
        /*
        *  do the same task for everybody
        */
        while (!el[keys['isRoot']]) {
          if (el[keys['isFolder']]) {
            /*
            *  clear only existing timeouts
            */
            if (parseInt(el[keys['timeout']])) clearTimeout(el[keys['timeout']]);
            el[keys['timeout']] = null;
            switch (e.type.toLowerCase()) {
              case 'mouseover' :
                /*
                *  call method only if node state is changed
                */
                if (!el[keys['openFlag']]) el[keys['timeout']] = playTimeout(playOpenClose,menuOptions.openTimeout,[el,'open']);
                break;
              case 'mouseout' :
                /*
                *  call method only if node state is changed
                */
                if (el[keys['openFlag']] && parseInt(menuOptions.closeTimeout)) el[keys['timeout']] = playTimeout(playOpenClose,menuOptions.closeTimeout,[el,'close']);
                break;
            }
          }
          el = el[keys['parentNode']];
        }
        break;
      /*
      *  actual 'click' processor
      */
      case "mouseup" :
        if (!el[keys['isFolder']] || (el[keys['submenu']][keys['interval']] && el[keys['submenu']][keys['interval']].interval)) return;
        clearTimeout(el[keys['timeout']]);
        /*
        *  click toggles submenu open state
        */
        if (menuOptions['toggleMenuOnClick'] 
         && (menuOptions['toggleMenuOnClick'] ^ el[keys['openFlag']]*2))
          playOpenClose(el, 'toggle');
        break;
    }
        
  }

  /*
  *  Menu item actions
  */
  /*
  *  Performs menu open
  *
  *  @param DOMnode to be opened - li
  *  @param boolean node should be opened or toggled
  *  @access private
  */
  var playOpenClose = function(el, flag) {
    var isOpen, i, player;
    if (flag != 'toggle' && el[keys['openFlag']] == (flag == 'open')) return;
    switch (flag.toLowerCase()) {case 'open': flag = 'Open'; break; case 'close': flag = 'Close'; break; case 'toggle': flag = el[keys['openFlag']]?'Close':'Open'; break; default: return; }

    /*
    *  Call custom event handlers, only if node stated is changed
    */
    if (el[keys['openFlag']] != (flag=='Open')) callEventHandlers(el, flag);

    /*
    *  for incremental conversion
    */
    if (null == el[keys['submenu']][keys['menuLevel']]) self.reinitSubmenu(el);

    isOpen = el[keys['openFlag']] = (flag=='Open');

    /*
    *  close sibling submenus, if allowed
    */
    if (menuOptions['closeSiblings'] && isOpen)
      for (i=0,sL=el[keys['parentNode']][keys['submenu']].length;i<sL;i++) 
        if ( el[keys['parentNode']][keys['submenu']][i][keys['openFlag']] 
          && el[keys['parentNode']][keys['submenu']][i] != el
          && el[keys['parentNode']][keys['submenu']][i][keys['isFolder']])
            playOpenClose (el[keys['parentNode']][keys['submenu']][i], 'close');

    el = el[keys['submenu']];
    /*
    *  executes transitions
    *
    *  @param array list of transitions
    *  @param array list of finalize methods
    *  @param int progress meter
    *  @param int delta time
    *  @access private
    */
    player = function(el,t,e) { 
      var i,tL=t.length,eL=e.length;
      var dt = (new Date).valueOf();
      /*
      *  progress meter in percents
      */
      el[keys['interval']].pg = Math.round(el[keys['interval']].pg+(dt-el[keys['interval']].start)*100/menuOptions.length);
      /*
      *  start point in percents
      */
      el[keys['interval']].start = dt;
      if (el[keys['interval']].pg>100) el[keys['interval']].pg = 100;  // specially for FireFox
      el[keys['interval']].pg_delta = el[keys['interval']].pg/100;

      for (i=0;i<tL;i++) { 
        if (null == t[i]) continue; 
        if (!t[i][0].call(t[i][1],el,menuOptions,cssClasses,keys)) {
          t.splice(i,1); i--; tL--;
        }
      }
      if (0 == t.length) { 
        for (i=0;i<eL;i++) e[i][0].call(e[i][1],el,menuOptions,cssClasses,keys); 
        clearInterval(el[keys['interval']].interval); 
        el[keys['interval']].interval = false;
        menuOptions['forceSkipTransitions'] = false;
      } 
    }
    if (el[keys['interval']]) {
      /*
      *  if came from unfinished event
      */
      clearInterval(el[keys['interval']].interval);
      el[keys['interval']].pg = 100-el[keys['interval']].pg;
      el[keys['interval']].pg_delta = el[keys['interval']].pg/100;
    } else {
      /*
      *  or begin from scratch
      */
      el[keys['interval']] = { 'pg' : 0,
                               'pg_delta' : 0
                             }
    }

    /*
    *  prepare transitions 
    */
    var f, t = [], e = [];
    for (i=0,mL=transitions.length; i<mL; i++) {
      f = transitions[i]['init'+flag]; if (typeof f == 'function') f.call(transitions[i],el,menuOptions,cssClasses,keys); 
      f = transitions[i]['play'+flag]; if (!menuOptions['forceSkipTransitions'] && typeof f == 'function') t[t.length] = [f, transitions[i]]; 
      f = transitions[i]['finish'+flag]; if (typeof f == 'function') e[e.length] = [f, transitions[i]];
    }
    el[keys['interval']].start = (new Date).valueOf();
    el[keys['interval']].interval = setInterval(function(){player(el,t,e)},menuOptions.interval);
  }
  /*
  *  Call custom event handlers
  *
  *  @param DOMNode to call event on
  *  @param string flag
  *  @access private
  */
  var callEventHandlers = function (el, flag) {
    if (!menuOptions.handlers) return;
    /*
    *  Perform call itself
    *
    *  @param DOMNode to call event on
    *  @param string handler type in form 'on'+type
    */
    var _call = function (el, h) {
      if (menuOptions.handlers[h] instanceof Array) {

        for (var i=0, mL = menuOptions.handlers[h].length; i<mL; i++) {
          try {
            menuOptions.handlers[h][i][1].call(menuOptions.handlers[h][i][0],el, keys, cssClasses, menuOptions);
          } catch (e) {} //window.status = 'Cannot execute handler: '+e+menuOptions.handlers[h][i]}
        }
      }
    }
    var h = 'on'+flag;
    _call(el,h);
    _call(el,'onChangeState');
  } 
  /*
  *  Convert list into DHTML menu
  *
  *  @param DOMnode menu container (<ul> node)
  *  @access private
  */
  var convertMenu = function (el,level) {
    /*
    *  don't convert menu below the max depth
    */
    if (menuOptions.maxDepth && level > menuOptions.maxDepth-1 && 
        (el[keys.parentNode] && el[keys.parentNode][keys.openFlag]===false)) return;
    /*
    *  save level number for future use
    *  root ul will have -1
    */
    el[keys.menuLevel] = level;
    /*
    *  temporarily remove node from the document
    *  to prevent the whole document re-render on it's change
    */
    var dummy = document.createElement('div');
    el.parentNode.replaceChild(dummy,el);
    /*
    *  increase level on start, it's recursive function
    */
    level++;

    el[keys.submenu] = [];
    for (var i=0,cL=el.childNodes.length; i<cL; i++) { 
      if (!el.childNodes[i].tagName || el.childNodes[i].tagName.toLowerCase() != 'li') continue;
      el[keys.submenu][el[keys.submenu].length] = el.childNodes[i];
      el.style.display = '';
      /*
      *  save direct link to our current parent node
      */
      el.childNodes[i][keys.parentNode] = el;

      var tmp = el.childNodes[i].className.split(' ');
      /*
      *  either current level is lesser then max opened by default
      *  or CSS class to force open is set
      */
      el.childNodes[i][keys.openFlag] = ((level < menuOptions.maxOpenDepth || 
                                             tmp.indexOf(menuOptions.flagOpenClass) > -1
                                            ) &&
                                            tmp.indexOf(menuOptions.flagClosedClass) <0);
      /*
      *  strip junk classes
      */
      tmp = stripCssClasses(tmp, 'li');
      /*
      *  note, menu level passes here
      *  go to convert submenu
      */
      convertMenuItem(el.childNodes[i],level);


      if (!isUndefined(el.childNodes[i][keys.submenu])) {
        /*
        *  by default, this menu folder is closed
        */
        tmp[tmp.length] = cssClasses['folder'];
        /*
        *  set proper Open/Closed CSS class
        */
        tmp[tmp.length] = cssClasses[el.childNodes[i][keys.openFlag]?'folderOpen':'folderClosed'];
        /*
        *  mark node as folder
        */
        el.childNodes[i][keys.isFolder] = true;
      } else {
        /*
        *  means this is not a menu folder
        */
        tmp[tmp.length] = cssClasses.menuItem;
        el.childNodes[i][keys.isFolder] = false;
      }
      /*
      *  count levels for the folder
      */
      tmp[tmp.length] = cssClasses.menuLevel.split(" ").map(function(el){return el+level}).join(" ");
      tmp[tmp.length] = cssClasses[level%2?'evenLevel':'oddLevel'];

      el.childNodes[i].className=tmp.join(' ');

      /*
      *  run modifiers on <li>
      */
      applyModifiers(el.childNodes[i]);
      /*
      *  process active node control (<a> tag)
      */
      var a = el.childNodes[i].firstChild;

      while (null != a && (!a.tagName || (a.tagName && a.tagName.toLowerCase()!='a'))) a = a.nextSibling;
      if (a) {
        el.childNodes[i][keys.activator] = a;
        a[keys.parentNode] = el.childNodes[i];
        var tmp = a.className.split(' ');
        tmp = stripCssClasses(tmp, 'a');
        a.className = tmp.join(" ");
        /*
        *  run modifiers on <a>
        */
        applyModifiers(a);
      }
    }
    /*
    *  if submenu has no live items
    */
    if (el[keys['submenu']].length < 1 && el[keys.parentNode]) {el[keys.parentNode][keys.openFlag] = false;}
    /*
    *  put converted menu back
    */
    dummy.parentNode.replaceChild(el,dummy);
    dummy = null;
  }
  /*
  *  Converts submenu item to dynamic
  *
  *  @param DOMnode submenu item
  *  @param int submenu depth level
  *  @access private
  */
  var convertMenuItem = function (el,level) {
    for (var i=0,cL=el.childNodes.length; i<cL; i++) {
      if (!el.childNodes[i].tagName || el.childNodes[i].tagName.toLowerCase() != 'ul') continue;

      var tmp = el.childNodes[i].className.split(" ");
      tmp = stripCssClasses(tmp, 'ul');
      el.childNodes[i].className = tmp.join(" ");

      /*
      *  li submenu has one and only one item
      */
      el[keys['submenu']] = el.childNodes[i];
      el.childNodes[i][keys['parentNode']] = el;

      if (!menuOptions.incrementalConvert || 
          el[keys['openFlag']] ||
          level < menuOptions['maxDepth']-1
          ) convertMenu(el[keys['submenu']],level);
      /*
      *  run modifiers on <ul>
      */
      applyModifiers(el.childNodes[i]);
    }
  }
  /*
  *  Performs lookup for menu availability, then initializes it
  *
  *  @access private
  */
  var convertMenuById = function () {
    var el = document.getElementById(menuId);
    if (!el || !dpdLoaded()) { setTimeout(convertMenuById,10); return}
    /**
    *  BEGIN final menu initialization
    ****************
    *
    *  put flag classes to the removal list, to avoid unwanted effects
    */
    menuOptions.stripCssClasses.li.push(menuOptions.flagOpenClass);
    menuOptions.stripCssClasses.li.push(menuOptions.flagClosedClass);
    /*
    *  if set, append template suffix to all CSS classes
    */
    if (menuOptions.appendTemplateSuffix) {
      var n = menuOptions.theme.name.split("/");
      var s = n[0];
      var n = n.join("");
      for (var i in cssClasses) {
        if (cssClasses.hasOwnProperty(i) && 'root' != i)
          cssClasses[i] = cssClasses[i]+s+' '+cssClasses[i]+n;
      }
    }
    /****************
    *  END final menu initialization
    **/
    var tmp = el.className.split(" ");
    /*
    *  strip some CSS classes
    */
    tmp = stripCssClasses(tmp, 'root');
    /*
    *  applying main class name
    */
    tmp[tmp.length] = cssClasses.root;
    /*
    *  theme-dependent class name
    */
    var n = menuOptions.theme.name.split("/");
    var s = "";
    for (var i=0,nL=n.length;i<nL;i++) {
      s += n[i];
      tmp[tmp.length] = cssClasses.root+s;
    }
    el.className = tmp.join(" ");

    el[keys['isRoot']] = true;
    /*
    *  root menu items are at level0
    */
    convertMenu(el,-1);
    if (menuOptions.openTimeout) {
      el.attachEvent('onmouseover',menuItemEventHandler);
      el.attachEvent('onmouseout',menuItemEventHandler);
    }
    el.attachEvent('onmouseup',menuItemEventHandler);
    el.style.display = '';
    /*
    *  run modifiers on <root>
    */
    applyModifiers(el,'root');
    /*
    *  attach menu processor object to the DOM tree
    */
    el[keys['cmsSelf']] = self;
  }
  /*
  *  Performs check for all menu components availability
  *
  *  @access private
  */
  var dpdLoaded = function () {
     var i, dL = dependencies.length, dp;
     for (i=0;i<dL;i++) {
       if (isNaN(dependencies[i][3])) dependencies[i][3] = 0;
       dp = self[dependencies[i][0]][dependencies[i][1]];
       /*
       *  string means - dependency is not yet loaded
       *  there should be the object
       */
       if ('string' != typeof dp) {
         if (dp.menuOptions) menuOptions.merge(dp.menuOptions,dependencies[i][0]=='theme');
         if (dp.init) dp.init.call(dp, menuOptions, cssClasses, keys);
         if (loader[dependencies[i][0]+'Onload']) loader[dependencies[i][0]+'Onload'](dependencies[i][1]);
         dependencies.splice(i,1);
         i--;
         dL--;
       } else if (dependencies[i][3] >= 10000) {
         /*
         *  if timeout...
         */
         throw Error ("Resource could not be loaded: "+dependencies[i][0]+" - "+dependencies[i][1]);
       } else {
         dependencies[i][3] += 10;
       }
     }
     return !dependencies.length;
  }
}

/*
*  Complete Menu Solution's root path
*
*  @type string
*  @access private
*/
CompleteMenuSolution.prototype.cmsRoot = findPath('cms.js');

/*
*  define class-instances global vars
*
*  List of loaded stylesheets
*/
CompleteMenuSolution.prototype.loadedStylesheets = {};
/*
*  define class-instances global vars
*
*  List of loaded stylesheets
*/
CompleteMenuSolution.prototype.loadedJS = {};
/*
*  List of available themes
*
*  @type hash
*  @access public
*/
CompleteMenuSolution.prototype.theme = {};
/*
*  List of available transitions
*
*  @type hash
*  @access public
*/
CompleteMenuSolution.prototype.transition = {
  /*
  *  Always awailable transition
  */
  'default' : {
    /*
    *  Initializes Open transition
    *
    *  @param DOMnode target element
    *  @param array menuOptions
    *  @param array cssClasses
    *  @access public
    */
    'initOpen' : function (el,mo,cssClasses,keys) {
       el = el[keys['parentNode']];
       var tmp = el.className.split(" "),
           tc = cssClasses.folderClosed.split(" "),
           idx;
       for (var i=0,tcL=tc.length; i<tcL; i++) { idx = tmp.indexOf(tc[i]); if (idx > -1) tmp.splice(idx,1);}
       tc = cssClasses.folderOpen.split(" ");
       for (var i=0,tcL=tc.length; i<tcL; i++) { idx = tmp.indexOf(tc[i]); if (idx > -1) tmp.splice(idx,1);}
       tmp[tmp.length] = cssClasses.folderOpen;
       el.className = tmp.join(" ");
    },
//    'finishOpen' : function (el,mo,cssClasses) {
//    },
    /*
    *  Finishes Close transition
    *
    *  @param DOMnode target element
    *  @param array menuOptions
    *  @param array cssClasses
    *  @access public
    */
//    'initClose' : function (el,mo,cssClasses) {
//    },
    'finishClose' : function (el,mo,cssClasses,keys) {
       el = el[keys['parentNode']];
       var tmp = el.className.split(" "),
           tc = cssClasses.folderOpen.split(" "),
           idx;
       for (var i=0,tcL=tc.length; i<tcL; i++) { idx = tmp.indexOf(tc[i]); if (idx > -1) tmp.splice(idx,1);}
       tc = cssClasses.folderClosed.split(" ");
       for (var i=0,tcL=tc.length; i<tcL; i++) { idx = tmp.indexOf(tc[i]); if (idx > -1) tmp.splice(idx,1);}
       tmp[tmp.length] = cssClasses.folderClosed;
       el.className = tmp.join(" ");
    }
  }
};
/*
*  List of available mods
*
*  @type hash
*  @access public
*/
CompleteMenuSolution.prototype.modifier = {};
/*
*  List of required libraries
*
*  @type array
*  @access public
*/
CompleteMenuSolution.prototype.requires = [
  'extensions/e.js',
//  'extensions/objectextensions.js',
//  'extensions/functionextensions.js',
//  'extensions/arrayextensions.js',
//  'extensions/domextensions.js'
//  'at.js'
];


/*
*  Load required libraries
*/
for (var i=0, cL= CompleteMenuSolution.prototype.requires.length; i<cL; i++) {
  try {
      document.write("<scr"+"ipt type=\"text/javascript\" src=\""+CompleteMenuSolution.prototype.cmsRoot+CompleteMenuSolution.prototype.requires[i]+"\" ></script>");
  } catch (e) {
      var el = document.getElementsByTagName('head')[0]
         ,s = document.createElement('script');
      s.type="text/javascript";
      s.src = CompleteMenuSolution.prototype.cmsRoot+CompleteMenuSolution.prototype.requires[i]
      el.appendChild(s);
  }
}

/**
 *  return full path to the script
 *
 *  @param string script name
 *  @return mixed string full path or null
 *  @scope public
 */
function findPath (sname) {
  var sc = document.getElementsByTagName('script'),
      sr = new RegExp('^(.*/|)('+sname+')([#?]|$)');
  for (var i=0,scL=sc.length; i<scL; i++) {
    // matched desired script
    var m = String(sc[i].src).match(sr);
    if (m) {
      /*
      *  we've matched the full path
      */
      if (m[1].match(/^((https?|file)\:\/{2,}|\w:[\\])/)) return m[1];
      /*
      *  we've matched absolute path from the site root
      */
      if (m[1].indexOf("/")==0) return m[1];
      b = document.getElementsByTagName('base');
      if (b[0] && b[0].href) return b[0].href+m[1];
      /*
      *  return matching part of the document location and path to js file
      */
      return (document.location.pathname.match(/(.*[\/\\])/)[0]+m[1]).replace(/^\/+(?=\w:)/,"");
    }
  }
  return null;
}
