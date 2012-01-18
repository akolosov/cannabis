/*
*  $Id: activeontop.js 51 2006-07-25 22:11:49Z wingedfox $
*  $HeadURL: https://svn.debugger.ru/repos/CompleteMenuSolution/tags/v0.5.12/modifiers/activeontop.js $
*
*  Activeontop modifier adjusts 
*
*
*  @package CompleteMenuSolution
*  @author Ilya Lebedev <ilya.lebedev.net>
*  @lastmodifier $Author: wingedfox $
*  @title Ajaxum
*  @version $Rev: 51 $
*/
CompleteMenuSolution.prototype.modifier.activeontop = {
  runat : 'root',

  maxZIndex : 0,
  /*
  *  modifier itself
  *
  *  @param DOMNode element
  *  @param array node keys
  *  @return DOMNode modified element
  *  @access public
  */
  mod : function (el, keys) {
    var self = this;
    /*
    *  check the target and stop event, if match span
    *
    *  @param EventTarget
    *  @return boolean
    *  @access protected
    */
    var onmouseover = function(e) {
      var el = e.srcElement || e.target;
      self.maxZIndex++;
      while (el && (!el[keys['parentNode']] || el[keys['isRoot']])) 
        el = el.parentNode;
      if (!el) return;
      while (el[keys['parentNode']]) {
        if (el[keys['isFolder']]) {
          el.style.zIndex = self.maxZIndex;
        }
        el = el[keys['parentNode']];
      }
      if (el[keys['isRoot']]) 
        el.style.zIndex = self.maxZIndex;
    }

    el.attachEvent('onmouseover', onmouseover);
  }
}