/*
*
*  Check window borders and applies cpecific CSS classes
*  when needed
*
*  @package CompleteMenuSolution
*  @author Ilya Lebedev <ilya.lebedev.net>
*
*/
CompleteMenuSolution.prototype.transition.windowborderschecker = {
  cssClasses : ['FolderOverflowRight', 'FolderOverflowLeft', 'FolderOverflowBottom', 'FolderOverflowTop'],
                 
  getOffset : function (el) {
//    alert(this.toSource())
    var xy = [el.offsetLeft,el.offsetTop];
    if (el.offsetParent) {
      var xy1 = this.getOffset(el.offsetParent);
      xy[0] += xy1[0];
      xy[1] += xy1[1];
    }
    return xy;
  },
  getWindowWH : function () {
    var w = 0, h = 0;
    if( typeof( window.innerWidth ) == 'number' ) {
      //Non-IE
      w = window.innerWidth;
      h = window.innerHeight;
    } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
      //IE 6+ in 'standards compliant mode'
      w = document.documentElement.clientWidth;
      h = document.documentElement.clientHeight;
    } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
      //IE 4 compatible
      w = document.body.clientWidth;
      h = document.body.clientHeight;
    }
    return [w,h];
  },
  getScrollXY : function () {
    var x = 0, y = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      y = window.pageYOffset;
      x = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      y = document.body.scrollTop;
      x = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      y = document.documentElement.scrollTop;
      x = document.documentElement.scrollLeft;
    }
    return [x,y];
  },
  /*
  *  initializes transition
  *
  *  @param DOMNode target element
  *  @param hash menu options
  */
  'initOpen' : function (el, mo, css, keys) {
    var tmp = el.className.split(" ");
    var cC = this.cssClasses;
    /*
    *  remove prev-set classes
    */
    for (var i=0, cCL = cC.length; i < cCL; i++) {
      tmp.splice(tmp.indexOf(cC[i]),1);
    }
    var ofst = this.getOffset(el),
    wh = this.getWindowWH(),
    xy = this.getScrollXY();
    if (el.offsetWidth+ofst[0] > wh[0]+xy[0]) tmp[tmp.length] = cC[0];
    if (ofst[0] < 0) tmp[tmp.length] = cC[1];
    if (el.offsetHeight+ofst[1] > wh[1]+xy[1]) tmp[tmp.length] = cC[2];
    if (ofst[1] < 0) tmp[tmp.length] = cC[3];
    el.className = tmp.join(" ");
  },
  finishClose : function (el) {
    var tmp = el.className.split(" ");
    for (var i=0, cL = this.cssClasses.length; i< cL; i++) {
      tmp.splice(tmp.indexOf(this.cssClasses[i]),1);
    }
    el.className = tmp.join(" ");
  }
}