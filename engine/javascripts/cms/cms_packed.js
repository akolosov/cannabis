CompleteMenuSolution=function(){var i=this;var I=null;var l=[];var O=[];var Q=[];var _={'root':'CmsListMenu','folder':'CmsMenuItemFolder','folderOpen':'CmsMenuItemFolderExpanded','folderClosed':'CmsMenuItemFolderCollapsed','menuItem':'CmsMenuItemFile','evenLevel':'CmsMenuItemEvenLevel','oddLevel':'CmsMenuItemOddLevel','menuLevel':'CmsMenuItemLevel'};var c={'theme':{'name':'','options':{}},'transitions':{},themeRootPath:null,maxDepth:0,maxOpenDepth:0,forceSkipTransitions:false,interval:10,length:100,openTimeout:0,closeTimeout:0,toggleMenuOnClick:0,closeSiblings:true,incrementalConvert:true,handlers:{onOpen:[],onClose:[],onChangeState:[]},stripCssClasses:{'root':[],'ul':[],'li':[],'a':[]},flagOpenClass:_['folderOpen'],flagClosedClass:_['folderClosed'],appendTemplateSuffix:false,dummy:null};var C={'cmsSelf':'__cmsSelf','openFlag':'__isOpen','interval':'__interval','timeout':'__timeout','isRoot':'__isRoot','isFolder':'__isFolder','parentNode':'__parentNode','submenu':'__submenu','menuLevel':'__menuLevel','activator':'__activator'};var v;this.setMenuOption=function(V,o){if(c[V]&&typeof c[V]!=typeof o)return false;c[V]=o;return true};this.initMenu=function(x,o){I=x;c.theme.merge(o.theme);if(o.themeRootPath)c.themeRootPath=o.themeRootPath;s.init(o);r();};this.getThemePath=function(X){if(!/^[-a-z0-9\/]*$/.test(name.toLowerCase()))return false;var o=c.theme.name.split('/');return gluePath(c.themeRootPath?c.themeRootPath:gluePath(i.cmsRoot,'templates'),(X?c.theme.name:o[0]));};this.reinitSubmenu=function(z){if(!z||!z.tagName)return;var o=c.maxDepth;switch(z.tagName.toLowerCase()){case"li":c.maxDepth=z[C['parentNode']][C['menuLevel']]+2;q(z[C['submenu']],z[C['parentNode']][C['menuLevel']]+1);break}c.maxDepth=o};var Z;var w=function(css,o){try{for(var t=css.length;t>=0;t--){if(c.stripCssClasses[o].indexOf(css[t])<0)continue;css.splice(t,1);}}catch(e){};return css};var W=function(node,o){var e={};if(isUndefined(o)||'string'!=typeof o)o=node.tagName.toLowerCase();for(var t=0,T=Q.length;t<T;t++){if(i.modifier[Q[t]].runat!=o||!isUndefined(e[Q[t]]))continue;i.modifier[Q[t]].mod.call(i.modifier[Q[t]],node,C,_,c);e[Q[t]]=true}e=null};var s=new function(){var e=this;var t=null;var T=document.getElementsByTagName('head')[0];var y=function(sn){if(!isUndefined(i.loadedStylesheets[sn]))return;T.appendChild(document.createElementExt('link',{'param':{'rel':'stylesheet','type':'text/css','href':sn}}));i.loadedStylesheets[sn]=true};var Y=function(sn){if(!isUndefined(i.loadedJS[sn]))return;T.appendChild(document.createElementExt('script',{'param':{'type':'text/javascript','defer':true,'src':sn}}));i.loadedJS[sn]=true};this.transitionOnload=function(u,o){if(o>=10000){i.transition[u]=true;return}if(!i.transition[u]){setTimeout(function(){e.transitionOnload(u,o+10)},10);return}O[O.length]=i.transition[u];if('function'==typeof i.transition[u].init)i.transition[u].init.call(i.transition[u],c,_,C);};this.themeOnload=function(u){O=[i.transition['default']];c.merge(t);for(var o in c.transitions){if(!c.transitions.hasOwnProperty(o))continue;if(!i.transition[o])Y(gluePath(i.cmsRoot,'transitions',o+'.js'));playTimeout(this.transitionOnload,1,[o,0]);}if(c.modifiers&&c.modifiers.length>0){for(var o=0,p=c.modifiers.length;o<p;o++){if(!i.modifier[c.modifiers[o]]){if(isUndefined(i.modifier[c.modifiers[o]]))i.modifier[c.modifiers[o]]=c.modifiers[o];Y(gluePath(i.cmsRoot,'modifiers',c.modifiers[o]+'.js'));}l[l.length]=['modifier',c.modifiers[o]];Q.push(c.modifiers[o]);}}};this.init=function(U){t=U;y(gluePath(i.getThemePath(),'layout.css'));y(gluePath(i.getThemePath(true),'design.css'));Y(gluePath(i.getThemePath(),'template.js'));var p=c.theme.name.split('/');if(isUndefined(i.theme[p[0]]))i.theme[p[0]]=p[0];l[l.length]=['theme',p[0]]}};var S=function(e){var o=getParent(e.srcElement||e.target,C.isRoot,true);if(o[C.cmsSelf]!=i)return;var z=getParent(e.srcElement||e.target,'li');if(!getParent(z,o))return;o=null;var t=z;while(z&&!z[C['parentNode']]&&t!=(t=getParent(z,C['isFolder'],true)))i.reinitSubmenu(t);if(!z)return;switch(e.type.toLowerCase()){case"mouseover":case"mouseout":while(!z[C['isRoot']]){if(z[C['isFolder']]){if(parseInt(z[C['timeout']]))clearTimeout(z[C['timeout']]);z[C['timeout']]=null;switch(e.type.toLowerCase()){case'mouseover':if(!z[C['openFlag']])z[C['timeout']]=playTimeout(k,c.openTimeout,[z,'open']);break;case'mouseout':if(z[C['openFlag']]&&parseInt(c.closeTimeout))z[C['timeout']]=playTimeout(k,c.closeTimeout,[z,'close']);break}}z=z[C['parentNode']]}break;case"mouseup":if(!z[C['isFolder']]||(z[C['submenu']][C['interval']]&&z[C['submenu']][C['interval']].interval))return;clearTimeout(z[C['timeout']]);if(c['toggleMenuOnClick']&&(c['toggleMenuOnClick']^z[C['openFlag']]*2))k(z,'toggle');break}};var k=function(z,o){var T,y,Y;if(o!='toggle'&&z[C['openFlag']]==(o=='open'))return;switch(o.toLowerCase()){case'open':o='Open';break;case'close':o='Close';break;case'toggle':o=z[C['openFlag']]?'Close':'Open';break;default:return}if(z[C['openFlag']]!=(o=='Open'))K(z,o);if(null==z[C['submenu']][C['menuLevel']])i.reinitSubmenu(z);T=z[C['openFlag']]=(o=='Open');if(c['closeSiblings']&&T)for(y=0,sL=z[C['parentNode']][C['submenu']].length;y<sL;y++)if(z[C['parentNode']][C['submenu']][y][C['openFlag']]&&z[C['parentNode']][C['submenu']][y]!=z&&z[C['parentNode']][C['submenu']][y][C['isFolder']])k(z[C['parentNode']][C['submenu']][y],'close');z=z[C['submenu']];Y=function(z,U,p){var y,e=U.length,t=p.length;var P=(new Date).valueOf();z[C['interval']].pg=Math.round(z[C['interval']].pg+(P-z[C['interval']].start)*100/c.length);z[C['interval']].start=P;if(z[C['interval']].pg>100)z[C['interval']].pg=100;z[C['interval']].pg_delta=z[C['interval']].pg/100;for(y=0;y<e;y++){if(null==U[y])continue;if(!U[y][0].call(U[y][1],z,c,_,C)){U.splice(y,1);y--;e--}}if(0==U.length){for(y=0;y<t;y++)p[y][0].call(p[y][1],z,c,_,C);clearInterval(z[C['interval']].interval);z[C['interval']].interval=false;c['forceSkipTransitions']=false}};if(z[C['interval']]){clearInterval(z[C['interval']].interval);z[C['interval']].pg=100-z[C['interval']].pg;z[C['interval']].pg_delta=z[C['interval']].pg/100}else{z[C['interval']]={'pg':0,'pg_delta':0}}var u,U=[],p=[];for(y=0,mL=O.length;y<mL;y++){u=O[y]['init'+o];if(typeof u=='function')u.call(O[y],z,c,_,C);u=O[y]['play'+o];if(!c['forceSkipTransitions']&&typeof u=='function')U[U.length]=[u,O[y]];u=O[y]['finish'+o];if(typeof u=='function')p[p.length]=[u,O[y]]}z[C['interval']].start=(new Date).valueOf();z[C['interval']].interval=setInterval(function(){Y(z,U,p)},c.interval);};var K=function(z,o){if(!c.handlers)return;var t=function(z,T){if(c.handlers[T]instanceof Array){for(var y=0,Y=c.handlers[T].length;y<Y;y++){try{c.handlers[T][y][1].call(c.handlers[T][y][0],z,C,_,c);}catch(e){}}}};var T='on'+o;t(z,T);t(z,'onChangeState');};var q=function(z,o){if(c.maxDepth&&o>c.maxDepth-1&&(z[C.parentNode]&&z[C.parentNode][C.openFlag]===false))return;z[C.menuLevel]=o;var e=document.createElement('div');z.parentNode.replaceChild(e,z);o++;z[C.submenu]=[];for(var t=0,T=z.childNodes.length;t<T;t++){if(!z.childNodes[t].tagName||z.childNodes[t].tagName.toLowerCase()!='li')continue;z[C.submenu][z[C.submenu].length]=z.childNodes[t];z.style.display='';z.childNodes[t][C.parentNode]=z;var y=z.childNodes[t].className.split(' ');z.childNodes[t][C.openFlag]=((o<c.maxOpenDepth||y.indexOf(c.flagOpenClass)>-1)&&y.indexOf(c.flagClosedClass)<0);y=w(y,'li');E(z.childNodes[t],o);if(!isUndefined(z.childNodes[t][C.submenu])){y[y.length]=_['folder'];y[y.length]=_[z.childNodes[t][C.openFlag]?'folderOpen':'folderClosed'];z.childNodes[t][C.isFolder]=true}else{y[y.length]=_.menuItem;z.childNodes[t][C.isFolder]=false}y[y.length]=_.menuLevel.split(" ").map(function(z){return z+o}).join(" ");y[y.length]=_[o%2?'evenLevel':'oddLevel'];z.childNodes[t].className=y.join(' ');W(z.childNodes[t]);var Y=z.childNodes[t].firstChild;while(null!=Y&&(!Y.tagName||(Y.tagName&&Y.tagName.toLowerCase()!='a')))Y=Y.nextSibling;if(Y){z.childNodes[t][C.activator]=Y;Y[C.parentNode]=z.childNodes[t];var y=Y.className.split(' ');y=w(y,'a');Y.className=y.join(" ");W(Y);}}if(z[C['submenu']].length<1&&z[C.parentNode]){z[C.parentNode][C.openFlag]=false}e.parentNode.replaceChild(z,e);e=null};var E=function(z,o){for(var e=0,t=z.childNodes.length;e<t;e++){if(!z.childNodes[e].tagName||z.childNodes[e].tagName.toLowerCase()!='ul')continue;var T=z.childNodes[e].className.split(" ");T=w(T,'ul');z.childNodes[e].className=T.join(" ");z[C['submenu']]=z.childNodes[e];z.childNodes[e][C['parentNode']]=z;if(!c.incrementalConvert||z[C['openFlag']]||o<c['maxDepth']-1)q(z[C['submenu']],o);W(z.childNodes[e]);}};var r=function(){var z=document.getElementById(I);if(!z||!R()){setTimeout(r,10);return}c.stripCssClasses.li.push(c.flagOpenClass);c.stripCssClasses.li.push(c.flagClosedClass);if(c.appendTemplateSuffix){var V=c.theme.name.split("/");var o=V[0];var V=V.join("");for(var e in _){if(_.hasOwnProperty(e)&&'root'!=e)_[e]=_[e]+o+' '+_[e]+V}}var t=z.className.split(" ");t=w(t,'root');t[t.length]=_.root;var V=c.theme.name.split("/");var o="";for(var e=0,T=V.length;e<T;e++){o+=V[e];t[t.length]=_.root+o}z.className=t.join(" ");z[C['isRoot']]=true;q(z,-1);if(c.openTimeout){z.attachEvent('onmouseover',S);z.attachEvent('onmouseout',S);}z.attachEvent('onmouseup',S);z.style.display='';W(z,'root');z[C['cmsSelf']]=i};var R=function(){var o,e=l.length,t;for(o=0;o<e;o++){if(isNaN(l[o][3]))l[o][3]=0;t=i[l[o][0]][l[o][1]];if('string'!=typeof t){if(t.menuOptions)c.merge(t.menuOptions,l[o][0]=='theme');if(t.init)t.init.call(t,c,_,C);if(s[l[o][0]+'Onload'])s[l[o][0]+'Onload'](l[o][1]);l.splice(o,1);o--;e--}else if(l[o][3]>=10000){throw Error("Resource could not be loaded: "+l[o][0]+" - "+l[o][1]);}else{l[o][3]+=10}}return!l.length}};CompleteMenuSolution.prototype.cmsRoot=findPath('cms_packed.js');CompleteMenuSolution.prototype.loadedStylesheets={};CompleteMenuSolution.prototype.loadedJS={};CompleteMenuSolution.prototype.theme={};CompleteMenuSolution.prototype.transition={'default':{'initOpen':function(el,i,I,l){el=el[l['parentNode']];var o=el.className.split(" "),O=I.folderClosed.split(" "),Q;for(var _=0,c=O.length;_<c;_++){Q=o.indexOf(O[_]);if(Q>-1)o.splice(Q,1);}O=I.folderOpen.split(" ");for(var _=0,c=O.length;_<c;_++){Q=o.indexOf(O[_]);if(Q>-1)o.splice(Q,1);}o[o.length]=I.folderOpen;el.className=o.join(" ");},'finishClose':function(el,i,I,l){el=el[l['parentNode']];var o=el.className.split(" "),O=I.folderOpen.split(" "),Q;for(var _=0,c=O.length;_<c;_++){Q=o.indexOf(O[_]);if(Q>-1)o.splice(Q,1);}O=I.folderClosed.split(" ");for(var _=0,c=O.length;_<c;_++){Q=o.indexOf(O[_]);if(Q>-1)o.splice(Q,1);}o[o.length]=I.folderClosed;el.className=o.join(" ");}}};CompleteMenuSolution.prototype.modifier={};CompleteMenuSolution.prototype.requires=['extensions/e.js',];for(var i=0,cL=CompleteMenuSolution.prototype.requires.length;i<cL;i++){try{document.write("<scr"+"ipt type=\"text/javascript\" src=\""+CompleteMenuSolution.prototype.cmsRoot+CompleteMenuSolution.prototype.requires[i]+"\" ></script>");}catch(e){var el=document.getElementsByTagName('head')[0],s=document.createElement('script');s.type="text/javascript";s.src=CompleteMenuSolution.prototype.cmsRoot+CompleteMenuSolution.prototype.requires[i];el.appendChild(s);}}function findPath(i){var I=document.getElementsByTagName('script'),l=new RegExp('^(.*/|)('+i+')([#?]|$)');for(var o=0,O=I.length;o<O;o++){var Q=String(I[o].src).match(l);if(Q){if(Q[1].match(/^((https?|file)\:\/{2,}|\w:[\\])/))return Q[1];if(Q[1].indexOf("/")==0)return Q[1];b=document.getElementsByTagName('base');if(b[0]&&b[0].href)return b[0].href+Q[1];return(document.location.pathname.match(/(.*[\/\\])/)[0]+Q[1]).replace(/^\/+(?=\w:)/,"");}}return null}