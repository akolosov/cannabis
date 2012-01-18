/*
*
*  Performas clip transformation
*
*  @application Complete Menu Solution
*  @author Ilya Lebedev <ilya@lebedev.net>
*  @copyright (c) 2006, Ilya Lebedev
*  @license Free for non-commercial use
*  @package CompleteMenuSolution
*  @title Clip transformation
*  @version 0.3.0.03032006
*
*  Revision history
*
*  0.3.1.03032006
*
*  % bug with multiple instance running
*  + support of 
*
*  0.3.0.03032006
*
*  % meet transition concept of Cms v0.4
*  - junk code
*  % uses 'moving toward 100%' convept, instead of number of fixed intervals
*  + initVal and doClip methods, to share them through transition methods
*
*  0.2.0.01032006
*  + support of much more pre-completed directions
*
*  0.1.0.31012006 First public release
*/
CompleteMenuSolution.prototype.transition.clip = {
  /*
  *  possible directions
  *
  *  @type array
  *  @aqccess public
  */
  directions : ['s', 'se', 'e', 'ne', 'n', 'nw', 'w', 'sw',
                'sn', 'ew', 'sen', 'enw', 'nws', 'wse', 'senw'],
  /*
  *  min and max values for certain attributes
  *  number = percent from max value
  *
  *
  */
  clipvalues : {
    's'  : { 'stTop' : 0,
             'enTop' : 0,
             'stBot' : 0,
             'enBot' : 100,
             'stLt' : 0,
             'enLt' : 0,
             'stRt' : 100,
             'enRt' : 100
    },
    'se' : { 'stTop' : 0,
             'enTop' : 0,
             'stBot' : 0,
             'enBot' : 100,
             'stLt' : 0,
             'enLt' : 0,
             'stRt' : 0,
             'enRt' : 100
    },
    'e'  : { 'stTop' : 0,
             'enTop' : 0,
             'stBot' : 100,
             'enBot' : 100,
             'stLt' : 0,
             'enLt' : 0,
             'stRt' : 0,
             'enRt' : 100
    },
    'ne' : { 'stTop' : 100,
             'enTop' : 0,
             'stBot' : 100,
             'enBot' : 100,
             'stLt' : 0,
             'enLt' : 0,
             'stRt' : 0,
             'enRt' : 100
    },
    'n'  : { 'stTop' : 100,
             'enTop' : 0,
             'stBot' : 100,
             'enBot' : 100,
             'stLt' : 0,
             'enLt' : 0,
             'stRt' : 100,
             'enRt' : 100
    },
    'nw' : { 'stTop' : 100,
             'enTop' : 0,
             'stBot' : 100,
             'enBot' : 100,
             'stLt' : 100,
             'enLt' : 0,
             'stRt' : 100,
             'enRt' : 100
    },
    'w'  : { 'stTop' : 0,
             'enTop' : 0,
             'stBot' : 100,
             'enBot' : 100,
             'stLt' : 100,
             'enLt' : 0,
             'stRt' : 100,
             'enRt' : 100
    },
    'sw' : { 'stTop' : 0,
             'enTop' : 0,
             'stBot' : 0,
             'enBot' : 100,
             'stLt' : 100,
             'enLt' : 0,
             'stRt' : 100,
             'enRt' : 100
    },
    'sn' : { 'stTop' : 50,
             'enTop' : 0,
             'stBot' : 50,
             'enBot' : 100,
             'stLt' : 0,
             'enLt' : 0,
             'stRt' : 100,
             'enRt' : 100
    },
    'ew' : { 'stTop' : 0,
             'enTop' : 0,
             'stBot' : 100,
             'enBot' : 100,
             'stLt' : 50,
             'enLt' : 0,
             'stRt' : 50,
             'enRt' : 100
    },
    'sen' : { 'stTop' : 50,
             'enTop' : 0,
             'stBot' : 50,
             'enBot' : 100,
             'stLt' : 0,
             'enLt' : 0,
             'stRt' : 0,
             'enRt' : 100
    },
    'enw' : { 'stTop' : 0,
             'enTop' : 0,
             'stBot' : 0,
             'enBot' : 100,
             'stLt' : 50,
             'enLt' : 0,
             'stRt' : 50,
             'enRt' : 100
    },
    'nws' : { 'stTop' : 50,
             'enTop' : 0,
             'stBot' : 50,
             'enBot' : 100,
             'stLt' : 100,
             'enLt' : 0,
             'stRt' : 100,
             'enRt' : 100
    },
    'wse' : { 'stTop' : 100,
             'enTop' : 0,
             'stBot' : 100,
             'enBot' : 100,
             'stLt' : 50,
             'enLt' : 0,
             'stRt' : 50,
             'enRt' : 100
    },
    'senw' : { 'stTop' : 50,
               'enTop' : 0,
               'stBot' : 50,
               'enBot' : 100,
               'stLt' : 50,
               'enLt' : 0,
               'stRt' : 50,
               'enRt' : 100
    }
  },
  /*
  *  performs options check and set defaults, if something is wrong
  *
  *  @param hash menu options
  *  @param hash css classes
  *  @param hash additional keys
  *  @access public
  */
  init : function (mo, css, keys) {
    /*
    *  clip increment
    */

    var d = mo.transitions.clip.direction;
    if (typeof d == 'string') d = [d];
    else if (!(d instanceof Array ) || d.length == 0) d = ['se'];
    for (var i=0; i<d.length ; i++) {
      if (this.directions.indexOf(d[i]) < 0) {
        d.splice(i,1);
        i--;
      }
    }
    mo.transitions.clip.direction = d;
    /*
    *  update keys
    */
    keys['clipIncrement'] = '__clipIncrement';

  },
  /*
  *  initialize clip margins 
  *
  *  @param DOMNode element
  *  @param hash menu paramenters
  *  @param hash populated keys
  *  @access private
  */
  initValues : function (el, mo, keys) {
    el.style.visibility = 'hidden';
    el.style.display = 'block';

    var dir = mo.transitions.clip.direction, d;
    if (!dir[el[keys['menuLevel']]]) d = dir[dir.length-1];
    else d = dir[el[keys['menuLevel']]];

    var clval = this.clipvalues[d];

    el[keys['clipIncrement']] = {};
    el[keys['clipIncrement']].sTop = el.offsetHeight*clval['stTop']/100;
    el[keys['clipIncrement']].eTop = el.offsetHeight*clval['enTop']/100 - el[keys['clipIncrement']].sTop;

    el[keys['clipIncrement']].sBot = el.offsetHeight*clval['stBot']/100;
    el[keys['clipIncrement']].eBot = el.offsetHeight*clval['enBot']/100 - el[keys['clipIncrement']].sBot;

    el[keys['clipIncrement']].sLt = el.offsetWidth*clval['stLt']/100;
    el[keys['clipIncrement']].eLt = el.offsetWidth*clval['enLt']/100 - el[keys['clipIncrement']].sLt;

    el[keys['clipIncrement']].sRt = el.offsetWidth*clval['stRt']/100;
    el[keys['clipIncrement']].eRt = el.offsetWidth*clval['enRt']/100 - el[keys['clipIncrement']].sRt;

    el.style.display = '';
    el.style.visibility = '';
  },
  /*
  *  performs the clipping
  *
  *  @param DOMNode element to be clipped
  *  @param hash populated keys
  *  @param float percentage delta for clipping
  *  @access private
  */
  doClip : function (el, keys, dt) {
    var t = el[keys['clipIncrement']].sTop+el[keys['clipIncrement']].eTop*dt;
    var b = el[keys['clipIncrement']].sBot+el[keys['clipIncrement']].eBot*dt;
    var l = el[keys['clipIncrement']].sLt+el[keys['clipIncrement']].eLt*dt;
    var r = el[keys['clipIncrement']].sRt+el[keys['clipIncrement']].eRt*dt;
    try {
      el.style.clip = "rect("+t+"px "+r+"px "+b+"px "+l+"px)";
    } catch (e) {    }
  },
  /*
  *  initializes transition
  *
  *  @param DOMNode target element
  *  @param hash menu options
  */
  'initOpen' : function (el, mo, css, keys) {
    el.style.overflow = 'hidden';
    if (!el[keys['clipIncrement']]) {
      this.initValues.call(this, el, mo, keys)
    }
    this.doClip(el, keys, el[keys['interval']].pg_delta);
  },
  /*
  *  performs transformation
  *
  *  @param DOMNode target element
  *  @param hash menu options
  *  @param hash css classes
  */
  'playOpen' : function (el, mo, css, keys) {

    this.doClip(el, keys, el[keys['interval']].pg_delta);

    if (el[keys['interval']].pg == 100) {
      return false;
    }
    return true;
  },
  /*
  *  does finish transformation
  *
  *  @param DOMNode element
  */
  finishOpen : function (el) {
    el.style.overflow = '';
    try {
      el.style.clip = '';
    } catch (e) {
      el.style.clip = 'rect(auto auto auto auto)';
    }
  },
  /*
  *  initializes transition
  *
  *  @param DOMNode target element
  *  @param hash menu options
  */
  'initClose' : function (el, mo, css, keys) {
    el.style.overflow = 'hidden';
    if (!el[keys['clipIncrement']]) {
      this.initValues.call(this, el, mo, keys)
    }

    this.doClip(el, keys, 1-el[keys['interval']].pg_delta);

    el.style.display = '';
    el.style.visibility = '';
  },
  /*
  *  performs blending transformation
  *
  *  @param DOMNode target element
  *  @param hash menu options
  *  @param hash css classes
  */
  'playClose' : function (el, mo, css, keys) {

    this.doClip(el, keys, 1-el[keys['interval']].pg_delta);

    if (el[keys['interval']].pg == 100) {
      return false;
    }
    return true;
  },
  /*
  *  does finish transformation
  *
  *  @param DOMNode element
  */
  finishClose : function (el) {
    el.style.overflow = '';
    try {
      el.style.clip = '';
    } catch (e) {
//      el.style.clip = 'rect(auto auto auto auto)';
      el.style.clip = 'rect(0 0 0 0)';
    }
  }
}