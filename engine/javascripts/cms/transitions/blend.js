/*
*
*  Performas blend transformation
*
*  @application Complete Menu Solution
*  @author Ilya Lebedev <ilya@lebedev.net>
*  @copyright (c) 2006, Ilya Lebedev
*  @license Free for non-commercial use
*  @package CompleteMenuSolution
*  @title Blend transformation
*/
CompleteMenuSolution.prototype.transition.blend = {
  /*
  *  performs options check and set defaults, if something is wrong
  *
  *  @param hash menu options
  *  @access public
  */
  init : function (mo) {
      /*
      *  start and end opacity values, percent
      */
      if (!mo.transitions.blend.start) mo.transitions.blend.start = 0;
      if (!mo.transitions.blend.end) mo.transitions.blend.end = 1;
      if (!mo.transitions.blend.useIeBlendFix) mo.transitions.blend.useIeBlendFix = false;
      mo.transitions.blend.end = mo.transitions.blend.end - mo.transitions.blend.start;
      var el = document.body;
      mo.transitions.blend.attr = (el.style.opacity==null)
                                   ?((el.style.MozOpacity==null)
                                      ?((el.style.KhtmlOpacity == null)
                                         ?'KhtmlOpacity' //Konqueror, Safari, ...
                                         :'OOpacity')    //Opera?
                                      :'MozOpacity')     //Mozilla/FireFox
                                   :'opacity';           //CSS3
  },
  /*
  *  performs the blending
  *
  *  @param DOMNode element to be blended
  *  @param hash menu options
  *  @param hash populated keys
  *  @param float percentage delta for clipping
  *  @access private
  */
  doBlend : function (el, mo, keys, dt) {
    var attr = mo.transitions.blend.attr;
    var start = mo.transitions.blend.start;
    var end = mo.transitions.blend.end;

    el.style[attr] = start+end*dt;

    if (el.filters) {
      if (mo.transitions.blend.useIeBlendFix)
        el.filters.item('alpha').opacity = Math.round(start*100+mo.transitions.blend.maxIeOpac*dt);
      else 
        el.filters.item('alpha').opacity = Math.round(Number(el.style[attr])*100);
    }

    if (el[keys['interval']].pg == 100) {
      return false;
    }

    return true;
  },
  /*
  *  initializes transition
  *
  *  @param DOMNode target element
  *  @param hash menu options
  */
  'initOpen' : function (el, mo, css, keys) {
    if (el.filters) {
      if (mo.transitions.blend.useIeBlendFix) {
        if (!mo.transitions.blend.maxIeOpac) mo.transitions.blend.maxIeOpac = el.filters.item('alpha').opacity;
      }
      if (el.style.filter.indexOf("alpha(")<0) {
        el.style.filter = "alpha(opacity=0);";
      }
    }
    this.doBlend(el, mo, keys, el[keys['interval']].pg_delta);
  },
  /*
  *  performs blending transformation
  *
  *  @param DOMNode target element
  *  @param hash menu options
  */
  'playOpen' : function (el, mo, css, keys) {
    return this.doBlend(el, mo, keys, el[keys['interval']].pg_delta);
  },
  'finishOpen' : function (el,mo) {
    var attr = mo.transitions.blend.attr;
    var end = mo.transitions.blend.end;
    if (el.filters)
      if (mo.transitions.blend.useIeBlendFix) {
        el.filters.item('alpha').opacity = mo.transitions.blend.maxIeOpac;
      } else {
        el.style.filter = '';
//        el.filters.item('alpha').enabled = 0;
       // does not work for IE 5.01
    }
    el.style[attr] = end;
  },
  /*
  *  initializes transition
  *
  *  @param DOMNode target element
  *  @param hash menu options
  */
  'initClose' : function (el, mo, css, keys) {
    if (el.filters) {
      if (mo.transitions.blend.useIeBlendFix) {
        if (!mo.transitions.blend.maxIeOpac) mo.transitions.blend.maxIeOpac = el.filters.item('alpha').opacity;
      }
      if (el.style.filter.indexOf("alpha(")<0) {
        el.style.filter = "alpha(opacity=100);";
      }
    }
    this.doBlend(el, mo, keys, 1-el[keys['interval']].pg_delta);
  },
  /*
  *  performs blending transformation
  *
  *  @param DOMNode target element
  *  @param hash menu options
  */
  'playClose' : function (el, mo, css, keys) {
    return this.doBlend(el, mo, keys, 1-el[keys['interval']].pg_delta);
  },

  'finishClose' : function (el, mo) {
    var attr = mo.transitions.blend.attr;
    var start = mo.transitions.blend.start;
    if (el.filters)
      if (mo.transitions.blend.useIeBlendFix) {
        el.filters.item('alpha').opacity = mo.transitions.blend.maxIeOpac;
      } else {
        el.style.filter = '';
//        el.filters.item('alpha').enabled = 0;
       // does not work for IE 5.01
    }
    el.style[attr] = start;
  }
}