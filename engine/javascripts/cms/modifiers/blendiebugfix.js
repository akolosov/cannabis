/*
*
*  Fixes blending for IE
*
*  @package CompleteMenuSolution
*  @author Ilya Lebedev <ilya.lebedev.net>
*
*/
CompleteMenuSolution.prototype.modifier.blendiebugfix = {
  runat: 'ul',
  /*
  *  modifier itself
  *
  *  @param DOMNode element
  *  @return DOMNode modified element
  *  @access public
  */
  mod : function (el,keys,css,mo) {
    var div = document.createElementExt('div',{'class':'blendIeBugfix'});
    el[keys['parentNode']].replaceChild(div,el);
    div.appendChild(el);
  }
}