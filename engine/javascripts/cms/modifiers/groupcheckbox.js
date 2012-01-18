/*
*
*  Used to set group of checkboxes in the list
*
*  @package CompleteMenuSolution
*  @author Ilya Lebedev <ilya.lebedev.net>
*
*/
CompleteMenuSolution.prototype.modifier.groupcheckbox = {
  runat: 'a',
  /*
  *  modifier itself
  *
  *  @param DOMNode element
  *  @param array keys for nodes
  *  @return DOMNode modified element
  *  @access public
  */
  mod : function (el, keys) {
    /*
    *  process checkbox settings
    *
    *  @param EventTarget
    *  @return boolean
    *  @access protected
    */
    var onclick = function (e) {
      var el = e.srcElement || e.target;
      if (!el.tagName || el.tagName.toLowerCase() != 'input' || el.type.toLowerCase() != 'checkbox') return;
    
      var res = setCbxOnChilds(el[keys['parentNode']][keys['parentNode']][keys['submenu']], true);
      /*
      *  if one or more checkboxes was not set
      */
      el.checked = !el.checked;
    
      if (!res) {
        el.checked = true;
      } else if (el.checked == false && res) {
        setCbxOnChilds(el[keys['parentNode']][keys['parentNode']][keys['submenu']], false);
      }
      setCbxOnParents(el[keys['parentNode']][keys['parentNode']][keys['parentNode']][keys['parentNode']],el.checked);
    
      if (e.stopPropagation) e.stopPropagation();
      e.cancelBubble = true;
    }
    /*
    *  prevent submenu toggle on checkbox
    *
    *  @param EventTarget
    *  @return boolean
    *  @access protected
    */
    var preventFollowLink = function (e) {
      var el = e.srcElement || e.target;
      if (el.tagName && el.tagName.toLowerCase() == 'input') {
        if (e.preventDefault) e.preventDefault();
        e.returnValue = false;
      }
    }
    /*
    *  sets checkbox state on submenu nodes
    *
    *  @param DOMNode ul or li containers
    *  @param boolean target checkbox state
    *  @return boolean true if all childs was checked or false otherwise
    *  @access private
    */
    var setCbxOnChilds = function (el,state) {
      var res = true;
      if (el) {
        if (el && el.tagName.toLowerCase() == 'ul') {
          /*
          *  matched ul node
          */
          for (var i=0, elL = el[keys['submenu']].length; i<elL; i++) {
            res &= setCbxOnChilds(el[keys['submenu']][i], state);
          }
        } else {
          /*
          *  matched li node
          */
          res = el[keys['activator']].__groupCheckbox.checked == true;
          el[keys['activator']].__groupCheckbox.checked = state;
          res &= setCbxOnChilds(el[keys['submenu']], state);
        }
      }
      return res;
    }
    /*
    *  set checkbox state on parents nodes
    *
    *
    */
    var setCbxOnParents = function (el, state) {
      var res = true;
      while (el) {
        if (el[keys['submenu']][keys['submenu']]) {
          for (var i=0, sL = el[keys['submenu']][keys['submenu']].length; i<sL; i++) {
            res &= el[keys['submenu']][keys['submenu']][i][keys['activator']].__groupCheckbox.checked == state;
          }
        }
        if (!state || res) {
          el[keys['activator']].__groupCheckbox.checked = state;
        } else {
          return;
        }
        el = el[keys['parentNode']][keys['parentNode']];
      }
    }



    var cbx = el.getElementsByTagName('input');
    for (var i=0,cL=cbx.length; i<cL; i++) {
      if (cbx[i].type.toLowerCase() == 'checkbox') {
        cbx[i][keys['parentNode']] = el;
        /*
        *  handlers are attached this way, because FF has bug in checkbox handling
        *  - parent link could not be blocked w/o blocking checkbox state change
        */
        el.attachEvent('onmouseup', onclick);
        el.attachEvent('onclick', preventFollowLink);
        el.__groupCheckbox = cbx[i];
      }
    }
  }
}

