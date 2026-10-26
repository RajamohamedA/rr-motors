import{t as e}from"./axios-CFoX--h4.js";import{t}from"./bootstrap-icons-C_xVcBx8.js";window.axios=e,window.axios.defaults.headers.common[`X-Requested-With`]=`XMLHttpRequest`,t(),document.addEventListener(`DOMContentLoaded`,function(){let e=document.getElementById(`toggleSidebar`),t=document.getElementById(`sidebar`);e&&e.addEventListener(`click`,function(){t.classList.toggle(`collapsed`)})});var n=document.getElementById(`fullscreenBtn`);n&&(n.onclick=()=>{document.fullscreenElement?document.exitFullscreen():document.documentElement.requestFullscreen()});function r(e,t){if(e===void 0||!e.document)throw Error(`jQuery requires a window with a document`);var n=[],r=Object.getPrototypeOf,i=n.slice,a=n.flat?function(e){return n.flat.call(e)}:function(e){return n.concat.apply([],e)},o=n.push,s=n.indexOf,c={},l=c.toString,u=c.hasOwnProperty,d=u.toString,f=d.call(Object),p={};function m(e){return e==null?e+``:typeof e==`object`?c[l.call(e)]||`object`:typeof e}function h(e){return e!=null&&e===e.window}function g(e){var t=!!e&&e.length,n=m(e);return typeof e==`function`||h(e)?!1:n===`array`||t===0||typeof t==`number`&&t>0&&t-1 in e}var _=e.document,v={type:!0,src:!0,nonce:!0,noModule:!0};function y(e,t,n){n||=_;var r,i=n.createElement(`script`);for(r in i.text=e,v)t&&t[r]&&(i[r]=t[r]);n.head.appendChild(i).parentNode&&i.parentNode.removeChild(i)}var b=`4.0.0`,x=/HTML$/i,S=function(e,t){return new S.fn.init(e,t)};S.fn=S.prototype={jquery:b,constructor:S,length:0,toArray:function(){return i.call(this)},get:function(e){return e==null?i.call(this):e<0?this[e+this.length]:this[e]},pushStack:function(e){var t=S.merge(this.constructor(),e);return t.prevObject=this,t},each:function(e){return S.each(this,e)},map:function(e){return this.pushStack(S.map(this,function(t,n){return e.call(t,n,t)}))},slice:function(){return this.pushStack(i.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},even:function(){return this.pushStack(S.grep(this,function(e,t){return(t+1)%2}))},odd:function(){return this.pushStack(S.grep(this,function(e,t){return t%2}))},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(n>=0&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()}},S.extend=S.fn.extend=function(){var e,t,n,r,i,a,o=arguments[0]||{},s=1,c=arguments.length,l=!1;for(typeof o==`boolean`&&(l=o,o=arguments[s]||{},s++),typeof o!=`object`&&typeof o!=`function`&&(o={}),s===c&&(o=this,s--);s<c;s++)if((e=arguments[s])!=null)for(t in e)r=e[t],!(t===`__proto__`||o===r)&&(l&&r&&(S.isPlainObject(r)||(i=Array.isArray(r)))?(n=o[t],a=i&&!Array.isArray(n)?[]:!i&&!S.isPlainObject(n)?{}:n,i=!1,o[t]=S.extend(l,a,r)):r!==void 0&&(o[t]=r));return o},S.extend({expando:`jQuery`+(b+Math.random()).replace(/\D/g,``),isReady:!0,error:function(e){throw Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return!e||l.call(e)!==`[object Object]`?!1:(t=r(e),t?(n=u.call(t,`constructor`)&&t.constructor,typeof n==`function`&&d.call(n)===f):!0)},isEmptyObject:function(e){for(var t in e)return!1;return!0},globalEval:function(e,t,n){y(e,{nonce:t&&t.nonce},n)},each:function(e,t){var n,r=0;if(g(e))for(n=e.length;r<n&&t.call(e[r],r,e[r])!==!1;r++);else for(r in e)if(t.call(e[r],r,e[r])===!1)break;return e},text:function(e){var t,n=``,r=0,i=e.nodeType;if(!i)for(;t=e[r++];)n+=S.text(t);return i===1||i===11?e.textContent:i===9?e.documentElement.textContent:i===3||i===4?e.nodeValue:n},makeArray:function(e,t){var n=t||[];return e!=null&&(g(Object(e))?S.merge(n,typeof e==`string`?[e]:e):o.call(n,e)),n},inArray:function(e,t,n){return t==null?-1:s.call(t,e,n)},isXMLDoc:function(e){var t=e&&e.namespaceURI,n=e&&(e.ownerDocument||e).documentElement;return!x.test(t||n&&n.nodeName||`HTML`)},contains:function(e,t){var n=t&&t.parentNode;return e===n||!!(n&&n.nodeType===1&&(e.contains?e.contains(n):e.compareDocumentPosition&&e.compareDocumentPosition(n)&16))},merge:function(e,t){for(var n=+t.length,r=0,i=e.length;r<n;r++)e[i++]=t[r];return e.length=i,e},grep:function(e,t,n){for(var r,i=[],a=0,o=e.length,s=!n;a<o;a++)r=!t(e[a],a),r!==s&&i.push(e[a]);return i},map:function(e,t,n){var r,i,o=0,s=[];if(g(e))for(r=e.length;o<r;o++)i=t(e[o],o,n),i!=null&&s.push(i);else for(o in e)i=t(e[o],o,n),i!=null&&s.push(i);return a(s)},guid:1,support:p}),typeof Symbol==`function`&&(S.fn[Symbol.iterator]=n[Symbol.iterator]),S.each(`Boolean Number String Function Array Date RegExp Object Error Symbol`.split(` `),function(e,t){c[`[object `+t+`]`]=t.toLowerCase()});function C(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}var w=n.pop,T=`[\\x20\\t\\r\\n\\f]`,E=_.documentMode,ee=E&&RegExp(`:enabled|:disabled|\\[`+T+`*name`+T+`*=`+T+`*(?:''|"")`),D=RegExp(`^`+T+`+|((?:^|[^\\\\])(?:\\\\.)*)`+T+`+$`,`g`),O=`(?:\\\\[\\da-fA-F]{1,6}`+T+`?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+`,k=RegExp(`^`+T+`*([>+~]|`+T+`)`+T+`*`),te=RegExp(T+`|>`),ne=/[+~]/,A=_.documentElement,j=A.matches||A.msMatchesSelector;function M(){var e=[];function t(n,r){return e.push(n+` `)>S.expr.cacheLength&&delete t[e.shift()],t[n+` `]=r}return t}function N(e){return e&&e.getElementsByTagName!==void 0&&e}var P=`\\[`+T+`*(`+O+`)(?:`+T+`*([*^$|!~]?=)`+T+`*(?:'((?:\\\\.|[^\\\\'])*)'|"((?:\\\\.|[^\\\\"])*)"|(`+O+`))|)`+T+`*\\]`,F=`:(`+O+`)(?:\\((('((?:\\\\.|[^\\\\'])*)'|"((?:\\\\.|[^\\\\"])*)")|((?:\\\\.|[^\\\\()[\\]]|`+P+`)*)|.*)\\)|)`,re={ID:RegExp(`^#(`+O+`)`),CLASS:RegExp(`^\\.(`+O+`)`),TAG:RegExp(`^(`+O+`|[*])`),ATTR:RegExp(`^`+P),PSEUDO:RegExp(`^`+F),CHILD:RegExp(`^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(`+T+`*(even|odd|(([+-]|)(\\d*)n|)`+T+`*(?:([+-]|)`+T+`*(\\d+)|))`+T+`*\\)|)`,`i`)},ie=new RegExp(F),ae=RegExp(`\\\\[\\da-fA-F]{1,6}`+T+`?|\\\\([^\\r\\n\\f])`,`g`),oe=function(e,t){var n=`0x`+e.slice(1)-65536;return t||(n<0?String.fromCharCode(n+65536):String.fromCharCode(n>>10|55296,n&1023|56320))};function I(e){return e.replace(ae,oe)}function se(e){S.error(`Syntax error, unrecognized expression: `+e)}var ce=RegExp(`^`+T+`*,`+T+`*`),le=M();function ue(e,t){var n,r,i,a,o,s,c,l=le[e+` `];if(l)return t?0:l.slice(0);for(o=e,s=[],c=S.expr.preFilter;o;){for(a in(!n||(r=ce.exec(o)))&&(r&&(o=o.slice(r[0].length)||o),s.push(i=[])),n=!1,(r=k.exec(o))&&(n=r.shift(),i.push({value:n,type:r[0].replace(D,` `)}),o=o.slice(n.length)),re)(r=S.expr.match[a].exec(o))&&(!c[a]||(r=c[a](r)))&&(n=r.shift(),i.push({value:n,type:a,matches:r}),o=o.slice(n.length));if(!n)break}return t?o.length:o?se(e):le(e,s).slice(0)}var de={ATTR:function(e){return e[1]=I(e[1]),e[3]=I(e[3]||e[4]||e[5]||``),e[2]===`~=`&&(e[3]=` `+e[3]+` `),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),e[1].slice(0,3)===`nth`?(e[3]||se(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*(e[3]===`even`||e[3]===`odd`)),e[5]=+(e[7]+e[8]||e[3]===`odd`)):e[3]&&se(e[0]),e},PSEUDO:function(e){var t,n=!e[6]&&e[2];return re.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||``:n&&ie.test(n)&&(t=ue(n,!0))&&(t=n.indexOf(`)`,n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}};function fe(e){for(var t=0,n=e.length,r=``;t<n;t++)r+=e[t].value;return r}function L(e,t,n,r,i,a,o){var s=0,c=e.length,l=n==null;if(m(n)===`object`)for(s in i=!0,n)L(e,t,s,n[s],!0,a,o);else if(r!==void 0&&(i=!0,typeof r!=`function`&&(o=!0),l&&(o?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(S(e),n)})),t))for(;s<c;s++)t(e[s],n,o?r:r.call(e[s],s,t(e[s],n)));return i?e:l?t.call(e):c?t(e[0],n):a}var R=/[^\x20\t\r\n\f]+/g;S.fn.extend({attr:function(e,t){return L(this,S.attr,e,t,arguments.length>1)},removeAttr:function(e){return this.each(function(){S.removeAttr(this,e)})}}),S.extend({attr:function(e,t,n){var r,i,a=e.nodeType;if(!(a===3||a===8||a===2)){if(e.getAttribute===void 0)return S.prop(e,t,n);if((a!==1||!S.isXMLDoc(e))&&(i=S.attrHooks[t.toLowerCase()]),n!==void 0){if(n===null||n===!1&&t.toLowerCase().indexOf(`aria-`)!==0){S.removeAttr(e,t);return}return i&&`set`in i&&(r=i.set(e,n,t))!==void 0?r:(e.setAttribute(t,n),n)}return i&&`get`in i&&(r=i.get(e,t))!==null?r:(r=e.getAttribute(t),r??void 0)}},attrHooks:{},removeAttr:function(e,t){var n,r=0,i=t&&t.match(R);if(i&&e.nodeType===1)for(;n=i[r++];)e.removeAttribute(n)}}),E&&(S.attrHooks.type={set:function(e,t){if(t===`radio`&&C(e,`input`)){var n=e.value;return e.setAttribute(`type`,t),n&&(e.value=n),t}}});var z=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\x80-\uFFFF\w-]/g;function pe(e,t){return t?e===`\0`?`�`:e.slice(0,-1)+`\\`+e.charCodeAt(e.length-1).toString(16)+` `:`\\`+e}S.escapeSelector=function(e){return(e+``).replace(z,pe)};var me=n.sort,he=n.splice,ge;function _e(e,t){if(e===t)return ge=!0,0;var n=!e.compareDocumentPosition-!t.compareDocumentPosition;return n||(n=(e.ownerDocument||e)==(t.ownerDocument||t)?e.compareDocumentPosition(t):1,n&1?e==_||e.ownerDocument==_&&S.contains(_,e)?-1:t==_||t.ownerDocument==_&&S.contains(_,t)?1:0:n&4?-1:1)}S.uniqueSort=function(e){var t,n=[],r=0,i=0;if(ge=!1,me.call(e,_e),ge){for(;t=e[i++];)t===e[i]&&(r=n.push(i));for(;r--;)he.call(e,n[r],1)}return e},S.fn.uniqueSort=function(){return this.pushStack(S.uniqueSort(i.apply(this)))};var ve,ye,B,be,V,H=0,xe=0,Se=M(),Ce=M(),we=M(),Te=RegExp(T+`+`,`g`),Ee=RegExp(`^`+O+`$`),De=S.extend({needsContext:RegExp(`^`+T+`*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(`+T+`*((?:-\\d)?\\d*)`+T+`*\\)|)(?=[^-]|$)`,`i`)},re),Oe=/^(?:input|select|textarea|button)$/i,ke=/^h\d$/i,Ae=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,je=function(){Le()},Me=ze(function(e){return e.disabled===!0&&C(e,`fieldset`)},{dir:`parentNode`,next:`legend`});function U(e,t,n,r){var i,a,s,c,l,u,d,f=t&&t.ownerDocument,p=t?t.nodeType:9;if(n||=[],typeof e!=`string`||!e||p!==1&&p!==9&&p!==11)return n;if(!r&&(Le(t),t||=B,V)){if(p!==11&&(l=Ae.exec(e))){if(i=l[1]){if(p===9)return(s=t.getElementById(i))&&o.call(n,s),n;if(f&&(s=f.getElementById(i))&&S.contains(t,s))return o.call(n,s),n}else if(l[2])return o.apply(n,t.getElementsByTagName(e)),n;else if((i=l[3])&&t.getElementsByClassName)return o.apply(n,t.getElementsByClassName(i)),n}if(!we[e+` `]&&(!ee||!ee.test(e))){if(d=e,f=t,p===1&&(te.test(e)||k.test(e))){for(f=ne.test(e)&&N(t.parentNode)||t,(f!=t||E)&&((c=t.getAttribute(`id`))?c=S.escapeSelector(c):t.setAttribute(`id`,c=S.expando)),u=ue(e),a=u.length;a--;)u[a]=(c?`#`+c:`:scope`)+` `+fe(u[a]);d=u.join(`,`)}try{return o.apply(n,f.querySelectorAll(d)),n}catch{we(e,!0)}finally{c===S.expando&&t.removeAttribute(`id`)}}}return qe(e.replace(D,`$1`),t,n,r)}function W(e){return e[S.expando]=!0,e}function Ne(e){return function(t){return C(t,`input`)&&t.type===e}}function Pe(e){return function(t){return(C(t,`input`)||C(t,`button`))&&t.type===e}}function Fe(e){return function(t){return`form`in t?t.parentNode&&t.disabled===!1?`label`in t?`label`in t.parentNode?t.parentNode.disabled===e:t.disabled===e:t.isDisabled===e||t.isDisabled!==!e&&Me(t)===e:t.disabled===e:`label`in t&&t.disabled===e}}function Ie(e){return W(function(t){return t=+t,W(function(n,r){for(var i,a=e([],n.length,t),o=a.length;o--;)n[i=a[o]]&&(n[i]=!(r[i]=n[i]))})})}function Le(e){var t,n=e?e.ownerDocument||e:_;n==B||n.nodeType!==9||(B=n,be=B.documentElement,V=!S.isXMLDoc(B),E&&_!=B&&(t=B.defaultView)&&t.top!==t&&t.addEventListener(`unload`,je))}for(ve in U.matches=function(e,t){return U(e,null,null,t)},U.matchesSelector=function(e,t){if(Le(e),V&&!we[t+` `]&&(!ee||!ee.test(t)))try{return j.call(e,t)}catch{we(t,!0)}return U(t,B,null,[e]).length>0},S.expr={cacheLength:50,createPseudo:W,match:De,find:{ID:function(e,t){if(t.getElementById!==void 0&&V){var n=t.getElementById(e);return n?[n]:[]}},TAG:function(e,t){return t.getElementsByTagName===void 0?t.querySelectorAll(e):t.getElementsByTagName(e)},CLASS:function(e,t){if(t.getElementsByClassName!==void 0&&V)return t.getElementsByClassName(e)}},relative:{">":{dir:`parentNode`,first:!0}," ":{dir:`parentNode`},"+":{dir:`previousSibling`,first:!0},"~":{dir:`previousSibling`}},preFilter:de,filter:{ID:function(e){var t=I(e);return function(e){return e.getAttribute(`id`)===t}},TAG:function(e){var t=I(e).toLowerCase();return e===`*`?function(){return!0}:function(e){return C(e,t)}},CLASS:function(e){var t=Se[e+` `];return t||(t=RegExp(`(^|`+T+`)`+e+`(`+T+`|$)`))&&Se(e,function(e){return t.test(typeof e.className==`string`&&e.className||e.getAttribute!==void 0&&e.getAttribute(`class`)||``)})},ATTR:function(e,t,n){return function(r){var i=S.attr(r,e);return i==null?t===`!=`:t?(i+=``,t===`=`?i===n:t===`!=`?i!==n:t===`^=`?n&&i.indexOf(n)===0:t===`*=`?n&&i.indexOf(n)>-1:t===`$=`?n&&i.slice(-n.length)===n:t===`~=`?(` `+i.replace(Te,` `)+` `).indexOf(n)>-1:t===`|=`?i===n||i.slice(0,n.length+1)===n+`-`:!1):!0}},CHILD:function(e,t,n,r,i){var a=e.slice(0,3)!==`nth`,o=e.slice(-4)!==`last`,s=t===`of-type`;return r===1&&i===0?function(e){return!!e.parentNode}:function(t,n,c){var l,u,d,f,p,m=a===o?`previousSibling`:`nextSibling`,h=t.parentNode,g=s&&t.nodeName.toLowerCase(),_=!c&&!s,v=!1;if(h){if(a){for(;m;){for(d=t;d=d[m];)if(s?C(d,g):d.nodeType===1)return!1;p=m=e===`only`&&!p&&`nextSibling`}return!0}if(p=[o?h.firstChild:h.lastChild],o&&_){for(u=h[S.expando]||(h[S.expando]={}),l=u[e]||[],f=l[0]===H&&l[1],v=f&&l[2],d=f&&h.childNodes[f];d=++f&&d&&d[m]||(v=f=0)||p.pop();)if(d.nodeType===1&&++v&&d===t){u[e]=[H,f,v];break}}else if(_&&(u=t[S.expando]||(t[S.expando]={}),l=u[e]||[],f=l[0]===H&&l[1],v=f),v===!1)for(;(d=++f&&d&&d[m]||(v=f=0)||p.pop())&&!((s?C(d,g):d.nodeType===1)&&++v&&(_&&(u=d[S.expando]||(d[S.expando]={}),u[e]=[H,v]),d===t)););return v-=i,v===r||v%r===0&&v/r>=0}}},PSEUDO:function(e,t){var n=S.expr.pseudos[e]||S.expr.setFilters[e.toLowerCase()]||se(`unsupported pseudo: `+e);return n[S.expando]?n(t):n}},pseudos:{not:W(function(e){var t=[],n=[],r=Ke(e.replace(D,`$1`));return r[S.expando]?W(function(e,t,n,i){for(var a,o=r(e,null,i,[]),s=e.length;s--;)(a=o[s])&&(e[s]=!(t[s]=a))}):function(e,i,a){return t[0]=e,r(t,null,a,n),t[0]=null,!n.pop()}}),has:W(function(e){return function(t){return U(e,t).length>0}}),contains:W(function(e){return e=I(e),function(t){return(t.textContent||S.text(t)).indexOf(e)>-1}}),lang:W(function(e){return Ee.test(e||``)||se(`unsupported lang: `+e),e=I(e).toLowerCase(),function(t){var n;do if(n=V?t.lang:t.getAttribute(`xml:lang`)||t.getAttribute(`lang`))return n=n.toLowerCase(),n===e||n.indexOf(e+`-`)===0;while((t=t.parentNode)&&t.nodeType===1);return!1}}),target:function(t){var n=e.location&&e.location.hash;return n&&n.slice(1)===t.id},root:function(e){return e===be},focus:function(e){return e===B.activeElement&&B.hasFocus()&&!!(e.type||e.href||~e.tabIndex)},enabled:Fe(!1),disabled:Fe(!0),checked:function(e){return C(e,`input`)&&!!e.checked||C(e,`option`)&&!!e.selected},selected:function(e){return E&&e.parentNode&&e.parentNode.selectedIndex,e.selected===!0},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!S.expr.pseudos.empty(e)},header:function(e){return ke.test(e.nodeName)},input:function(e){return Oe.test(e.nodeName)},button:function(e){return C(e,`input`)&&e.type===`button`||C(e,`button`)},text:function(e){return C(e,`input`)&&e.type===`text`},first:Ie(function(){return[0]}),last:Ie(function(e,t){return[t-1]}),eq:Ie(function(e,t,n){return[n<0?n+t:n]}),even:Ie(function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e}),odd:Ie(function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e}),lt:Ie(function(e,t,n){for(var r=n<0?n+t:n>t?t:n;--r>=0;)e.push(r);return e}),gt:Ie(function(e,t,n){for(var r=n<0?n+t:n;++r<t;)e.push(r);return e})}},S.expr.pseudos.nth=S.expr.pseudos.eq,{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})S.expr.pseudos[ve]=Ne(ve);for(ve in{submit:!0,reset:!0})S.expr.pseudos[ve]=Pe(ve);function Re(){}Re.prototype=S.expr.pseudos,S.expr.setFilters=new Re;function ze(e,t,n){var r=t.dir,i=t.next,a=i||r,o=n&&a===`parentNode`,s=xe++;return t.first?function(t,n,i){for(;t=t[r];)if(t.nodeType===1||o)return e(t,n,i);return!1}:function(t,n,c){var l,u,d=[H,s];if(c){for(;t=t[r];)if((t.nodeType===1||o)&&e(t,n,c))return!0}else for(;t=t[r];)if(t.nodeType===1||o){if(u=t[S.expando]||(t[S.expando]={}),i&&C(t,i))t=t[r]||t;else if((l=u[a])&&l[0]===H&&l[1]===s)return d[2]=l[2];else if(u[a]=d,d[2]=e(t,n,c))return!0}return!1}}function Be(e){return e.length>1?function(t,n,r){for(var i=e.length;i--;)if(!e[i](t,n,r))return!1;return!0}:e[0]}function Ve(e,t,n){for(var r=0,i=t.length;r<i;r++)U(e,t[r],n);return n}function He(e,t,n,r,i){for(var a,o=[],s=0,c=e.length,l=t!=null;s<c;s++)(a=e[s])&&(!n||n(a,r,i))&&(o.push(a),l&&t.push(s));return o}function Ue(e,t,n,r,i,a){return r&&!r[S.expando]&&(r=Ue(r)),i&&!i[S.expando]&&(i=Ue(i,a)),W(function(a,c,l,u){var d,f,p,m,h=[],g=[],_=c.length,v=a||Ve(t||`*`,l.nodeType?[l]:l,[]),y=e&&(a||!t)?He(v,h,e,l,u):v;if(n?(m=i||(a?e:_||r)?[]:c,n(y,m,l,u)):m=y,r)for(d=He(m,g),r(d,[],l,u),f=d.length;f--;)(p=d[f])&&(m[g[f]]=!(y[g[f]]=p));if(a){if(i||e){if(i){for(d=[],f=m.length;f--;)(p=m[f])&&d.push(y[f]=p);i(null,m=[],d,u)}for(f=m.length;f--;)(p=m[f])&&(d=i?s.call(a,p):h[f])>-1&&(a[d]=!(c[d]=p))}}else m=He(m===c?m.splice(_,m.length):m),i?i(null,c,m,u):o.apply(c,m)})}function We(e){for(var t,n,r,i=e.length,a=S.expr.relative[e[0].type],o=a||S.expr.relative[` `],c=+!!a,l=ze(function(e){return e===t},o,!0),u=ze(function(e){return s.call(t,e)>-1},o,!0),d=[function(e,n,r){var i=!a&&(r||n!=ye)||((t=n).nodeType?l(e,n,r):u(e,n,r));return t=null,i}];c<i;c++)if(n=S.expr.relative[e[c].type])d=[ze(Be(d),n)];else{if(n=S.expr.filter[e[c].type].apply(null,e[c].matches),n[S.expando]){for(r=++c;r<i&&!S.expr.relative[e[r].type];r++);return Ue(c>1&&Be(d),c>1&&fe(e.slice(0,c-1).concat({value:e[c-2].type===` `?`*`:``})).replace(D,`$1`),n,c<r&&We(e.slice(c,r)),r<i&&We(e=e.slice(r)),r<i&&fe(e))}d.push(n)}return Be(d)}function Ge(e,t){var n=t.length>0,r=e.length>0,i=function(i,a,s,c,l){var u,d,f,p=0,m=`0`,h=i&&[],g=[],_=ye,v=i||r&&S.expr.find.TAG(`*`,l),y=H+=_==null?1:Math.random()||.1;for(l&&(ye=a==B||a||l);(u=v[m])!=null;m++){if(r&&u){for(d=0,!a&&u.ownerDocument!=B&&(Le(u),s=!V);f=e[d++];)if(f(u,a||B,s)){o.call(c,u);break}l&&(H=y)}n&&((u=!f&&u)&&p--,i&&h.push(u))}if(p+=m,n&&m!==p){for(d=0;f=t[d++];)f(h,g,a,s);if(i){if(p>0)for(;m--;)h[m]||g[m]||(g[m]=w.call(c));g=He(g)}o.apply(c,g),l&&!i&&g.length>0&&p+t.length>1&&S.uniqueSort(c)}return l&&(H=y,ye=_),h};return n?W(i):i}function Ke(e,t){var n,r=[],i=[],a=Ce[e+` `];if(!a){for(t||=ue(e),n=t.length;n--;)a=We(t[n]),a[S.expando]?r.push(a):i.push(a);a=Ce(e,Ge(i,r)),a.selector=e}return a}function qe(e,t,n,r){var i,a,s,c,l,u=typeof e==`function`&&e,d=!r&&ue(e=u.selector||e);if(n||=[],d.length===1){if(a=d[0]=d[0].slice(0),a.length>2&&(s=a[0]).type===`ID`&&t.nodeType===9&&V&&S.expr.relative[a[1].type]){if(t=(S.expr.find.ID(I(s.matches[0]),t)||[])[0],t)u&&(t=t.parentNode);else return n;e=e.slice(a.shift().value.length)}for(i=De.needsContext.test(e)?0:a.length;i--&&(s=a[i],!S.expr.relative[c=s.type]);)if((l=S.expr.find[c])&&(r=l(I(s.matches[0]),ne.test(a[0].type)&&N(t.parentNode)||t))){if(a.splice(i,1),e=r.length&&fe(a),!e)return o.apply(n,r),n;break}}return(u||Ke(e,d))(r,t,!V,n,!t||ne.test(e)&&N(t.parentNode)||t),n}Le(),S.find=U,U.compile=Ke,U.select=qe,U.setDocument=Le,U.tokenize=ue;function Je(e,t,n){for(var r=[],i=n!==void 0;(e=e[t])&&e.nodeType!==9;)if(e.nodeType===1){if(i&&S(e).is(n))break;r.push(e)}return r}function Ye(e,t){for(var n=[];e;e=e.nextSibling)e.nodeType===1&&e!==t&&n.push(e);return n}var Xe=S.expr.match.needsContext,Ze=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;function Qe(e){return e[0]===`<`&&e[e.length-1]===`>`&&e.length>=3}function $e(e,t,n){return typeof t==`function`?S.grep(e,function(e,r){return!!t.call(e,r,e)!==n}):t.nodeType?S.grep(e,function(e){return e===t!==n}):typeof t==`string`?S.filter(t,e,n):S.grep(e,function(e){return s.call(t,e)>-1!==n})}S.filter=function(e,t,n){var r=t[0];return n&&(e=`:not(`+e+`)`),t.length===1&&r.nodeType===1?S.find.matchesSelector(r,e)?[r]:[]:S.find.matches(e,S.grep(t,function(e){return e.nodeType===1}))},S.fn.extend({find:function(e){var t,n,r=this.length,i=this;if(typeof e!=`string`)return this.pushStack(S(e).filter(function(){for(t=0;t<r;t++)if(S.contains(i[t],this))return!0}));for(n=this.pushStack([]),t=0;t<r;t++)S.find(e,i[t],n);return r>1?S.uniqueSort(n):n},filter:function(e){return this.pushStack($e(this,e||[],!1))},not:function(e){return this.pushStack($e(this,e||[],!0))},is:function(e){return!!$e(this,typeof e==`string`&&Xe.test(e)?S(e):e||[],!1).length}});var et,tt=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/,nt=S.fn.init=function(e,t){var n,r;if(!e)return this;if(e.nodeType)return this[0]=e,this.length=1,this;if(typeof e==`function`)return et.ready===void 0?e(S):et.ready(e);if(n=e+``,Qe(n))n=[null,e,null];else if(typeof e==`string`)n=tt.exec(e);else return S.makeArray(e,this);if(n&&(n[1]||!t))if(n[1]){if(t=t instanceof S?t[0]:t,S.merge(this,S.parseHTML(n[1],t&&t.nodeType?t.ownerDocument||t:_,!0)),Ze.test(n[1])&&S.isPlainObject(t))for(n in t)typeof this[n]==`function`?this[n](t[n]):this.attr(n,t[n]);return this}else return r=_.getElementById(n[2]),r&&(this[0]=r,this.length=1),this;else if(!t||t.jquery)return(t||et).find(e);else return this.constructor(t).find(e)};nt.prototype=S.fn,et=S(_);var rt=/^(?:parents|prev(?:Until|All))/,it={children:!0,contents:!0,next:!0,prev:!0};S.fn.extend({has:function(e){var t=S(e,this),n=t.length;return this.filter(function(){for(var e=0;e<n;e++)if(S.contains(this,t[e]))return!0})},closest:function(e,t){var n,r=0,i=this.length,a=[],o=typeof e!=`string`&&S(e);if(!Xe.test(e)){for(;r<i;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(o?o.index(n)>-1:n.nodeType===1&&S.find.matchesSelector(n,e))){a.push(n);break}}return this.pushStack(a.length>1?S.uniqueSort(a):a)},index:function(e){return e?typeof e==`string`?s.call(S(e),this[0]):s.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){return this.pushStack(S.uniqueSort(S.merge(this.get(),S(e,t))))},addBack:function(e){return this.add(e==null?this.prevObject:this.prevObject.filter(e))}});function at(e,t){for(;(e=e[t])&&e.nodeType!==1;);return e}S.each({parent:function(e){var t=e.parentNode;return t&&t.nodeType!==11?t:null},parents:function(e){return Je(e,`parentNode`)},parentsUntil:function(e,t,n){return Je(e,`parentNode`,n)},next:function(e){return at(e,`nextSibling`)},prev:function(e){return at(e,`previousSibling`)},nextAll:function(e){return Je(e,`nextSibling`)},prevAll:function(e){return Je(e,`previousSibling`)},nextUntil:function(e,t,n){return Je(e,`nextSibling`,n)},prevUntil:function(e,t,n){return Je(e,`previousSibling`,n)},siblings:function(e){return Ye((e.parentNode||{}).firstChild,e)},children:function(e){return Ye(e.firstChild)},contents:function(e){return e.contentDocument!=null&&r(e.contentDocument)?e.contentDocument:(C(e,`template`)&&(e=e.content||e),S.merge([],e.childNodes))}},function(e,t){S.fn[e]=function(n,r){var i=S.map(this,t,n);return e.slice(-5)!==`Until`&&(r=n),r&&typeof r==`string`&&(i=S.filter(r,i)),this.length>1&&(it[e]||S.uniqueSort(i),rt.test(e)&&i.reverse()),this.pushStack(i)}});function ot(e){var t={};return S.each(e.match(R)||[],function(e,n){t[n]=!0}),t}S.Callbacks=function(e){e=typeof e==`string`?ot(e):S.extend({},e);var t,n,r,i,a=[],o=[],s=-1,c=function(){for(i||=e.once,r=t=!0;o.length;s=-1)for(n=o.shift();++s<a.length;)a[s].apply(n[0],n[1])===!1&&e.stopOnFalse&&(s=a.length,n=!1);e.memory||(n=!1),t=!1,i&&(a=n?[]:``)},l={add:function(){return a&&(n&&!t&&(s=a.length-1,o.push(n)),(function t(n){S.each(n,function(n,r){typeof r==`function`?(!e.unique||!l.has(r))&&a.push(r):r&&r.length&&m(r)!==`string`&&t(r)})})(arguments),n&&!t&&c()),this},remove:function(){return S.each(arguments,function(e,t){for(var n;(n=S.inArray(t,a,n))>-1;)a.splice(n,1),n<=s&&s--}),this},has:function(e){return e?S.inArray(e,a)>-1:a.length>0},empty:function(){return a&&=[],this},disable:function(){return i=o=[],a=n=``,this},disabled:function(){return!a},lock:function(){return i=o=[],!n&&!t&&(a=n=``),this},locked:function(){return!!i},fireWith:function(e,n){return i||(n||=[],n=[e,n.slice?n.slice():n],o.push(n),t||c()),this},fire:function(){return l.fireWith(this,arguments),this},fired:function(){return!!r}};return l};function st(e){return e}function ct(e){throw e}function lt(e,t,n,r){var i;try{e&&typeof(i=e.promise)==`function`?i.call(e).done(t).fail(n):e&&typeof(i=e.then)==`function`?i.call(e,t,n):t.apply(void 0,[e].slice(r))}catch(e){n(e)}}S.extend({Deferred:function(t){var n=[[`notify`,`progress`,S.Callbacks(`memory`),S.Callbacks(`memory`),2],[`resolve`,`done`,S.Callbacks(`once memory`),S.Callbacks(`once memory`),0,`resolved`],[`reject`,`fail`,S.Callbacks(`once memory`),S.Callbacks(`once memory`),1,`rejected`]],r=`pending`,i={state:function(){return r},always:function(){return a.done(arguments).fail(arguments),this},catch:function(e){return i.then(null,e)},pipe:function(){var e=arguments;return S.Deferred(function(t){S.each(n,function(n,r){var i=typeof e[r[4]]==`function`&&e[r[4]];a[r[1]](function(){var e=i&&i.apply(this,arguments);e&&typeof e.promise==`function`?e.promise().progress(t.notify).done(t.resolve).fail(t.reject):t[r[0]+`With`](this,i?[e]:arguments)})}),e=null}).promise()},then:function(t,r,i){var a=0;function o(t,n,r,i){return function(){var s=this,c=arguments,l=function(){var e,l;if(!(t<a)){if(e=r.apply(s,c),e===n.promise())throw TypeError(`Thenable self-resolution`);l=e&&(typeof e==`object`||typeof e==`function`)&&e.then,typeof l==`function`?i?l.call(e,o(a,n,st,i),o(a,n,ct,i)):(a++,l.call(e,o(a,n,st,i),o(a,n,ct,i),o(a,n,st,n.notifyWith))):(r!==st&&(s=void 0,c=[e]),(i||n.resolveWith)(s,c))}},u=i?l:function(){try{l()}catch(e){S.Deferred.exceptionHook&&S.Deferred.exceptionHook(e,u.error),t+1>=a&&(r!==ct&&(s=void 0,c=[e]),n.rejectWith(s,c))}};t?u():(S.Deferred.getErrorHook&&(u.error=S.Deferred.getErrorHook()),e.setTimeout(u))}}return S.Deferred(function(e){n[0][3].add(o(0,e,typeof i==`function`?i:st,e.notifyWith)),n[1][3].add(o(0,e,typeof t==`function`?t:st)),n[2][3].add(o(0,e,typeof r==`function`?r:ct))}).promise()},promise:function(e){return e==null?i:S.extend(e,i)}},a={};return S.each(n,function(e,t){var o=t[2],s=t[5];i[t[1]]=o.add,s&&o.add(function(){r=s},n[3-e][2].disable,n[3-e][3].disable,n[0][2].lock,n[0][3].lock),o.add(t[3].fire),a[t[0]]=function(){return a[t[0]+`With`](this===a?void 0:this,arguments),this},a[t[0]+`With`]=o.fireWith}),i.promise(a),t&&t.call(a,a),a},when:function(e){var t=arguments.length,n=t,r=Array(n),a=i.call(arguments),o=S.Deferred(),s=function(e){return function(n){r[e]=this,a[e]=arguments.length>1?i.call(arguments):n,--t||o.resolveWith(r,a)}};if(t<=1&&(lt(e,o.done(s(n)).resolve,o.reject,!t),o.state()===`pending`||typeof(a[n]&&a[n].then)==`function`))return o.then();for(;n--;)lt(a[n],s(n),o.reject);return o.promise()}});var ut=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;S.Deferred.exceptionHook=function(t,n){t&&ut.test(t.name)&&e.console.warn(`jQuery.Deferred exception`,t,n)},S.readyException=function(t){e.setTimeout(function(){throw t})};var dt=S.Deferred();S.fn.ready=function(e){return dt.then(e).catch(function(e){S.readyException(e)}),this},S.extend({isReady:!1,readyWait:1,ready:function(e){(e===!0?--S.readyWait:S.isReady)||(S.isReady=!0,!(e!==!0&&--S.readyWait>0)&&dt.resolveWith(_,[S]))}}),S.ready.then=dt.then;function ft(){_.removeEventListener(`DOMContentLoaded`,ft),e.removeEventListener(`load`,ft),S.ready()}_.readyState===`loading`?(_.addEventListener(`DOMContentLoaded`,ft),e.addEventListener(`load`,ft)):e.setTimeout(S.ready);var pt=/-([a-z])/g;function mt(e,t){return t.toUpperCase()}function ht(e){return e.replace(pt,mt)}function gt(e){return e.nodeType===1||e.nodeType===9||!+e.nodeType}function _t(){this.expando=S.expando+_t.uid++}_t.uid=1,_t.prototype={cache:function(e){var t=e[this.expando];return t||(t=Object.create(null),gt(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var r,i=this.cache(e);if(typeof t==`string`)i[ht(t)]=n;else for(r in t)i[ht(r)]=t[r];return n},get:function(e,t){return t===void 0?this.cache(e):e[this.expando]&&e[this.expando][ht(t)]},access:function(e,t,n){return t===void 0||t&&typeof t==`string`&&n===void 0?this.get(e,t):(this.set(e,t,n),n===void 0?t:n)},remove:function(e,t){var n,r=e[this.expando];if(r!==void 0){if(t!==void 0)for(Array.isArray(t)?t=t.map(ht):(t=ht(t),t=(t in r)?[t]:t.match(R)||[]),n=t.length;n--;)delete r[t[n]];(t===void 0||S.isEmptyObject(r))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return t!==void 0&&!S.isEmptyObject(t)}};var G=new _t,K=new _t,vt=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,yt=/[A-Z]/g;function bt(e){return e===`true`?!0:e===`false`?!1:e===`null`?null:e===+e+``?+e:vt.test(e)?JSON.parse(e):e}function xt(e,t,n){var r;if(n===void 0&&e.nodeType===1)if(r=`data-`+t.replace(yt,`-$&`).toLowerCase(),n=e.getAttribute(r),typeof n==`string`){try{n=bt(n)}catch{}K.set(e,t,n)}else n=void 0;return n}S.extend({hasData:function(e){return K.hasData(e)||G.hasData(e)},data:function(e,t,n){return K.access(e,t,n)},removeData:function(e,t){K.remove(e,t)},_data:function(e,t,n){return G.access(e,t,n)},_removeData:function(e,t){G.remove(e,t)}}),S.fn.extend({data:function(e,t){var n,r,i,a=this[0],o=a&&a.attributes;if(e===void 0){if(this.length&&(i=K.get(a),a.nodeType===1&&!G.get(a,`hasDataAttrs`))){for(n=o.length;n--;)o[n]&&(r=o[n].name,r.indexOf(`data-`)===0&&(r=ht(r.slice(5)),xt(a,r,i[r])));G.set(a,`hasDataAttrs`,!0)}return i}return typeof e==`object`?this.each(function(){K.set(this,e)}):L(this,function(t){var n;if(a&&t===void 0)return n=K.get(a,e),n!==void 0||(n=xt(a,e),n!==void 0)?n:void 0;this.each(function(){K.set(this,e,t)})},null,t,arguments.length>1,null,!0)},removeData:function(e){return this.each(function(){K.remove(this,e)})}}),S.extend({queue:function(e,t,n){var r;if(e)return t=(t||`fx`)+`queue`,r=G.get(e,t),n&&(!r||Array.isArray(n)?r=G.set(e,t,S.makeArray(n)):r.push(n)),r||[]},dequeue:function(e,t){t||=`fx`;var n=S.queue(e,t),r=n.length,i=n.shift(),a=S._queueHooks(e,t);i===`inprogress`&&(i=n.shift(),r--),i&&(t===`fx`&&n.unshift(`inprogress`),delete a.stop,i.call(e,function(){S.dequeue(e,t)},a)),!r&&a&&a.empty.fire()},_queueHooks:function(e,t){var n=t+`queueHooks`;return G.get(e,n)||G.set(e,n,{empty:S.Callbacks(`once memory`).add(function(){G.remove(e,[t+`queue`,n])})})}}),S.fn.extend({queue:function(e,t){var n=2;return typeof e!=`string`&&(t=e,e=`fx`,n--),arguments.length<n?S.queue(this[0],e):t===void 0?this:this.each(function(){var n=S.queue(this,e,t);S._queueHooks(this,e),e===`fx`&&n[0]!==`inprogress`&&S.dequeue(this,e)})},dequeue:function(e){return this.each(function(){S.dequeue(this,e)})},clearQueue:function(e){return this.queue(e||`fx`,[])},promise:function(e,t){var n,r=1,i=S.Deferred(),a=this,o=this.length,s=function(){--r||i.resolveWith(a,[a])};for(typeof e!=`string`&&(t=e,e=void 0),e||=`fx`;o--;)n=G.get(a[o],e+`queueHooks`),n&&n.empty&&(r++,n.empty.add(s));return s(),i.promise(t)}});var St=`[+-]?(?:\\d*\\.|)\\d+(?:[eE][+-]?\\d+|)`,Ct=RegExp(`^(?:([+-])=|)(`+St+`)([a-z%]*)$`,`i`),q=[`Top`,`Right`,`Bottom`,`Left`];function wt(e,t){return e=t||e,e.style.display===`none`||e.style.display===``&&S.css(e,`display`)===`none`}var Tt=/^[a-z]/,Et=/^(?:Border(?:Top|Right|Bottom|Left)?(?:Width|)|(?:Margin|Padding)?(?:Top|Right|Bottom|Left)?|(?:Min|Max)?(?:Width|Height))$/;function Dt(e){return Tt.test(e)&&Et.test(e[0].toUpperCase()+e.slice(1))}function Ot(e,t,n,r){var i,a,o=20,s=r?function(){return r.cur()}:function(){return S.css(e,t,``)},c=s(),l=n&&n[3]||(Dt(t)?`px`:``),u=e.nodeType&&(!Dt(t)||l!==`px`&&+c)&&Ct.exec(S.css(e,t));if(u&&u[3]!==l){for(c/=2,l||=u[3],u=+c||1;o--;)S.style(e,t,u+l),(1-a)*(1-(a=s()/c||.5))<=0&&(o=0),u/=a;u*=2,S.style(e,t,u+l),n||=[]}return n&&(u=+u||+c||0,i=n[1]?u+(n[1]+1)*n[2]:+n[2],r&&(r.unit=l,r.start=u,r.end=i)),i}var kt=/^-ms-/;function At(e){return ht(e.replace(kt,`ms-`))}var jt={};function Mt(e){var t,n=e.ownerDocument,r=e.nodeName,i=jt[r];return i||(t=n.body.appendChild(n.createElement(r)),i=S.css(t,`display`),t.parentNode.removeChild(t),i===`none`&&(i=`block`),jt[r]=i,i)}function Nt(e,t){for(var n,r,i=[],a=0,o=e.length;a<o;a++)r=e[a],r.style&&(n=r.style.display,t?(n===`none`&&(i[a]=G.get(r,`display`)||null,i[a]||(r.style.display=``)),r.style.display===``&&wt(r)&&(i[a]=Mt(r))):n!==`none`&&(i[a]=`none`,G.set(r,`display`,n)));for(a=0;a<o;a++)i[a]!=null&&(e[a].style.display=i[a]);return e}S.fn.extend({show:function(){return Nt(this,!0)},hide:function(){return Nt(this)},toggle:function(e){return typeof e==`boolean`?e?this.show():this.hide():this.each(function(){wt(this)?S(this).show():S(this).hide()})}});var Pt=function(e){return S.contains(e.ownerDocument,e)||e.getRootNode(Ft)===e.ownerDocument},Ft={composed:!0};A.getRootNode||(Pt=function(e){return S.contains(e.ownerDocument,e)});var It=/<([a-z][^\/\0>\x20\t\r\n\f]*)/i,J={thead:[`table`],col:[`colgroup`,`table`],tr:[`tbody`,`table`],td:[`tr`,`tbody`,`table`]};J.tbody=J.tfoot=J.colgroup=J.caption=J.thead,J.th=J.td;function Y(e,t){var r=e.getElementsByTagName===void 0?e.querySelectorAll===void 0?[]:e.querySelectorAll(t||`*`):n.slice.call(e.getElementsByTagName(t||`*`));return t===void 0||t&&C(e,t)?S.merge([e],r):r}var Lt=/^$|^module$|\/(?:java|ecma)script/i;function Rt(e,t){for(var n=0,r=e.length;n<r;n++)G.set(e[n],`globalEval`,!t||G.get(t[n],`globalEval`))}var zt=/<|&#?\w+;/;function Bt(e,t,r,i,a){for(var o,s,c,l,u,d,f=t.createDocumentFragment(),p=[],h=0,_=e.length;h<_;h++)if(o=e[h],o||o===0)if(m(o)===`object`&&(o.nodeType||g(o)))S.merge(p,o.nodeType?[o]:o);else if(!zt.test(o))p.push(t.createTextNode(o));else{for(s||=f.appendChild(t.createElement(`div`)),c=(It.exec(o)||[``,``])[1].toLowerCase(),l=J[c]||n,d=l.length;--d>-1;)s=s.appendChild(t.createElement(l[d]));s.innerHTML=S.htmlPrefilter(o),S.merge(p,s.childNodes),s=f.firstChild,s.textContent=``}for(f.textContent=``,h=0;o=p[h++];){if(i&&S.inArray(o,i)>-1){a&&a.push(o);continue}if(u=Pt(o),s=Y(f.appendChild(o),`script`),u&&Rt(s),r)for(d=0;o=s[d++];)Lt.test(o.type||``)&&r.push(o)}return f}function Vt(e){return e.type=(e.getAttribute(`type`)!==null)+`/`+e.type,e}function Ht(e){return(e.type||``).slice(0,5)===`true/`?e.type=e.type.slice(5):e.removeAttribute(`type`),e}function Ut(e,t,n,r){t=a(t);var i,o,s,c,l,u,d=0,f=e.length,p=f-1,m=t[0];if(typeof m==`function`)return e.each(function(i){var a=e.eq(i);t[0]=m.call(this,i,a.html()),Ut(a,t,n,r)});if(f&&(i=Bt(t,e[0].ownerDocument,!1,e,r),o=i.firstChild,i.childNodes.length===1&&(i=o),o||r)){for(s=S.map(Y(i,`script`),Vt),c=s.length;d<f;d++)l=i,d!==p&&(l=S.clone(l,!0,!0),c&&S.merge(s,Y(l,`script`))),n.call(e[d],l,d);if(c)for(u=s[s.length-1].ownerDocument,S.map(s,Ht),d=0;d<c;d++)l=s[d],Lt.test(l.type||``)&&!G.get(l,`globalEval`)&&S.contains(u,l)&&(l.src&&(l.type||``).toLowerCase()!==`module`?S._evalUrl&&!l.noModule&&S._evalUrl(l.src,{nonce:l.nonce,crossOrigin:l.crossOrigin},u):y(l.textContent,l,u))}return e}var Wt=/^(?:checkbox|radio)$/i,Gt=/^([^.]*)(?:\.(.+)|)/;function Kt(){return!0}function qt(){return!1}function Jt(e,t,n,r,i,a){var o,s;if(typeof t==`object`){for(s in typeof n!=`string`&&(r||=n,n=void 0),t)Jt(e,s,n,r,t[s],a);return e}if(r==null&&i==null?(i=n,r=n=void 0):i??(typeof n==`string`?(i=r,r=void 0):(i=r,r=n,n=void 0)),i===!1)i=qt;else if(!i)return e;return a===1&&(o=i,i=function(e){return S().off(e),o.apply(this,arguments)},i.guid=o.guid||=S.guid++),e.each(function(){S.event.add(this,t,i,r,n)})}S.event={add:function(e,t,n,r,i){var a,o,s,c,l,u,d,f,p,m,h,g=G.get(e);if(gt(e))for(n.handler&&(a=n,n=a.handler,i=a.selector),i&&S.find.matchesSelector(A,i),n.guid||=S.guid++,(c=g.events)||(c=g.events=Object.create(null)),(o=g.handle)||(o=g.handle=function(t){return S!==void 0&&S.event.triggered!==t.type?S.event.dispatch.apply(e,arguments):void 0}),t=(t||``).match(R)||[``],l=t.length;l--;)s=Gt.exec(t[l])||[],p=h=s[1],m=(s[2]||``).split(`.`).sort(),p&&(d=S.event.special[p]||{},p=(i?d.delegateType:d.bindType)||p,d=S.event.special[p]||{},u=S.extend({type:p,origType:h,data:r,handler:n,guid:n.guid,selector:i,needsContext:i&&S.expr.match.needsContext.test(i),namespace:m.join(`.`)},a),(f=c[p])||(f=c[p]=[],f.delegateCount=0,(!d.setup||d.setup.call(e,r,m,o)===!1)&&e.addEventListener&&e.addEventListener(p,o)),d.add&&(d.add.call(e,u),u.handler.guid||(u.handler.guid=n.guid)),i?f.splice(f.delegateCount++,0,u):f.push(u))},remove:function(e,t,n,r,i){var a,o,s,c,l,u,d,f,p,m,h,g=G.hasData(e)&&G.get(e);if(!(!g||!(c=g.events))){for(t=(t||``).match(R)||[``],l=t.length;l--;){if(s=Gt.exec(t[l])||[],p=h=s[1],m=(s[2]||``).split(`.`).sort(),!p){for(p in c)S.event.remove(e,p+t[l],n,r,!0);continue}for(d=S.event.special[p]||{},p=(r?d.delegateType:d.bindType)||p,f=c[p]||[],s=s[2]&&RegExp(`(^|\\.)`+m.join(`\\.(?:.*\\.|)`)+`(\\.|$)`),o=a=f.length;a--;)u=f[a],(i||h===u.origType)&&(!n||n.guid===u.guid)&&(!s||s.test(u.namespace))&&(!r||r===u.selector||r===`**`&&u.selector)&&(f.splice(a,1),u.selector&&f.delegateCount--,d.remove&&d.remove.call(e,u));o&&!f.length&&((!d.teardown||d.teardown.call(e,m,g.handle)===!1)&&S.removeEvent(e,p,g.handle),delete c[p])}S.isEmptyObject(c)&&G.remove(e,`handle events`)}},dispatch:function(e){var t,n,r,i,a,o,s=Array(arguments.length),c=S.event.fix(e),l=(G.get(this,`events`)||Object.create(null))[c.type]||[],u=S.event.special[c.type]||{};for(s[0]=c,t=1;t<arguments.length;t++)s[t]=arguments[t];if(c.delegateTarget=this,!(u.preDispatch&&u.preDispatch.call(this,c)===!1)){for(o=S.event.handlers.call(this,c,l),t=0;(i=o[t++])&&!c.isPropagationStopped();)for(c.currentTarget=i.elem,n=0;(a=i.handlers[n++])&&!c.isImmediatePropagationStopped();)(!c.rnamespace||a.namespace===!1||c.rnamespace.test(a.namespace))&&(c.handleObj=a,c.data=a.data,r=((S.event.special[a.origType]||{}).handle||a.handler).apply(i.elem,s),r!==void 0&&(c.result=r)===!1&&(c.preventDefault(),c.stopPropagation()));return u.postDispatch&&u.postDispatch.call(this,c),c.result}},handlers:function(e,t){var n,r,i,a,o,s=[],c=t.delegateCount,l=e.target;if(c&&!(e.type===`click`&&e.button>=1)){for(;l!==this;l=l.parentNode||this)if(l.nodeType===1&&!(e.type===`click`&&l.disabled===!0)){for(a=[],o={},n=0;n<c;n++)r=t[n],i=r.selector+` `,o[i]===void 0&&(o[i]=r.needsContext?S(i,this).index(l)>-1:S.find(i,this,null,[l]).length),o[i]&&a.push(r);a.length&&s.push({elem:l,handlers:a})}}return l=this,c<t.length&&s.push({elem:l,handlers:t.slice(c)}),s},addProp:function(e,t){Object.defineProperty(S.Event.prototype,e,{enumerable:!0,configurable:!0,get:typeof t==`function`?function(){if(this.originalEvent)return t(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[e]},set:function(t){Object.defineProperty(this,e,{enumerable:!0,configurable:!0,writable:!0,value:t})}})},fix:function(e){return e[S.expando]?e:new S.Event(e)},special:S.extend(Object.create(null),{load:{noBubble:!0},click:{setup:function(e){var t=this||e;return Wt.test(t.type)&&t.click&&C(t,`input`)&&Yt(t,`click`,!0),!1},trigger:function(e){var t=this||e;return Wt.test(t.type)&&t.click&&C(t,`input`)&&Yt(t,`click`),!0},_default:function(e){var t=e.target;return Wt.test(t.type)&&t.click&&C(t,`input`)&&G.get(t,`click`)||C(t,`a`)}},beforeunload:{postDispatch:function(e){e.result!==void 0&&e.preventDefault()}}})};function Yt(e,t,n){if(!n){G.get(e,t)===void 0&&S.event.add(e,t,Kt);return}G.set(e,t,!1),S.event.add(e,t,{namespace:!1,handler:function(e){var n,r=G.get(this,t);if(e.isTrigger&1&&this[t]){if(r.length)(S.event.special[t]||{}).delegateType&&e.stopPropagation();else if(r=i.call(arguments),G.set(this,t,r),this[t](),n=G.get(this,t),G.set(this,t,!1),r!==n)return e.stopImmediatePropagation(),e.preventDefault(),n&&n.value}else r.length&&(G.set(this,t,{value:S.event.trigger(r[0],r.slice(1),this)}),e.stopPropagation(),e.isImmediatePropagationStopped=Kt)}})}S.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n)},S.Event=function(e,t){if(!(this instanceof S.Event))return new S.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented?Kt:qt,this.target=e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&S.extend(this,t),this.timeStamp=e&&e.timeStamp||Date.now(),this[S.expando]=!0},S.Event.prototype={constructor:S.Event,isDefaultPrevented:qt,isPropagationStopped:qt,isImmediatePropagationStopped:qt,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=Kt,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=Kt,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=Kt,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},S.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,char:!0,code:!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:!0},S.event.addProp),S.each({focus:`focusin`,blur:`focusout`},function(e,t){function n(e){var t=S.event.fix(e);t.type=e.type===`focusin`?`focus`:`blur`,t.isSimulated=!0,t.target===t.currentTarget&&G.get(this,`handle`)(t)}S.event.special[e]={setup:function(){if(Yt(this,e,!0),E)this.addEventListener(t,n);else return!1},trigger:function(){return Yt(this,e),!0},teardown:function(){if(E)this.removeEventListener(t,n);else return!1},_default:function(t){return G.get(t.target,e)},delegateType:t}}),S.each({mouseenter:`mouseover`,mouseleave:`mouseout`,pointerenter:`pointerover`,pointerleave:`pointerout`},function(e,t){S.event.special[e]={delegateType:t,bindType:t,handle:function(e){var n,r=this,i=e.relatedTarget,a=e.handleObj;return(!i||i!==r&&!S.contains(r,i))&&(e.type=a.origType,n=a.handler.apply(this,arguments),e.type=t),n}}}),S.fn.extend({on:function(e,t,n,r){return Jt(this,e,t,n,r)},one:function(e,t,n,r){return Jt(this,e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return r=e.handleObj,S(e.delegateTarget).off(r.namespace?r.origType+`.`+r.namespace:r.origType,r.selector,r.handler),this;if(typeof e==`object`){for(i in e)this.off(i,t,e[i]);return this}return(t===!1||typeof t==`function`)&&(n=t,t=void 0),n===!1&&(n=qt),this.each(function(){S.event.remove(this,e,n,t)})}});var Xt=/<script|<style|<link/i;function Zt(e,t){return C(e,`table`)&&C(t.nodeType===11?t.firstChild:t,`tr`)&&S(e).children(`tbody`)[0]||e}function Qt(e,t){var n,r,i,a=G.get(e,`events`);if(t.nodeType===1){if(a)for(n in G.remove(t,`handle events`),a)for(r=0,i=a[n].length;r<i;r++)S.event.add(t,n,a[n][r]);K.hasData(e)&&K.set(t,S.extend({},K.get(e)))}}function $t(e,t,n){for(var r,i=t?S.filter(t,e):e,a=0;(r=i[a])!=null;a++)!n&&r.nodeType===1&&S.cleanData(Y(r)),r.parentNode&&(n&&Pt(r)&&Rt(Y(r,`script`)),r.parentNode.removeChild(r));return e}S.extend({htmlPrefilter:function(e){return e},clone:function(e,t,n){var r,i,a,o,s=e.cloneNode(!0),c=Pt(e);if(E&&(e.nodeType===1||e.nodeType===11)&&!S.isXMLDoc(e))for(o=Y(s),a=Y(e),r=0,i=a.length;r<i;r++)C(o[r],`textarea`)&&(o[r].defaultValue=a[r].defaultValue);if(t)if(n)for(a||=Y(e),o||=Y(s),r=0,i=a.length;r<i;r++)Qt(a[r],o[r]);else Qt(e,s);return o=Y(s,`script`),o.length>0&&Rt(o,!c&&Y(e,`script`)),s},cleanData:function(e){for(var t,n,r,i=S.event.special,a=0;(n=e[a])!==void 0;a++)if(gt(n)){if(t=n[G.expando]){if(t.events)for(r in t.events)i[r]?S.event.remove(n,r):S.removeEvent(n,r,t.handle);n[G.expando]=void 0}n[K.expando]&&(n[K.expando]=void 0)}}}),S.fn.extend({detach:function(e){return $t(this,e,!0)},remove:function(e){return $t(this,e)},text:function(e){return L(this,function(e){return e===void 0?S.text(this):this.empty().each(function(){(this.nodeType===1||this.nodeType===11||this.nodeType===9)&&(this.textContent=e)})},null,e,arguments.length)},append:function(){return Ut(this,arguments,function(e){(this.nodeType===1||this.nodeType===11||this.nodeType===9)&&Zt(this,e).appendChild(e)})},prepend:function(){return Ut(this,arguments,function(e){if(this.nodeType===1||this.nodeType===11||this.nodeType===9){var t=Zt(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return Ut(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return Ut(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},empty:function(){for(var e,t=0;(e=this[t])!=null;t++)e.nodeType===1&&(S.cleanData(Y(e,!1)),e.textContent=``);return this},clone:function(e,t){return e??=!1,t??=e,this.map(function(){return S.clone(this,e,t)})},html:function(e){return L(this,function(e){var t=this[0]||{},n=0,r=this.length;if(e===void 0&&t.nodeType===1)return t.innerHTML;if(typeof e==`string`&&!Xt.test(e)&&!J[(It.exec(e)||[``,``])[1].toLowerCase()]){e=S.htmlPrefilter(e);try{for(;n<r;n++)t=this[n]||{},t.nodeType===1&&(S.cleanData(Y(t,!1)),t.innerHTML=e);t=0}catch{}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var e=[];return Ut(this,arguments,function(t){var n=this.parentNode;S.inArray(this,e)<0&&(S.cleanData(Y(this)),n&&n.replaceChild(t,this))},e)}}),S.each({appendTo:`append`,prependTo:`prepend`,insertBefore:`before`,insertAfter:`after`,replaceAll:`replaceWith`},function(e,t){S.fn[e]=function(e){for(var n,r=[],i=S(e),a=i.length-1,s=0;s<=a;s++)n=s===a?this:this.clone(!0),S(i[s])[t](n),o.apply(r,n);return this.pushStack(r)}});var en=RegExp(`^(`+St+`)(?!px)[a-z%]+$`,`i`),tn=/^--/;function nn(t){var n=t.ownerDocument.defaultView;return n||=e,n.getComputedStyle(t)}function rn(e,t,n){var r,i,a={};for(i in t)a[i]=e.style[i],e.style[i]=t[i];for(i in r=n.call(e),t)e.style[i]=a[i];return r}function an(e,t,n){var r,i=tn.test(t);return n||=nn(e),n&&(r=n.getPropertyValue(t)||n[t],i&&r&&(r=r.replace(D,`$1`)||void 0),r===``&&!Pt(e)&&(r=S.style(e,t))),r===void 0?r:r+``}var on=[`Webkit`,`Moz`,`ms`],sn=_.createElement(`div`).style;function cn(e){for(var t=e[0].toUpperCase()+e.slice(1),n=on.length;n--;)if(e=on[n]+t,e in sn)return e}function ln(e){return e in sn?e:cn(e)||e}var un,dn,X=_.createElement(`table`);function fn(){if(!(!X||!X.style)){var t,n=_.createElement(`col`),r=_.createElement(`tr`),i=_.createElement(`td`);if(X.style.cssText=`position:absolute;left:-11111px;border-collapse:separate;border-spacing:0`,r.style.cssText=`box-sizing:content-box;border:1px solid;height:1px`,i.style.cssText=`height:9px;width:9px;padding:0`,n.span=2,A.appendChild(X).appendChild(n).parentNode.appendChild(r).appendChild(i).parentNode.appendChild(i.cloneNode(!0)),X.offsetWidth===0){A.removeChild(X);return}t=e.getComputedStyle(r),dn=E||Math.round(parseFloat(e.getComputedStyle(n).width))===18,un=Math.round(parseFloat(t.height)+parseFloat(t.borderTopWidth)+parseFloat(t.borderBottomWidth))===r.offsetHeight,A.removeChild(X),X=null}}S.extend(p,{reliableTrDimensions:function(){return fn(),un},reliableColDimensions:function(){return fn(),dn}});var pn={position:`absolute`,visibility:`hidden`,display:`block`},mn={letterSpacing:`0`,fontWeight:`400`};function hn(e,t,n){var r=Ct.exec(t);return r?Math.max(0,r[2]-(n||0))+(r[3]||`px`):t}function gn(e,t,n,r,i,a){var o=+(t===`width`),s=0,c=0,l=0;if(n===(r?`border`:`content`))return 0;for(;o<4;o+=2)n===`margin`&&(l+=S.css(e,n+q[o],!0,i)),r?(n===`content`&&(c-=S.css(e,`padding`+q[o],!0,i)),n!==`margin`&&(c-=S.css(e,`border`+q[o]+`Width`,!0,i))):(c+=S.css(e,`padding`+q[o],!0,i),n===`padding`?s+=S.css(e,`border`+q[o]+`Width`,!0,i):c+=S.css(e,`border`+q[o]+`Width`,!0,i));return!r&&a>=0&&(c+=Math.max(0,Math.ceil(e[`offset`+t[0].toUpperCase()+t.slice(1)]-a-c-s-.5))||0),c+l}function _n(e,t,n){var r=nn(e),i=(E||n)&&S.css(e,`boxSizing`,!1,r)===`border-box`,a=i,o=an(e,t,r),s=`offset`+t[0].toUpperCase()+t.slice(1);if(en.test(o)){if(!n)return o;o=`auto`}return(o===`auto`||E&&i||!p.reliableColDimensions()&&C(e,`col`)||!p.reliableTrDimensions()&&C(e,`tr`))&&e.getClientRects().length&&(i=S.css(e,`boxSizing`,!1,r)===`border-box`,a=s in e,a&&(o=e[s])),o=parseFloat(o)||0,o+gn(e,t,n||(i?`border`:`content`),a,r,o)+`px`}S.extend({cssHooks:{},style:function(e,t,n,r){if(!(!e||e.nodeType===3||e.nodeType===8||!e.style)){var i,a,o,s=At(t),c=tn.test(t),l=e.style;if(c||(t=ln(s)),o=S.cssHooks[t]||S.cssHooks[s],n!==void 0){if(a=typeof n,a===`string`&&(i=Ct.exec(n))&&i[1]&&(n=Ot(e,t,i),a=`number`),n==null||n!==n)return;a===`number`&&(n+=i&&i[3]||(Dt(s)?`px`:``)),E&&n===``&&t.indexOf(`background`)===0&&(l[t]=`inherit`),(!o||!(`set`in o)||(n=o.set(e,n,r))!==void 0)&&(c?l.setProperty(t,n):l[t]=n)}else return o&&`get`in o&&(i=o.get(e,!1,r))!==void 0?i:l[t]}},css:function(e,t,n,r){var i,a,o,s=At(t);return tn.test(t)||(t=ln(s)),o=S.cssHooks[t]||S.cssHooks[s],o&&`get`in o&&(i=o.get(e,!0,n)),i===void 0&&(i=an(e,t,r)),i===`normal`&&t in mn&&(i=mn[t]),n===``||n?(a=parseFloat(i),n===!0||isFinite(a)?a||0:i):i}}),S.each([`height`,`width`],function(e,t){S.cssHooks[t]={get:function(e,n,r){if(n)return S.css(e,`display`)===`none`?rn(e,pn,function(){return _n(e,t,r)}):_n(e,t,r)},set:function(e,n,r){var i,a=nn(e),o=r&&S.css(e,`boxSizing`,!1,a)===`border-box`,s=r?gn(e,t,r,o,a):0;return s&&(i=Ct.exec(n))&&(i[3]||`px`)!==`px`&&(e.style[t]=n,n=S.css(e,t)),hn(e,n,s)}}}),S.each({margin:``,padding:``,border:`Width`},function(e,t){S.cssHooks[e+t]={expand:function(n){for(var r=0,i={},a=typeof n==`string`?n.split(` `):[n];r<4;r++)i[e+q[r]+t]=a[r]||a[r-2]||a[0];return i}},e!==`margin`&&(S.cssHooks[e+t].set=hn)}),S.fn.extend({css:function(e,t){return L(this,function(e,t,n){var r,i,a={},o=0;if(Array.isArray(t)){for(r=nn(e),i=t.length;o<i;o++)a[t[o]]=S.css(e,t[o],!1,r);return a}return n===void 0?S.css(e,t):S.style(e,t,n)},e,t,arguments.length>1)}});function Z(e,t,n,r,i){return new Z.prototype.init(e,t,n,r,i)}S.Tween=Z,Z.prototype={constructor:Z,init:function(e,t,n,r,i,a){this.elem=e,this.prop=n,this.easing=i||S.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=a||(Dt(n)?`px`:``)},cur:function(){var e=Z.propHooks[this.prop];return e&&e.get?e.get(this):Z.propHooks._default.get(this)},run:function(e){var t,n=Z.propHooks[this.prop];return this.options.duration?this.pos=t=S.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):Z.propHooks._default.set(this),this}},Z.prototype.init.prototype=Z.prototype,Z.propHooks={_default:{get:function(e){var t;return e.elem.nodeType!==1||e.elem[e.prop]!=null&&e.elem.style[e.prop]==null?e.elem[e.prop]:(t=S.css(e.elem,e.prop,``),!t||t===`auto`?0:t)},set:function(e){S.fx.step[e.prop]?S.fx.step[e.prop](e):e.elem.nodeType===1&&(S.cssHooks[e.prop]||e.elem.style[ln(e.prop)]!=null)?S.style(e.elem,e.prop,e.now+e.unit):e.elem[e.prop]=e.now}}},S.easing={linear:function(e){return e},swing:function(e){return .5-Math.cos(e*Math.PI)/2},_default:`swing`},S.fx=Z.prototype.init,S.fx.step={};var vn,yn,bn=/^(?:toggle|show|hide)$/,xn=/queueHooks$/;function Sn(){yn&&(_.hidden===!1&&e.requestAnimationFrame?e.requestAnimationFrame(Sn):e.setTimeout(Sn,13),S.fx.tick())}function Cn(){return e.setTimeout(function(){vn=void 0}),vn=Date.now()}function wn(e,t){var n,r=0,i={height:e};for(t=+!!t;r<4;r+=2-t)n=q[r],i[`margin`+n]=i[`padding`+n]=e;return t&&(i.opacity=i.width=e),i}function Tn(e,t,n){for(var r,i=(Q.tweeners[t]||[]).concat(Q.tweeners[`*`]),a=0,o=i.length;a<o;a++)if(r=i[a].call(n,t,e))return r}function En(e,t,n){var r,i,a,o,s,c,l,u,d=`width`in t||`height`in t,f=this,p={},m=e.style,h=e.nodeType&&wt(e),g=G.get(e,`fxshow`);for(r in n.queue||(o=S._queueHooks(e,`fx`),o.unqueued??(o.unqueued=0,s=o.empty.fire,o.empty.fire=function(){o.unqueued||s()}),o.unqueued++,f.always(function(){f.always(function(){o.unqueued--,S.queue(e,`fx`).length||o.empty.fire()})})),t)if(i=t[r],bn.test(i)){if(delete t[r],a||=i===`toggle`,i===(h?`hide`:`show`))if(i===`show`&&g&&g[r]!==void 0)h=!0;else continue;p[r]=g&&g[r]||S.style(e,r)}if(c=!S.isEmptyObject(t),!(!c&&S.isEmptyObject(p)))for(r in d&&e.nodeType===1&&(n.overflow=[m.overflow,m.overflowX,m.overflowY],l=g&&g.display,l??=G.get(e,`display`),u=S.css(e,`display`),u===`none`&&(l?u=l:(Nt([e],!0),l=e.style.display||l,u=S.css(e,`display`),Nt([e]))),(u===`inline`||u===`inline-block`&&l!=null)&&S.css(e,`float`)===`none`&&(c||(f.done(function(){m.display=l}),l??=(u=m.display,u===`none`?``:u)),m.display=`inline-block`)),n.overflow&&(m.overflow=`hidden`,f.always(function(){m.overflow=n.overflow[0],m.overflowX=n.overflow[1],m.overflowY=n.overflow[2]})),c=!1,p)c||(g?`hidden`in g&&(h=g.hidden):g=G.set(e,`fxshow`,{display:l}),a&&(g.hidden=!h),h&&Nt([e],!0),f.done(function(){for(r in h||Nt([e]),G.remove(e,`fxshow`),p)S.style(e,r,p[r])})),c=Tn(h?g[r]:0,r,f),r in g||(g[r]=c.start,h&&(c.end=c.start,c.start=0))}function Dn(e,t){var n,r,i,a,o;for(n in e)if(r=At(n),i=t[r],a=e[n],Array.isArray(a)&&(i=a[1],a=e[n]=a[0]),n!==r&&(e[r]=a,delete e[n]),o=S.cssHooks[r],o&&`expand`in o)for(n in a=o.expand(a),delete e[r],a)n in e||(e[n]=a[n],t[n]=i);else t[r]=i}function Q(e,t,n){var r,i,a=0,o=Q.prefilters.length,s=S.Deferred().always(function(){delete c.elem}),c=function(){if(i)return!1;for(var t=vn||Cn(),n=Math.max(0,l.startTime+l.duration-t),r=1-(n/l.duration||0),a=0,o=l.tweens.length;a<o;a++)l.tweens[a].run(r);return s.notifyWith(e,[l,r,n]),r<1&&o?n:(o||s.notifyWith(e,[l,1,0]),s.resolveWith(e,[l]),!1)},l=s.promise({elem:e,props:S.extend({},t),opts:S.extend(!0,{specialEasing:{},easing:S.easing._default},n),originalProperties:t,originalOptions:n,startTime:vn||Cn(),duration:n.duration,tweens:[],createTween:function(t,n){var r=S.Tween(e,l.opts,t,n,l.opts.specialEasing[t]||l.opts.easing);return l.tweens.push(r),r},stop:function(t){var n=0,r=t?l.tweens.length:0;if(i)return this;for(i=!0;n<r;n++)l.tweens[n].run(1);return t?(s.notifyWith(e,[l,1,0]),s.resolveWith(e,[l,t])):s.rejectWith(e,[l,t]),this}}),u=l.props;for(Dn(u,l.opts.specialEasing);a<o;a++)if(r=Q.prefilters[a].call(l,e,u,l.opts),r)return typeof r.stop==`function`&&(S._queueHooks(l.elem,l.opts.queue).stop=r.stop.bind(r)),r;return S.map(u,Tn,l),typeof l.opts.start==`function`&&l.opts.start.call(e,l),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always),S.fx.timer(S.extend(c,{elem:e,anim:l,queue:l.opts.queue})),l}S.Animation=S.extend(Q,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return Ot(n.elem,e,Ct.exec(t),n),n}]},tweener:function(e,t){typeof e==`function`?(t=e,e=[`*`]):e=e.match(R);for(var n,r=0,i=e.length;r<i;r++)n=e[r],Q.tweeners[n]=Q.tweeners[n]||[],Q.tweeners[n].unshift(t)},prefilters:[En],prefilter:function(e,t){t?Q.prefilters.unshift(e):Q.prefilters.push(e)}}),S.speed=function(e,t,n){var r=e&&typeof e==`object`?S.extend({},e):{complete:n||t||typeof e==`function`&&e,duration:e,easing:n&&t||t&&typeof t!=`function`&&t};return S.fx.off?r.duration=0:typeof r.duration!=`number`&&(r.duration in S.fx.speeds?r.duration=S.fx.speeds[r.duration]:r.duration=S.fx.speeds._default),(r.queue==null||r.queue===!0)&&(r.queue=`fx`),r.old=r.complete,r.complete=function(){typeof r.old==`function`&&r.old.call(this),r.queue&&S.dequeue(this,r.queue)},r},S.fn.extend({fadeTo:function(e,t,n,r){return this.filter(wt).css(`opacity`,0).show().end().animate({opacity:t},e,n,r)},animate:function(e,t,n,r){var i=S.isEmptyObject(e),a=S.speed(t,n,r),o=function(){var t=Q(this,S.extend({},e),a);(i||G.get(this,`finish`))&&t.stop(!0)};return o.finish=o,i||a.queue===!1?this.each(o):this.queue(a.queue,o)},stop:function(e,t,n){var r=function(e){var t=e.stop;delete e.stop,t(n)};return typeof e!=`string`&&(n=t,t=e,e=void 0),t&&this.queue(e||`fx`,[]),this.each(function(){var t=!0,i=e!=null&&e+`queueHooks`,a=S.timers,o=G.get(this);if(i)o[i]&&o[i].stop&&r(o[i]);else for(i in o)o[i]&&o[i].stop&&xn.test(i)&&r(o[i]);for(i=a.length;i--;)a[i].elem===this&&(e==null||a[i].queue===e)&&(a[i].anim.stop(n),t=!1,a.splice(i,1));(t||!n)&&S.dequeue(this,e)})},finish:function(e){return e!==!1&&(e||=`fx`),this.each(function(){var t,n=G.get(this),r=n[e+`queue`],i=n[e+`queueHooks`],a=S.timers,o=r?r.length:0;for(n.finish=!0,S.queue(this,e,[]),i&&i.stop&&i.stop.call(this,!0),t=a.length;t--;)a[t].elem===this&&a[t].queue===e&&(a[t].anim.stop(!0),a.splice(t,1));for(t=0;t<o;t++)r[t]&&r[t].finish&&r[t].finish.call(this);delete n.finish})}}),S.each([`toggle`,`show`,`hide`],function(e,t){var n=S.fn[t];S.fn[t]=function(e,r,i){return e==null||typeof e==`boolean`?n.apply(this,arguments):this.animate(wn(t,!0),e,r,i)}}),S.each({slideDown:wn(`show`),slideUp:wn(`hide`),slideToggle:wn(`toggle`),fadeIn:{opacity:`show`},fadeOut:{opacity:`hide`},fadeToggle:{opacity:`toggle`}},function(e,t){S.fn[e]=function(e,n,r){return this.animate(t,e,n,r)}}),S.timers=[],S.fx.tick=function(){var e,t=0,n=S.timers;for(vn=Date.now();t<n.length;t++)e=n[t],!e()&&n[t]===e&&n.splice(t--,1);n.length||S.fx.stop(),vn=void 0},S.fx.timer=function(e){S.timers.push(e),S.fx.start()},S.fx.start=function(){yn||(yn=!0,Sn())},S.fx.stop=function(){yn=null},S.fx.speeds={slow:600,fast:200,_default:400},S.fn.delay=function(t,n){return t=S.fx&&S.fx.speeds[t]||t,n||=`fx`,this.queue(n,function(n,r){var i=e.setTimeout(n,t);r.stop=function(){e.clearTimeout(i)}})};var On=/^(?:input|select|textarea|button)$/i,kn=/^(?:a|area)$/i;S.fn.extend({prop:function(e,t){return L(this,S.prop,e,t,arguments.length>1)},removeProp:function(e){return this.each(function(){delete this[S.propFix[e]||e]})}}),S.extend({prop:function(e,t,n){var r,i,a=e.nodeType;if(!(a===3||a===8||a===2))return(a!==1||!S.isXMLDoc(e))&&(t=S.propFix[t]||t,i=S.propHooks[t]),n===void 0?i&&`get`in i&&(r=i.get(e,t))!==null?r:e[t]:i&&`set`in i&&(r=i.set(e,n,t))!==void 0?r:e[t]=n},propHooks:{tabIndex:{get:function(e){var t=e.getAttribute(`tabindex`);return t?parseInt(t,10):On.test(e.nodeName)||kn.test(e.nodeName)&&e.href?0:-1}}},propFix:{for:`htmlFor`,class:`className`}}),E&&(S.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),S.each([`tabIndex`,`readOnly`,`maxLength`,`cellSpacing`,`cellPadding`,`rowSpan`,`colSpan`,`useMap`,`frameBorder`,`contentEditable`],function(){S.propFix[this.toLowerCase()]=this});function An(e){return(e.match(R)||[]).join(` `)}function jn(e){return e.getAttribute&&e.getAttribute(`class`)||``}function Mn(e){return Array.isArray(e)?e:typeof e==`string`&&e.match(R)||[]}S.fn.extend({addClass:function(e){var t,n,r,i,a,o;return typeof e==`function`?this.each(function(t){S(this).addClass(e.call(this,t,jn(this)))}):(t=Mn(e),t.length?this.each(function(){if(r=jn(this),n=this.nodeType===1&&` `+An(r)+` `,n){for(a=0;a<t.length;a++)i=t[a],n.indexOf(` `+i+` `)<0&&(n+=i+` `);o=An(n),r!==o&&this.setAttribute(`class`,o)}}):this)},removeClass:function(e){var t,n,r,i,a,o;return typeof e==`function`?this.each(function(t){S(this).removeClass(e.call(this,t,jn(this)))}):arguments.length?(t=Mn(e),t.length?this.each(function(){if(r=jn(this),n=this.nodeType===1&&` `+An(r)+` `,n){for(a=0;a<t.length;a++)for(i=t[a];n.indexOf(` `+i+` `)>-1;)n=n.replace(` `+i+` `,` `);o=An(n),r!==o&&this.setAttribute(`class`,o)}}):this):this.attr(`class`,``)},toggleClass:function(e,t){var n,r,i,a;return typeof e==`function`?this.each(function(n){S(this).toggleClass(e.call(this,n,jn(this),t),t)}):typeof t==`boolean`?t?this.addClass(e):this.removeClass(e):(n=Mn(e),n.length?this.each(function(){for(a=S(this),i=0;i<n.length;i++)r=n[i],a.hasClass(r)?a.removeClass(r):a.addClass(r)}):this)},hasClass:function(e){var t,n,r=0;for(t=` `+e+` `;n=this[r++];)if(n.nodeType===1&&(` `+An(jn(n))+` `).indexOf(t)>-1)return!0;return!1}}),S.fn.extend({val:function(e){var t,n,r,i=this[0];return arguments.length?(r=typeof e==`function`,this.each(function(n){var i;this.nodeType===1&&(i=r?e.call(this,n,S(this).val()):e,i==null?i=``:typeof i==`number`?i+=``:Array.isArray(i)&&(i=S.map(i,function(e){return e==null?``:e+``})),t=S.valHooks[this.type]||S.valHooks[this.nodeName.toLowerCase()],(!t||!(`set`in t)||t.set(this,i,`value`)===void 0)&&(this.value=i))})):i?(t=S.valHooks[i.type]||S.valHooks[i.nodeName.toLowerCase()],t&&`get`in t&&(n=t.get(i,`value`))!==void 0?n:(n=i.value,n??``)):void 0}}),S.extend({valHooks:{select:{get:function(e){var t,n,r,i=e.options,a=e.selectedIndex,o=e.type===`select-one`,s=o?null:[],c=o?a+1:i.length;for(r=a<0?c:o?a:0;r<c;r++)if(n=i[r],n.selected&&!n.disabled&&(!n.parentNode.disabled||!C(n.parentNode,`optgroup`))){if(t=S(n).val(),o)return t;s.push(t)}return s},set:function(e,t){for(var n,r,i=e.options,a=S.makeArray(t),o=i.length;o--;)r=i[o],(r.selected=S.inArray(S(r).val(),a)>-1)&&(n=!0);return n||(e.selectedIndex=-1),a}}}}),E&&(S.valHooks.option={get:function(e){return e.getAttribute(`value`)??An(S.text(e))}}),S.each([`radio`,`checkbox`],function(){S.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=S.inArray(S(e).val(),t)>-1}}});var Nn=/^(?:focusinfocus|focusoutblur)$/,Pn=function(e){e.stopPropagation()};S.extend(S.event,{trigger:function(t,n,r,i){var a,o,s,c,l,d,f,p,m=[r||_],g=u.call(t,`type`)?t.type:t,v=u.call(t,`namespace`)?t.namespace.split(`.`):[];if(o=p=s=r||=_,!(r.nodeType===3||r.nodeType===8)&&!Nn.test(g+S.event.triggered)&&(g.indexOf(`.`)>-1&&(v=g.split(`.`),g=v.shift(),v.sort()),l=g.indexOf(`:`)<0&&`on`+g,t=t[S.expando]?t:new S.Event(g,typeof t==`object`&&t),t.isTrigger=i?2:3,t.namespace=v.join(`.`),t.rnamespace=t.namespace?RegExp(`(^|\\.)`+v.join(`\\.(?:.*\\.|)`)+`(\\.|$)`):null,t.result=void 0,t.target||=r,n=n==null?[t]:S.makeArray(n,[t]),f=S.event.special[g]||{},!(!i&&f.trigger&&f.trigger.apply(r,n)===!1))){if(!i&&!f.noBubble&&!h(r)){for(c=f.delegateType||g,Nn.test(c+g)||(o=o.parentNode);o;o=o.parentNode)m.push(o),s=o;s===(r.ownerDocument||_)&&m.push(s.defaultView||s.parentWindow||e)}for(a=0;(o=m[a++])&&!t.isPropagationStopped();)p=o,t.type=a>1?c:f.bindType||g,d=(G.get(o,`events`)||Object.create(null))[t.type]&&G.get(o,`handle`),d&&d.apply(o,n),d=l&&o[l],d&&d.apply&&gt(o)&&(t.result=d.apply(o,n),t.result===!1&&t.preventDefault());return t.type=g,!i&&!t.isDefaultPrevented()&&(!f._default||f._default.apply(m.pop(),n)===!1)&&gt(r)&&l&&typeof r[g]==`function`&&!h(r)&&(s=r[l],s&&(r[l]=null),S.event.triggered=g,t.isPropagationStopped()&&p.addEventListener(g,Pn),r[g](),t.isPropagationStopped()&&p.removeEventListener(g,Pn),S.event.triggered=void 0,s&&(r[l]=s)),t.result}},simulate:function(e,t,n){var r=S.extend(new S.Event,n,{type:e,isSimulated:!0});S.event.trigger(r,null,t)}}),S.fn.extend({trigger:function(e,t){return this.each(function(){S.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];if(n)return S.event.trigger(e,t,n,!0)}});var Fn=e.location,In={guid:Date.now()},Ln=/\?/;S.parseXML=function(t){var n,r;if(!t||typeof t!=`string`)return null;try{n=new e.DOMParser().parseFromString(t,`text/xml`)}catch{}return r=n&&n.getElementsByTagName(`parsererror`)[0],(!n||r)&&S.error(`Invalid XML: `+(r?S.map(r.childNodes,function(e){return e.textContent}).join(`
`):t)),n};var Rn=/\[\]$/,zn=/\r?\n/g,Bn=/^(?:submit|button|image|reset|file)$/i,Vn=/^(?:input|select|textarea|keygen)/i;function Hn(e,t,n,r){var i;if(Array.isArray(t))S.each(t,function(t,i){n||Rn.test(e)?r(e,i):Hn(e+`[`+(typeof i==`object`&&i?t:``)+`]`,i,n,r)});else if(!n&&m(t)===`object`)for(i in t)Hn(e+`[`+i+`]`,t[i],n,r);else r(e,t)}S.param=function(e,t){var n,r=[],i=function(e,t){var n=typeof t==`function`?t():t;r[r.length]=encodeURIComponent(e)+`=`+encodeURIComponent(n??``)};if(e==null)return``;if(Array.isArray(e)||e.jquery&&!S.isPlainObject(e))S.each(e,function(){i(this.name,this.value)});else for(n in e)Hn(n,e[n],t,i);return r.join(`&`)},S.fn.extend({serialize:function(){return S.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=S.prop(this,`elements`);return e?S.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!S(this).is(`:disabled`)&&Vn.test(this.nodeName)&&!Bn.test(e)&&(this.checked||!Wt.test(e))}).map(function(e,t){var n=S(this).val();return n==null?null:Array.isArray(n)?S.map(n,function(e){return{name:t.name,value:e.replace(zn,`\r
`)}}):{name:t.name,value:n.replace(zn,`\r
`)}}).get()}});var Un=/%20/g,Wn=/#.*$/,Gn=/([?&])_=[^&]*/,Kn=/^(.*?):[ \t]*([^\r\n]*)$/gm,qn=/^(?:about|app|app-storage|.+-extension|file|res|widget):$/,Jn=/^(?:GET|HEAD)$/,Yn=/^\/\//,Xn={},Zn={},Qn=`*/*`,$n=_.createElement(`a`);$n.href=Fn.href;function er(e){return function(t,n){typeof t!=`string`&&(n=t,t=`*`);var r,i=0,a=t.toLowerCase().match(R)||[];if(typeof n==`function`)for(;r=a[i++];)r[0]===`+`?(r=r.slice(1)||`*`,(e[r]=e[r]||[]).unshift(n)):(e[r]=e[r]||[]).push(n)}}function tr(e,t,n,r){var i={},a=e===Zn;function o(s){var c;return i[s]=!0,S.each(e[s]||[],function(e,s){var l=s(t,n,r);if(typeof l==`string`&&!a&&!i[l])return t.dataTypes.unshift(l),o(l),!1;if(a)return!(c=l)}),c}return o(t.dataTypes[0])||!i[`*`]&&o(`*`)}function nr(e,t){var n,r,i=S.ajaxSettings.flatOptions||{};for(n in t)t[n]!==void 0&&((i[n]?e:r||={})[n]=t[n]);return r&&S.extend(!0,e,r),e}function rr(e,t,n){for(var r,i,a,o,s=e.contents,c=e.dataTypes;c[0]===`*`;)c.shift(),r===void 0&&(r=e.mimeType||t.getResponseHeader(`Content-Type`));if(r){for(i in s)if(s[i]&&s[i].test(r)){c.unshift(i);break}}if(c[0]in n)a=c[0];else{for(i in n){if(!c[0]||e.converters[i+` `+c[0]]){a=i;break}o||=i}a||=o}if(a)return a!==c[0]&&c.unshift(a),n[a]}function ir(e,t,n,r){var i,a,o,s,c,l={},u=e.dataTypes.slice();if(u[1])for(o in e.converters)l[o.toLowerCase()]=e.converters[o];for(a=u.shift();a;)if(e.responseFields[a]&&(n[e.responseFields[a]]=t),!c&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),c=a,a=u.shift(),a){if(a===`*`)a=c;else if(c!==`*`&&c!==a){if(o=l[c+` `+a]||l[`* `+a],!o){for(i in l)if(s=i.split(` `),s[1]===a&&(o=l[c+` `+s[0]]||l[`* `+s[0]],o)){o===!0?o=l[i]:l[i]!==!0&&(a=s[0],u.unshift(s[1]));break}}if(o!==!0)if(o&&e.throws)t=o(t);else try{t=o(t)}catch(e){return{state:`parsererror`,error:o?e:`No conversion from `+c+` to `+a}}}}return{state:`success`,data:t}}S.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Fn.href,type:`GET`,isLocal:qn.test(Fn.protocol),global:!0,processData:!0,async:!0,contentType:`application/x-www-form-urlencoded; charset=UTF-8`,accepts:{"*":Qn,text:`text/plain`,html:`text/html`,xml:`application/xml, text/xml`,json:`application/json, text/javascript`},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:`responseXML`,text:`responseText`,json:`responseJSON`},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":S.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?nr(nr(e,S.ajaxSettings),t):nr(S.ajaxSettings,e)},ajaxPrefilter:er(Xn),ajaxTransport:er(Zn),ajax:function(t,n){typeof t==`object`&&(n=t,t=void 0),n||={};var r,i,a,o,s,c,l,u,d,f,p=S.ajaxSetup({},n),m=p.context||p,h=p.context&&(m.nodeType||m.jquery)?S(m):S.event,g=S.Deferred(),v=S.Callbacks(`once memory`),y=p.statusCode||{},b={},x={},C=`canceled`,w={readyState:0,getResponseHeader:function(e){var t;if(l){if(!o)for(o={};t=Kn.exec(a);)o[t[1].toLowerCase()+` `]=(o[t[1].toLowerCase()+` `]||[]).concat(t[2]);t=o[e.toLowerCase()+` `]}return t==null?null:t.join(`, `)},getAllResponseHeaders:function(){return l?a:null},setRequestHeader:function(e,t){return l??(e=x[e.toLowerCase()]=x[e.toLowerCase()]||e,b[e]=t),this},overrideMimeType:function(e){return l??(p.mimeType=e),this},statusCode:function(e){var t;if(e)if(l)w.always(e[w.status]);else for(t in e)y[t]=[y[t],e[t]];return this},abort:function(e){var t=e||C;return r&&r.abort(t),T(0,t),this}};if(g.promise(w),p.url=((t||p.url||Fn.href)+``).replace(Yn,Fn.protocol+`//`),p.type=n.method||n.type||p.method||p.type,p.dataTypes=(p.dataType||`*`).toLowerCase().match(R)||[``],p.crossDomain==null){c=_.createElement(`a`);try{c.href=p.url,c.href=c.href,p.crossDomain=$n.protocol+`//`+$n.host!=c.protocol+`//`+c.host}catch{p.crossDomain=!0}}if(tr(Xn,p,n,w),p.data&&p.processData&&typeof p.data!=`string`&&(p.data=S.param(p.data,p.traditional)),l)return w;for(d in u=S.event&&p.global,u&&S.active++===0&&S.event.trigger(`ajaxStart`),p.type=p.type.toUpperCase(),p.hasContent=!Jn.test(p.type),i=p.url.replace(Wn,``),p.hasContent?p.data&&p.processData&&(p.contentType||``).indexOf(`application/x-www-form-urlencoded`)===0&&(p.data=p.data.replace(Un,`+`)):(f=p.url.slice(i.length),p.data&&(p.processData||typeof p.data==`string`)&&(i+=(Ln.test(i)?`&`:`?`)+p.data,delete p.data),p.cache===!1&&(i=i.replace(Gn,`$1`),f=(Ln.test(i)?`&`:`?`)+`_=`+In.guid+++f),p.url=i+f),p.ifModified&&(S.lastModified[i]&&w.setRequestHeader(`If-Modified-Since`,S.lastModified[i]),S.etag[i]&&w.setRequestHeader(`If-None-Match`,S.etag[i])),(p.data&&p.hasContent&&p.contentType!==!1||n.contentType)&&w.setRequestHeader(`Content-Type`,p.contentType),w.setRequestHeader(`Accept`,p.dataTypes[0]&&p.accepts[p.dataTypes[0]]?p.accepts[p.dataTypes[0]]+(p.dataTypes[0]===`*`?``:`, `+Qn+`; q=0.01`):p.accepts[`*`]),p.headers)w.setRequestHeader(d,p.headers[d]);if(p.beforeSend&&(p.beforeSend.call(m,w,p)===!1||l))return w.abort();if(C=`abort`,v.add(p.complete),w.done(p.success),w.fail(p.error),r=tr(Zn,p,n,w),!r)T(-1,`No Transport`);else{if(w.readyState=1,u&&h.trigger(`ajaxSend`,[w,p]),l)return w;p.async&&p.timeout>0&&(s=e.setTimeout(function(){w.abort(`timeout`)},p.timeout));try{l=!1,r.send(b,T)}catch(e){if(l)throw e;T(-1,e)}}function T(t,n,o,c){var d,f,_,b,x,C=n;l||(l=!0,s&&e.clearTimeout(s),r=void 0,a=c||``,w.readyState=t>0?4:0,d=t>=200&&t<300||t===304,o&&(b=rr(p,w,o)),!d&&S.inArray(`script`,p.dataTypes)>-1&&S.inArray(`json`,p.dataTypes)<0&&(p.converters[`text script`]=function(){}),b=ir(p,b,w,d),d?(p.ifModified&&(x=w.getResponseHeader(`Last-Modified`),x&&(S.lastModified[i]=x),x=w.getResponseHeader(`etag`),x&&(S.etag[i]=x)),t===204||p.type===`HEAD`?C=`nocontent`:t===304?C=`notmodified`:(C=b.state,f=b.data,_=b.error,d=!_)):(_=C,(t||!C)&&(C=`error`,t<0&&(t=0))),w.status=t,w.statusText=(n||C)+``,d?g.resolveWith(m,[f,C,w]):g.rejectWith(m,[w,C,_]),w.statusCode(y),y=void 0,u&&h.trigger(d?`ajaxSuccess`:`ajaxError`,[w,p,d?f:_]),v.fireWith(m,[w,C]),u&&(h.trigger(`ajaxComplete`,[w,p]),--S.active||S.event.trigger(`ajaxStop`)))}return w},getJSON:function(e,t,n){return S.get(e,t,n,`json`)},getScript:function(e,t){return S.get(e,void 0,t,`script`)}}),S.each([`get`,`post`],function(e,t){S[t]=function(e,n,r,i){return(typeof n==`function`||n===null)&&(i||=r,r=n,n=void 0),S.ajax(S.extend({url:e,type:t,dataType:i,data:n,success:r},S.isPlainObject(e)&&e))}}),S.ajaxPrefilter(function(e){for(var t in e.headers)t.toLowerCase()===`content-type`&&(e.contentType=e.headers[t]||``)}),S._evalUrl=function(e,t,n){return S.ajax({url:e,type:`GET`,dataType:`script`,cache:!0,async:!1,global:!1,scriptAttrs:t.crossOrigin?{crossOrigin:t.crossOrigin}:void 0,converters:{"text script":function(){}},dataFilter:function(e){S.globalEval(e,t,n)}})},S.fn.extend({wrapAll:function(e){var t;return this[0]&&(typeof e==`function`&&(e=e.call(this[0])),t=S(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){for(var e=this;e.firstElementChild;)e=e.firstElementChild;return e}).append(this)),this},wrapInner:function(e){return typeof e==`function`?this.each(function(t){S(this).wrapInner(e.call(this,t))}):this.each(function(){var t=S(this),n=t.contents();n.length?n.wrapAll(e):t.append(e)})},wrap:function(e){var t=typeof e==`function`;return this.each(function(n){S(this).wrapAll(t?e.call(this,n):e)})},unwrap:function(e){return this.parent(e).not(`body`).each(function(){S(this).replaceWith(this.childNodes)}),this}}),S.expr.pseudos.hidden=function(e){return!S.expr.pseudos.visible(e)},S.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},S.ajaxSettings.xhr=function(){return new e.XMLHttpRequest};var ar={0:200};S.ajaxTransport(function(e){var t;return{send:function(n,r){var i,a=e.xhr();if(a.open(e.type,e.url,e.async,e.username,e.password),e.xhrFields)for(i in e.xhrFields)a[i]=e.xhrFields[i];for(i in e.mimeType&&a.overrideMimeType&&a.overrideMimeType(e.mimeType),!e.crossDomain&&!n[`X-Requested-With`]&&(n[`X-Requested-With`]=`XMLHttpRequest`),n)a.setRequestHeader(i,n[i]);t=function(e){return function(){t&&(t=a.onload=a.onerror=a.onabort=a.ontimeout=null,e===`abort`?a.abort():e===`error`?r(a.status,a.statusText):r(ar[a.status]||a.status,a.statusText,(a.responseType||`text`)===`text`?{text:a.responseText}:{binary:a.response},a.getAllResponseHeaders()))}},a.onload=t(),a.onabort=a.onerror=a.ontimeout=t(`error`),t=t(`abort`);try{a.send(e.hasContent&&e.data||null)}catch(e){if(t)throw e}},abort:function(){t&&t()}}});function or(e){return e.scriptAttrs||!e.headers&&(e.crossDomain||e.async&&S.inArray(`json`,e.dataTypes)<0)}S.ajaxSetup({accepts:{script:`text/javascript, application/javascript, application/ecmascript, application/x-ecmascript`},converters:{"text script":function(e){return S.globalEval(e),e}}}),S.ajaxPrefilter(`script`,function(e){e.cache===void 0&&(e.cache=!1),or(e)&&(e.type=`GET`)}),S.ajaxTransport(`script`,function(e){if(or(e)){var t,n;return{send:function(r,i){t=S(`<script>`).attr(e.scriptAttrs||{}).prop({charset:e.scriptCharset,src:e.url}).on(`load error`,n=function(e){t.remove(),n=null,e&&i(e.type===`error`?404:200,e.type)}),_.head.appendChild(t[0])},abort:function(){n&&n()}}}});var sr=[],cr=/(=)\?(?=&|$)|\?\?/;S.ajaxSetup({jsonp:`callback`,jsonpCallback:function(){var e=sr.pop()||S.expando+`_`+In.guid++;return this[e]=!0,e}}),S.ajaxPrefilter(`jsonp`,function(t,n,r){var i,a,o,s=t.jsonp!==!1&&(cr.test(t.url)?`url`:typeof t.data==`string`&&(t.contentType||``).indexOf(`application/x-www-form-urlencoded`)===0&&cr.test(t.data)&&`data`);return i=t.jsonpCallback=typeof t.jsonpCallback==`function`?t.jsonpCallback():t.jsonpCallback,s?t[s]=t[s].replace(cr,`$1`+i):t.jsonp!==!1&&(t.url+=(Ln.test(t.url)?`&`:`?`)+t.jsonp+`=`+i),t.converters[`script json`]=function(){return o||S.error(i+` was not called`),o[0]},t.dataTypes[0]=`json`,a=e[i],e[i]=function(){o=arguments},r.always(function(){a===void 0?S(e).removeProp(i):e[i]=a,t[i]&&(t.jsonpCallback=n.jsonpCallback,sr.push(i)),o&&typeof a==`function`&&a(o[0]),o=a=void 0}),`script`}),S.ajaxPrefilter(function(t,n){typeof t.data!=`string`&&!S.isPlainObject(t.data)&&!Array.isArray(t.data)&&!(`processData`in n)&&(t.processData=!1),t.data instanceof e.FormData&&(t.contentType=!1)}),S.parseHTML=function(t,n,r){if(typeof t!=`string`&&!Qe(t+``))return[];typeof n==`boolean`&&(r=n,n=!1);var i,a;return n||=new e.DOMParser().parseFromString(``,`text/html`),i=Ze.exec(t),a=!r&&[],i?[n.createElement(i[1])]:(i=Bt([t],n,a),a&&a.length&&S(a).remove(),S.merge([],i.childNodes))},S.fn.load=function(e,t,n){var r,i,a,o=this,s=e.indexOf(` `);return s>-1&&(r=An(e.slice(s)),e=e.slice(0,s)),typeof t==`function`?(n=t,t=void 0):t&&typeof t==`object`&&(i=`POST`),o.length>0&&S.ajax({url:e,type:i||`GET`,dataType:`html`,data:t}).done(function(e){a=arguments,o.html(r?S(`<div>`).append(S.parseHTML(e)).find(r):e)}).always(n&&function(e,t){o.each(function(){n.apply(this,a||[e.responseText,t,e])})}),this},S.expr.pseudos.animated=function(e){return S.grep(S.timers,function(t){return e===t.elem}).length},S.offset={setOffset:function(e,t,n){var r,i,a,o,s,c,l,u=S.css(e,`position`),d=S(e),f={};u===`static`&&(e.style.position=`relative`),s=d.offset(),a=S.css(e,`top`),c=S.css(e,`left`),l=(u===`absolute`||u===`fixed`)&&(a+c).indexOf(`auto`)>-1,l?(r=d.position(),o=r.top,i=r.left):(o=parseFloat(a)||0,i=parseFloat(c)||0),typeof t==`function`&&(t=t.call(e,n,S.extend({},s))),t.top!=null&&(f.top=t.top-s.top+o),t.left!=null&&(f.left=t.left-s.left+i),`using`in t?t.using.call(e,f):d.css(f)}},S.fn.extend({offset:function(e){if(arguments.length)return e===void 0?this:this.each(function(t){S.offset.setOffset(this,e,t)});var t,n,r=this[0];if(r)return r.getClientRects().length?(t=r.getBoundingClientRect(),n=r.ownerDocument.defaultView,{top:t.top+n.pageYOffset,left:t.left+n.pageXOffset}):{top:0,left:0}},position:function(){if(this[0]){var e,t,n,r=this[0],i={top:0,left:0};if(S.css(r,`position`)===`fixed`)t=r.getBoundingClientRect();else{for(t=this.offset(),n=r.ownerDocument,e=r.offsetParent||n.documentElement;e&&e!==n.documentElement&&S.css(e,`position`)===`static`;)e=e.offsetParent||n.documentElement;e&&e!==r&&e.nodeType===1&&S.css(e,`position`)!==`static`&&(i=S(e).offset(),i.top+=S.css(e,`borderTopWidth`,!0),i.left+=S.css(e,`borderLeftWidth`,!0))}return{top:t.top-i.top-S.css(r,`marginTop`,!0),left:t.left-i.left-S.css(r,`marginLeft`,!0)}}},offsetParent:function(){return this.map(function(){for(var e=this.offsetParent;e&&S.css(e,`position`)===`static`;)e=e.offsetParent;return e||A})}}),S.each({scrollLeft:`pageXOffset`,scrollTop:`pageYOffset`},function(e,t){var n=t===`pageYOffset`;S.fn[e]=function(r){return L(this,function(e,r,i){var a;if(h(e)?a=e:e.nodeType===9&&(a=e.defaultView),i===void 0)return a?a[t]:e[r];a?a.scrollTo(n?a.pageXOffset:i,n?i:a.pageYOffset):e[r]=i},e,r,arguments.length)}}),S.each({Height:`height`,Width:`width`},function(e,t){S.each({padding:`inner`+e,content:t,"":`outer`+e},function(n,r){S.fn[r]=function(i,a){var o=arguments.length&&(n||typeof i!=`boolean`),s=n||(i===!0||a===!0?`margin`:`border`);return L(this,function(t,n,i){var a;return h(t)?r.indexOf(`outer`)===0?t[`inner`+e]:t.document.documentElement[`client`+e]:t.nodeType===9?(a=t.documentElement,Math.max(t.body[`scroll`+e],a[`scroll`+e],t.body[`offset`+e],a[`offset`+e],a[`client`+e])):i===void 0?S.css(t,n,s):S.style(t,n,i,s)},t,o?i:void 0,o)}})}),S.each([`ajaxStart`,`ajaxStop`,`ajaxComplete`,`ajaxError`,`ajaxSuccess`,`ajaxSend`],function(e,t){S.fn[t]=function(e){return this.on(t,e)}}),S.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return arguments.length===1?this.off(e,`**`):this.off(t,e||`**`,n)},hover:function(e,t){return this.on(`mouseenter`,e).on(`mouseleave`,t||e)}}),S.each(`blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu`.split(` `),function(e,t){S.fn[t]=function(e,n){return arguments.length>0?this.on(t,null,e,n):this.trigger(t)}}),S.proxy=function(e,t){var n,r,a;if(typeof t==`string`&&(n=e[t],t=e,e=n),typeof e==`function`)return r=i.call(arguments,2),a=function(){return e.apply(t||this,r.concat(i.call(arguments)))},a.guid=e.guid=e.guid||S.guid++,a},S.holdReady=function(e){e?S.readyWait++:S.ready(!0)},S.expr[`:`]=S.expr.filters=S.expr.pseudos,typeof define==`function`&&define.amd&&define(`jquery`,[],function(){return S});var lr=e.jQuery,ur=e.$;return S.noConflict=function(t){return e.$===S&&(e.$=ur),t&&e.jQuery===S&&(e.jQuery=lr),S},t===void 0&&(e.jQuery=e.$=S),S}var i=r(window,!0);document.addEventListener(`DOMContentLoaded`,function(){let e=document.getElementById(`transactionType`),t=document.getElementById(`purchaseItems`),n=document.getElementById(`product_search`),r=document.getElementById(`searchResult`),i=document.getElementById(`productSearchUrl`),a=document.getElementById(`company_id`),o=document.getElementById(`store_id`),s=document.getElementById(`supplier_id`),c=document.getElementById(`grand_total`);if(!t)return;let l=e?e.value:`purchase`,u=i?i.value:``,d=[];function f(e){return e==null?``:String(e).replace(/&/g,`&amp;`).replace(/</g,`&lt;`).replace(/>/g,`&gt;`).replace(/"/g,`&quot;`).replace(/'/g,`&#039;`)}function p(e=``){let t=`
            <option value="">
                Shop / No Godown
            </option>
        `;return d.forEach(function(n){let r=String(e)===String(n.id)?`selected`:``;t+=`
                <option
                    value="${n.id}"
                    ${r}>

                    ${f(n.godown_name)}

                </option>
            `}),t}function m(e=!1){t.querySelectorAll(`.godown-item`).forEach(function(t){let n=``;e||(n=t.dataset.existingGodown||``),t.innerHTML=p(n),e&&delete t.dataset.existingGodown})}function h(){t.querySelectorAll(`.godown-item`).forEach(function(e){e.innerHTML=`
                    <option value="">
                        Loading godowns...
                    </option>
                `})}function g(e,t=!1){if(!e){d=[],m(!t);return}h(),fetch(`/purchases/godowns/${e}`,{headers:{Accept:`application/json`}}).then(function(e){if(!e.ok)throw Error(`Unable to load godowns`);return e.json()}).then(function(e){d=Array.isArray(e)?e:[],m(!t)}).catch(function(e){console.error(`Godown loading error:`,e),d=[],m(!t)})}a&&a.addEventListener(`change`,function(){let e=this.value,t=document.getElementById(`purchaseItems`);t&&(t.innerHTML=``);let n=document.getElementById(`grand_total`);if(n&&(n.value=`0.00`),o&&(o.innerHTML=`
                        <option value="">
                            Loading stores...
                        </option>
                    `),s&&(s.innerHTML=`
                        <option value="">
                            Loading suppliers...
                        </option>
                    `),d=[],m(!0),!e){o&&(o.innerHTML=`
                            <option value="">
                                Select Store
                            </option>
                        `),s&&(s.innerHTML=`
                            <option value="">
                                Select Supplier
                            </option>
                        `);return}fetch(`/purchases/stores/${e}`,{headers:{Accept:`application/json`}}).then(function(e){if(!e.ok)throw Error(`Unable to load stores`);return e.json()}).then(function(e){o&&(o.innerHTML=`
                            <option value="">
                                Select Store
                            </option>
                        `,e.forEach(function(e){let t=document.createElement(`option`);t.value=e.id,t.textContent=e.store_name,o.appendChild(t)}))}).catch(function(e){console.error(e),o&&(o.innerHTML=`
                                <option value="">
                                    Unable to load stores
                                </option>
                            `)}),fetch(`/purchases/suppliers/${e}`,{headers:{Accept:`application/json`}}).then(function(e){if(!e.ok)throw Error(`Unable to load suppliers`);return e.json()}).then(function(e){s&&(s.innerHTML=`
                            <option value="">
                                Select Supplier
                            </option>
                        `,e.forEach(function(e){let t=document.createElement(`option`);t.value=e.id,t.textContent=e.supplier_name,s.appendChild(t)}))}).catch(function(e){console.error(e),s&&(s.innerHTML=`
                                <option value="">
                                    Unable to load suppliers
                                </option>
                            `)})}),o&&o.addEventListener(`change`,function(){let e=this.value;t.querySelectorAll(`.godown-item`).forEach(function(e){delete e.dataset.existingGodown}),g(e,!1)});let _=null;n&&n.addEventListener(`input`,function(){let e=document.getElementById(`company_id`).value;if(!e){r.innerHTML=`<div class="list-group-item text-danger">Please select company first.</div>`,r.style.display=`block`;return}let t=this.value.trim();if(t.length<2){clearTimeout(_),r.innerHTML=``,r.style.display=`none`;return}clearTimeout(_),_=setTimeout(function(){v(t,e)},300)});function v(e,t){if(!u)return;r.style.display=`block`,r.innerHTML=`
            <div class="list-group-item text-center">
                <i class="fas fa-spinner fa-spin"></i>
                Searching...
            </div>
        `;let i=u+`?search=`+encodeURIComponent(e)+`&company_id=`+encodeURIComponent(t);fetch(i,{headers:{Accept:`application/json`}}).then(function(e){if(!e.ok)throw Error(`Product search failed`);return e.json()}).then(function(e){if(r.innerHTML=``,!Array.isArray(e)||e.length===0){r.innerHTML=`
                        <div class="list-group-item text-danger">
                            No product found
                        </div>
                    `;return}e.forEach(function(e){let t=document.createElement(`button`);t.type=`button`,t.className=`list-group-item list-group-item-action`;let i=e.product_code??``,a=e.product_name??``,o=e.barcode??``,s;s=parseFloat(l===`purchase`?e.purchase_price??e.cost_price??e.price??0:e.selling_price??e.price??0),parseFloat(e.gst_percent??e.gst??0);let c=parseFloat(e.current_stock??0);t.innerHTML=`

                            <div class="d-flex justify-content-between">

                                <div>

                                    <strong>
                                        ${f(i)}
                                    </strong>

                                    -
                                    ${f(a)}

                                    ${o?`
                                            <small class="text-muted ms-2">
                                                Barcode:
                                                ${f(o)}
                                            </small>
                                            `:``}

                                    <br>

                                    <small>

                                        Stock:
                                        ${c}

                                        |
                                        Price:
                                        ${s.toFixed(2)}

                                    </small>

                                </div>

                                <span class="badge bg-primary align-self-center">
                                    Add
                                </span>

                            </div>

                        `,t.addEventListener(`click`,function(){y(e),n.value=``,r.innerHTML=``,r.style.display=`none`}),r.appendChild(t)})}).catch(function(e){console.error(e),r.innerHTML=`
                    <div class="list-group-item text-danger">
                        Unable to search products
                    </div>
                `})}function y(e){let n=e.id,r=t.querySelector(`input[name="product_id[]"][value="${n}"]`);if(r){let e=r.closest(`tr`),t=e.querySelector(`.qty`);t.value=parseFloat(t.value||0)+1,x(e);return}let i=e.product_code??``,a=e.product_name??``,o=parseFloat(e.purchase_price??e.cost_price??e.price??0),s=parseFloat(e.selling_price??e.price??0),c=l===`purchase`?o:s,u=parseFloat(e.gst_percent??e.gst??0),d=parseFloat(e.current_stock??0),m=document.createElement(`tr`);m.innerHTML=`

            <!-- Product Code -->

            <td>

                ${f(i)}

                <input
                    type="hidden"
                    name="product_id[]"
                    value="${n}">

            </td>


            <!-- Product -->

            <td>

                ${f(a)}

                ${l===`sale`?`
                            <br>
                            <small class="text-success">
                                Stock:
                                <span class="stock">
                                    ${d}
                                </span>
                            </small>
                        `:``}

            </td>


            <!-- Godown -->

            <td>

                <select
                    name="godown_id[]"
                    class="form-select godown-item">

                    ${p(``)}

                </select>

            </td>


            <!-- Quantity -->

            <td>

                <input
                    type="number"
                    class="form-control qty"
                    name="qty[]"
                    value="1"
                    min="0.001"
                    step="0.001"
                    required>

            </td>


            <!-- Price -->

            <td>

                <input
                    type="number"
                    class="form-control price"
                    name="price[]"
                    value="${c.toFixed(2)}"
                    min="0"
                    step="0.01"
                    required>

            </td>


            <!-- GST -->

            <td>

                <input
                    type="number"
                    class="form-control gst"
                    name="gst[]"
                    value="${u}"
                    min="0"
                    step="0.01">

            </td>


            <!-- Amount -->

            <td>

                <input
                    type="text"
                    class="form-control amount"
                    readonly
                    value="0.00">

            </td>


            <!-- Remove -->

            <td>

                <button
                    type="button"
                    class="btn btn-danger btn-sm removeRow">

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        `,t.appendChild(m),x(m)}t.addEventListener(`input`,function(e){(e.target.classList.contains(`qty`)||e.target.classList.contains(`price`)||e.target.classList.contains(`gst`))&&x(e.target.closest(`tr`))});function b(e){if(l!==`sale`)return;let t=e.querySelector(`.qty`),n=e.querySelector(`.stock`);if(!t||!n)return;let r=parseFloat(t.value||0),i=parseFloat(n.textContent||0);r>i&&(alert(`Only `+i+` stock available.`),r=i,t.value=i)}function x(e){if(!e)return;b(e);let t=parseFloat(e.querySelector(`.qty`)?.value||0),n=parseFloat(e.querySelector(`.price`)?.value||0),r=parseFloat(e.querySelector(`.gst`)?.value||0),i=t*n,a=i+i*r/100,o=e.querySelector(`.amount`);o&&(o.value=a.toFixed(2)),S()}t.addEventListener(`click`,function(e){let t=e.target.closest(`.removeRow`);if(!t)return;let n=t.closest(`tr`);confirm(`Remove this product?`)&&(n.remove(),S())});function S(){let e=0;t.querySelectorAll(`.amount`).forEach(function(t){e+=parseFloat(t.value||0)}),c&&(c.value=e.toFixed(2)),C()}function C(){let e=document.getElementById(`paid_amount`),t=document.getElementById(`balance_amount`);!e||!t||(t.value=(parseFloat(c?.value||0)-parseFloat(e.value||0)).toFixed(2))}document.addEventListener(`input`,function(e){e.target.id===`paid_amount`&&C()}),document.addEventListener(`click`,function(e){n&&r&&!n.contains(e.target)&&!r.contains(e.target)&&(r.style.display=`none`)}),document.addEventListener(`keydown`,function(e){e.key===`Escape`&&r&&(r.style.display=`none`)}),n&&n.addEventListener(`keydown`,function(e){e.key===`Enter`&&e.preventDefault()}),o&&o.value?(t.querySelectorAll(`.godown-item`).forEach(function(e){e.value&&(e.dataset.existingGodown=e.value)}),g(o.value,!0)):(d=[],m(!0)),t.querySelectorAll(`tr`).forEach(function(e){x(e)}),S()}),document.addEventListener(`DOMContentLoaded`,function(){let e=document.getElementById(`salesForm`);if(!e){console.warn(`salesForm not found.`);return}let t=document.getElementById(`company_id`),n=document.getElementById(`sales_store_id`),r=document.getElementById(`sales_customer_id`),i=document.getElementById(`salesInvoiceNo`),a=document.getElementById(`product_search`),o=document.getElementById(`searchResult`),s=document.getElementById(`clearSearch`),c=document.getElementById(`salesItems`),l=document.getElementById(`emptyItems`),u=document.getElementById(`saveSaleBtn`),d=window.salesConfig||{},f=d.searchUrl||document.getElementById(`productSearchUrl`)?.value||`/api/products/search`,p=d.checkStockUrl||`/sales/check-stock`,m=d.quickTransferUrl||`/stock-transfers/quick`,h=!!d.isEdit,g=d.saleId||null,_=document.getElementById(`old_store_id`)?.value||``,v=document.getElementById(`old_customer_id`)?.value||``,y=document.querySelector(`meta[name="csrf-token"]`)?.getAttribute(`content`)||``;y||=e.querySelector(`input[name="_token"]`)?.value||``,y||console.error(`CSRF token not found.`);let b=null,x=-1,S=!1,C=!1,w=null,T=null,E=new WeakMap,ee=new WeakSet,D=document.getElementById(`stockShortageModal`),O=null;D&&typeof bootstrap<`u`&&(O=new bootstrap.Modal(D));let k=document.getElementById(`transferStockBtn`),te=document.getElementById(`godownStockList`),ne=document.getElementById(`noGodownStockMessage`),A=document.getElementById(`cannotFulfillMessage`);function j(e){return(parseFloat(e)||0).toFixed(2)}function M(e){let t=document.createElement(`div`);return t.textContent=e??``,t.innerHTML}function N(e,t){let n=document.getElementById(e);n&&(n.textContent=t)}function P(t,n=`warning`){let r=e.querySelector(`.sales-js-alert`);r&&r.remove();let i=document.createElement(`div`);i.className=`alert alert-${n} alert-dismissible fade show sales-js-alert`,i.innerHTML=`

            <i class="fas fa-info-circle me-1"></i>

            ${M(t)}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        `,e.prepend(i),setTimeout(function(){i&&i.parentNode&&i.remove()},4e3)}function F(){o&&(o.innerHTML=``,o.style.display=`none`,x=-1)}function re(){o&&(o.style.display=`block`)}function ie(){if(!c)return;let e=c.querySelectorAll(`.sale-item-row`).length;N(`itemCount`,`${e} ${e===1?`Item`:`Items`}`),l&&l.classList.toggle(`d-none`,e>0)}function ae(){return t?.value?n?.value?r?.value?!0:(P(`Please select a customer before searching products.`,`warning`),r?.focus(),!1):(P(`Please select a store before searching products.`,`warning`),n?.focus(),!1):(P(`Please select a company before searching products.`,`warning`),t?.focus(),!1)}async function oe(e,o=!1){if(!(!n||!r)){if(!e){n.innerHTML=`
                <option value="">
                    Select Company First
                </option>
            `,r.innerHTML=`
                <option value="">
                    Select Company First
                </option>
            `,n.disabled=!0,r.disabled=!0,a&&(a.disabled=!0),F();return}n.disabled=!0,r.disabled=!0,n.innerHTML=`
            <option value="">
                Loading stores...
            </option>
        `,r.innerHTML=`
            <option value="">
                Loading customers...
            </option>
        `,a&&(a.disabled=!0);try{let s=`/sales/company/${encodeURIComponent(e)}/data`,c=await fetch(s,{method:`GET`,headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`}});if(!c.ok){let e=`Unable to load company data. HTTP ${c.status}`;try{let t=await c.json();t.message&&(e=t.message)}catch{}throw Error(e)}let l=await c.json();n.innerHTML=`
                <option value="">
                    Select Store
                </option>
            `,(Array.isArray(l.stores)?l.stores:[]).forEach(function(e){let t=document.createElement(`option`);t.value=e.id,t.textContent=e.store_name??e.name??`Store ${e.id}`,n.appendChild(t)}),r.innerHTML=`
                <option value="">
                    Select Customer
                </option>
            `,(Array.isArray(l.customers)?l.customers:[]).forEach(function(e){let t=document.createElement(`option`);t.value=e.id,t.textContent=e.customer_name??e.name??`Customer ${e.id}`,r.appendChild(t)}),l.invoiceNo&&i&&(!h||!i.value)&&(i.value=l.invoiceNo),n.disabled=!1,r.disabled=!1,o&&(_&&(n.value=_),v&&(r.value=v)),t.value&&n.value&&r.value&&(a&&(a.disabled=!1),F())}catch(e){console.error(`Company data error:`,e),n.innerHTML=`
                <option value="">
                    Failed to load stores
                </option>
            `,r.innerHTML=`
                <option value="">
                    Failed to load customers
                </option>
            `,n.disabled=!0,r.disabled=!0,a&&(a.disabled=!0),P(e.message||`Unable to load company data.`,`danger`)}}}t&&(t.addEventListener(`change`,function(){oe(this.value,!1)}),t.value&&oe(t.value,!0));function I(){if(!a)return;let e=!!(t?.value&&n?.value&&r?.value);a.disabled=!e,e||F()}n?.addEventListener(`change`,function(){F(),I()}),r?.addEventListener(`change`,function(){F(),I()});async function se(e){if(e=String(e||``).trim(),e.length<2){F();return}if(!ae()){F();return}if(!f){P(`Product search URL is not configured.`,`danger`);return}let r=t.value,i=n.value,a=f.includes(`?`)?`&`:`?`,s=f+a+`search=`+encodeURIComponent(e)+`&company_id=`+encodeURIComponent(r)+`&sales_store_id=`+encodeURIComponent(i);o.innerHTML=`

            <div class="list-group-item text-center">

                <span
                    class="spinner-border spinner-border-sm me-2">
                </span>

                Searching...

            </div>

        `,re();try{let e=await fetch(s,{method:`GET`,headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`}});if(!e.ok)throw Error(`Product search failed. HTTP ${e.status}`);ce(await e.json())}catch(e){console.error(`Product search error:`,e),o.innerHTML=`

                <div class="list-group-item text-danger">

                    <i class="fas fa-exclamation-circle me-1"></i>

                    ${M(e.message||`Unable to search products.`)}

                </div>

            `,re()}}function ce(e){x=-1;let t=e;if(e&&Array.isArray(e.data)&&(t=e.data),Array.isArray(t)||(t=[]),t.length===0){o.innerHTML=`

                <div class="list-group-item text-center text-muted py-3">

                    <i class="fas fa-search-minus me-1"></i>

                    No product found.

                </div>

            `,re();return}o.innerHTML=``,t.forEach(function(e,t){let n=parseFloat(e.current_stock??e.shop_stock??e.stock??0)||0,r=parseFloat(e.sale_price??e.selling_price??e.price??0)||0;parseFloat(e.gst_percent??e.gst??0);let i=document.createElement(`button`);i.type=`button`,i.className=`list-group-item list-group-item-action product-search-item`,i.dataset.index=t,i.innerHTML=`

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="fw-semibold">

                                ${M(e.product_name??``)}

                            </div>

                            <small class="text-muted">

                                Code:
                                ${M(e.product_code??`-`)}

                                ${e.barcode?` | Barcode: `+M(e.barcode):``}

                            </small>

                        </div>


                        <div class="text-end">

                            <div class="small">

                                Stock:
                                <strong>
                                    ${j(n)}
                                </strong>

                            </div>

                            <div class="small text-muted">

                                ₹${j(r)}

                            </div>

                        </div>

                    </div>

                `,i.addEventListener(`click`,function(){L(e)}),o.appendChild(i)}),re()}a&&(a.addEventListener(`input`,function(){let e=this.value.trim();if(clearTimeout(b),e.length<2){F();return}if(!ae()){F();return}b=setTimeout(function(){se(e)},300)}),a.addEventListener(`keydown`,function(e){let t=o?.querySelectorAll(`.product-search-item`)||[];if(t.length){if(e.key===`ArrowDown`)e.preventDefault(),x=Math.min(x+1,t.length-1);else if(e.key===`ArrowUp`)e.preventDefault(),x=Math.max(x-1,0);else if(e.key===`Enter`){x>=0&&(e.preventDefault(),t[x].click());return}else if(e.key===`Escape`){F();return}t.forEach(function(e,t){e.classList.toggle(`active`,t===x)})}})),s?.addEventListener(`click`,function(){a&&(a.value=``,a.focus()),F()});function le(e){let t=c.querySelectorAll(`.sale-item-row`);for(let n of t){let t=n.querySelector(`input[name="product_id[]"]`);if(t&&String(t.value)===String(e))return n}return null}function ue(e,t){if(!e)return;let n=parseFloat(t)||0;e.dataset.shopStock=n;let r=e.querySelector(`.stock`);r&&(r.textContent=j(n),r.classList.remove(`bg-success`,`bg-danger`,`bg-warning`),n>0?r.classList.add(`bg-success`):r.classList.add(`bg-danger`))}function de(e,t){if(!e)return;let n=e.querySelector(`.transferRowStock, .transfer-stock-btn`);if(!n)return;let r=parseFloat(t)||0;r>0?(n.classList.remove(`btn-outline-primary`),n.classList.add(`btn-danger`),n.title=`Shop stock insufficient - Transfer ${j(r)} from Godown`):(n.classList.remove(`btn-danger`),n.classList.add(`btn-outline-primary`),n.title=`Transfer stock from Godown to Shop`)}function fe(e){let t=e.querySelector(`input[name="product_id[]"]`)?.value||``,n=parseFloat(e.querySelector(`.qty`)?.value)||0,r=parseFloat(e.querySelector(`.free_qty`)?.value)||0;return{productId:t,qty:n,freeQuantity:r,required:n+r}}function L(e){if(!e||!e.id)return;let t=le(e.id);if(t){let e=t.querySelector(`.qty`);e&&(e.value=((parseFloat(e.value)||0)+1).toFixed(2)),R(t),z(),F(),a&&(a.value=``,a.focus()),e&&(e.focus(),e.select()),me(t);return}let n=parseFloat(e.current_stock??e.shop_stock??e.stock??0)||0,r=parseFloat(e.sale_price??e.selling_price??e.price??0)||0,i=parseFloat(e.gst_percent??e.gst??0)||0,o=document.createElement(`tr`);o.className=`sale-item-row`,o.dataset.productId=e.id,o.dataset.shopStock=n,o.innerHTML=`

            <!-- CODE -->

            <td>

                <strong>
                    ${M(e.product_code??`-`)}
                </strong>

                <input
                    type="hidden"
                    name="product_id[]"
                    value="${e.id}">

            </td>


            <!-- PRODUCT -->

            <td>

                <div class="fw-semibold">

                    ${M(e.product_name??``)}

                </div>

                ${e.barcode?`
                            <small class="text-muted">
                                Barcode:
                                ${M(e.barcode)}
                            </small>
                          `:``}

            </td>


            <!-- LOCATION -->

            <td class="text-center">

                <span class="badge text-bg-primary">

                    <i class="fas fa-store me-1"></i>

                    Shop

                </span>

            </td>


            <!-- STOCK -->

            <td class="text-center">

                <span
                    class="badge bg-success stock">

                    ${j(n)}

                </span>

            </td>


            <!-- QUANTITY -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="qty[]"
                    class="form-control form-control-sm qty"
                    value="1">

            </td>


            <!-- FREE -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="free_quantity[]"
                    class="form-control form-control-sm free_qty"
                    value="0">

            </td>


            <!-- PRICE -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="price[]"
                    class="form-control form-control-sm price"
                    value="${j(r)}">

            </td>


            <!-- DISCOUNT -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="discount[]"
                    class="form-control form-control-sm discount"
                    value="0">

            </td>


            <!-- GST -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="gst[]"
                    class="form-control form-control-sm gst"
                    value="${i}">

            </td>


            <!-- GST AMOUNT -->

            <td>

                <input
                    type="text"
                    class="form-control form-control-sm gst_amount"
                    readonly
                    value="0.00">

            </td>


            <!-- TOTAL -->

            <td>

                <input
                    type="text"
                    class="form-control form-control-sm line_total fw-bold"
                    readonly
                    value="0.00">

            </td>


            <!-- TRANSFER -->

            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-outline-primary btn-sm transferRowStock"
                    title="Transfer stock from Godown to Shop">

                    <i class="fas fa-exchange-alt"></i>

                </button>

            </td>


            <!-- REMOVE -->

            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-outline-danger btn-sm removeRow"
                    title="Remove product">

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        `,c.appendChild(o),R(o),z(),ie(),a&&(a.value=``,a.focus()),F(),he(o)}function R(e){if(!e)return;let t=parseFloat(e.querySelector(`.qty`)?.value)||0,n=parseFloat(e.querySelector(`.price`)?.value)||0,r=parseFloat(e.querySelector(`.discount`)?.value)||0,i=parseFloat(e.querySelector(`.gst`)?.value)||0,a=t*n,o=Math.max(a-r,0),s=o*i/100,c=o+s,l=e.querySelector(`.gst_amount`),u=e.querySelector(`.line_total`);l&&(l.value=j(s)),u&&(u.value=j(c))}function z(){if(!c)return;let e=0,t=0,n=0,r=0;c.querySelectorAll(`.sale-item-row`).forEach(function(i){let a=parseFloat(i.querySelector(`.qty`)?.value)||0,o=parseFloat(i.querySelector(`.price`)?.value)||0,s=parseFloat(i.querySelector(`.discount`)?.value)||0,c=parseFloat(i.querySelector(`.gst`)?.value)||0,l=a*o,u=Math.max(l-s,0),d=u*c/100,f=u+d;e+=u,t+=s,n+=d,r+=f}),N(`subTotalDisplay`,j(e)),N(`discountDisplay`,j(t)),N(`gstDisplay`,j(n)),N(`grandTotalDisplay`,j(r));let i=document.getElementById(`sub_total`),a=document.getElementById(`discount_total`),o=document.getElementById(`gst_total`),s=document.getElementById(`grand_total`),l=document.getElementById(`paymentGrandTotal`);i&&(i.value=j(e)),a&&(a.value=j(t)),o&&(o.value=j(n)),s&&(s.value=j(r)),l&&(l.value=j(r)),pe(),ie()}function pe(){let e=parseFloat(document.getElementById(`grand_total`)?.value)||0,t=parseFloat(document.getElementById(`paid_amount`)?.value)||0,n=Math.max(e-t,0),r=document.getElementById(`balance_amount`);r&&(r.value=j(n))}c.addEventListener(`input`,function(e){let t=e.target.closest(`.sale-item-row`);t&&((e.target.classList.contains(`qty`)||e.target.classList.contains(`free_qty`)||e.target.classList.contains(`price`)||e.target.classList.contains(`discount`)||e.target.classList.contains(`gst`))&&(R(t),z()),(e.target.classList.contains(`qty`)||e.target.classList.contains(`free_qty`))&&me(t))}),c.addEventListener(`change`,function(e){let t=e.target.closest(`.sale-item-row`);t&&(R(t),z(),(e.target.classList.contains(`qty`)||e.target.classList.contains(`free_qty`))&&he(t))}),c.addEventListener(`blur`,function(e){let t=e.target.closest(`.sale-item-row`);t&&(e.target.classList.contains(`qty`)||e.target.classList.contains(`free_qty`))&&he(t)},!0);function me(e){let t=E.get(e);t&&clearTimeout(t);let n=setTimeout(function(){he(e)},500);E.set(e,n)}async function he(e){if(!e||C||S)return;let r=fe(e);!r.productId||r.required<=0||!t?.value||!n?.value||await ge(e,null,!1)}async function ge(e,r=null,i=!1){if(!e||ee.has(e))return{success:!1,cancelled:!0};let a=fe(e),o=t?.value||``,s=n?.value||``;if(!o)return i&&P(`Please select a company.`,`warning`),{success:!1,cancelled:!0};if(!s)return i&&P(`Please select a store.`,`warning`),{success:!1,cancelled:!0};if(!a.productId||a.required<=0)return{success:!1,cancelled:!0};ee.add(e);try{let t=await fetch(p,{method:`POST`,headers:{"Content-Type":`application/json`,Accept:`application/json`,"X-CSRF-TOKEN":y,"X-Requested-With":`XMLHttpRequest`},body:JSON.stringify({company_id:o,store_id:s,product_id:a.productId,quantity:a.required,sale_id:g})});if(t.status===419)throw Error(`Session expired or CSRF token is invalid. Please refresh the page.`);let n={};try{n=await t.json()}catch{throw Error(`Stock check failed. HTTP ${t.status}`)}if(!t.ok||!n.success)throw Error(n.message||`Unable to check Shop stock.`);if(ue(e,n.shop_stock),n.available)return de(e,0),{success:!0,transferred:!1,shop_stock:n.shop_stock};let i=parseFloat(n.shortage)||0;return de(e,i),w={companyId:o,storeId:s,productId:a.productId,quantity:a.required,rowIndex:r,shortage:i,shopStock:parseFloat(n.shop_stock)||0,godowns:Array.isArray(n.godowns)?n.godowns:[],row:e},_e(n),await ve()}catch(e){return console.error(`Stock check error:`,e),P(i?e.message||`Unable to check stock.`:e.message||`Unable to check Shop stock.`,`danger`),{success:!1,cancelled:!0}}finally{ee.delete(e)}}function _e(e){if(!O){alert(e.message||`Shop stock is insufficient.`);return}let t=w?.row||null,n=e.product_name||t?.querySelector(`td:nth-child(2) .fw-semibold`)?.textContent?.trim()||`Product`,r=parseFloat(e.required??w?.quantity)||0,i=parseFloat(e.shop_stock??w?.shopStock)||0,a=parseFloat(e.shortage)||0,o=document.getElementById(`shortageProductId`);o&&(o.value=w?.productId||``);let s=document.getElementById(`shortageRowIndex`);s&&(s.value=w?.rowIndex??``),N(`shortageProductName`,n),N(`shortageRequired`,j(r)),N(`shortageShopStock`,j(i)),N(`shortageQuantity`,j(a)),te&&(te.innerHTML=``),ne?.classList.add(`d-none`),A?.classList.add(`d-none`),k?.classList.remove(`d-none`);let c=Array.isArray(e.godowns)?e.godowns:[];c.length===0?(ne?.classList.remove(`d-none`),k?.classList.add(`d-none`)):(c.forEach(function(e){let t=parseFloat(e.available)||0,n=parseFloat(e.transfer_quantity)||Math.min(t,a),r=document.createElement(`div`);r.className=`d-flex justify-content-between align-items-center border rounded p-2 mb-2`,r.innerHTML=`

                        <div>

                            <strong>
                                ${M(e.name??e.godown_name??`Godown`)}
                            </strong>

                            <div class="small text-muted">

                                Available:
                                ${j(t)}

                            </div>

                        </div>


                        <span class="badge text-bg-primary">

                            Transfer
                            ${j(n)}

                        </span>

                    `,te?.appendChild(r)}),e.can_fulfill===!1?(A?.classList.remove(`d-none`),k?.classList.add(`d-none`)):k?.classList.remove(`d-none`)),O.show()}function ve(){return new Promise(function(e){T=e})}async function ye(){if(!(!w||S)){if(!w.godowns||w.godowns.length===0){P(`No stock is available in the godowns.`,`danger`);return}S=!0,k&&(k.disabled=!0,k.innerHTML=`

                <span
                    class="spinner-border spinner-border-sm me-1">
                </span>

                Transferring...

            `);try{let e=parseFloat(w.shortage)||0;for(let t of w.godowns){if(e<=0)break;let n=parseFloat(t.available)||0;if(n<=0)continue;let r=Math.min(n,e);if(r<=0)continue;let i=await fetch(m,{method:`POST`,headers:{"Content-Type":`application/json`,Accept:`application/json`,"X-CSRF-TOKEN":y,"X-Requested-With":`XMLHttpRequest`},body:JSON.stringify({company_id:w.companyId,from_godown_id:t.id,to_store_id:w.storeId,product_id:w.productId,quantity:r})});if(i.status===419)throw Error(`Session expired or CSRF token is invalid. Please refresh the page.`);let a={};try{a=await i.json()}catch{throw Error(`Stock transfer failed. HTTP ${i.status}`)}if(!i.ok||!a.success)throw Error(a.message||`Stock transfer failed.`);e-=r}if(e>1e-6)throw Error(`Only part of the required stock could be transferred. Remaining shortage: ${j(e)}`);if(O&&O.hide(),w.row){let e=(parseFloat(w.shopStock)||0)+(parseFloat(w.shortage)||0);ue(w.row,e),de(w.row,0)}if(typeof T==`function`){let e=T;T=null,e({success:!0,transferred:!0})}if(B(`Stock transferred to Shop successfully. You can continue the sale.`),w.row){let e=w.row;w=null,await ge(e,null,!1)}else w=null}catch(e){console.error(`Stock transfer error:`,e),P(e.message||`Unable to transfer stock.`,`danger`)}finally{S=!1,k&&(k.disabled=!1,k.innerHTML=`

                    <i class="fas fa-exchange-alt me-1"></i>

                    Transfer & Continue

                `)}}}k?.addEventListener(`click`,function(){ye()}),c.addEventListener(`click`,function(e){let t=e.target.closest(`.transferRowStock, .transfer-stock-btn`);if(!t)return;let n=t.closest(`.sale-item-row`);n&&ge(n,null,!1)}),D&&D.addEventListener(`hidden.bs.modal`,function(){if(typeof T==`function`){let e=T;T=null,e({success:!1,cancelled:!0})}w=null});function B(e){P(e,`success`)}c.addEventListener(`click`,function(e){let t=e.target.closest(`.removeRow`);if(!t)return;let n=t.closest(`.sale-item-row`);if(!n)return;let r=E.get(n);r&&clearTimeout(r),n.remove(),z(),ie(),a&&a.focus()});let be=document.getElementById(`paid_amount`);be?.addEventListener(`input`,pe),be?.addEventListener(`blur`,function(){let e=parseFloat(document.getElementById(`grand_total`)?.value)||0;(parseFloat(this.value)||0)>e&&(this.value=j(e)),pe()}),document.addEventListener(`click`,function(e){o&&a&&!o.contains(e.target)&&!a.contains(e.target)&&F()});function V(){let e=c.querySelectorAll(`.sale-item-row`);if(e.length===0)return P(`Please add at least one product.`,`warning`),a?.focus(),!1;if(!t?.value)return P(`Please select a company.`,`warning`),t?.focus(),!1;if(!n?.value)return P(`Please select a store.`,`warning`),n?.focus(),!1;if(!r?.value)return P(`Please select a customer.`,`warning`),r?.focus(),!1;for(let t of e){let e=parseFloat(t.querySelector(`.qty`)?.value)||0,n=parseFloat(t.querySelector(`.free_qty`)?.value)||0;if(e<=0)return P(`Quantity must be greater than zero.`,`warning`),t.querySelector(`.qty`)?.focus(),!1;if(n<0)return P(`Free quantity cannot be negative.`,`warning`),t.querySelector(`.free_qty`)?.focus(),!1}let i=parseFloat(document.getElementById(`grand_total`)?.value)||0;return(parseFloat(document.getElementById(`paid_amount`)?.value)||0)>i?(P(`Paid amount cannot be greater than Grand Total.`,`warning`),be?.focus(),!1):!0}async function H(){let e=Array.from(c.querySelectorAll(`.sale-item-row`));for(let t=0;t<e.length;t++){let n=e[t];if(!(await ge(n,t,!0)).success)return!1}return!0}e.addEventListener(`submit`,async function(t){if(t.preventDefault(),!(C||S)&&V()){if(u&&(u.disabled=!0,u.innerHTML=`

                    <span
                        class="spinner-border spinner-border-sm me-1">
                    </span>

                    Checking Stock...

                `),!await H()){u&&(u.disabled=!1,u.innerHTML=`

                        <i class="fas fa-save me-1"></i>

                        ${h?`Update Sales Invoice`:`Save Sales Invoice`}

                    `);return}z(),C=!0,u&&(u.innerHTML=`

                    <span
                        class="spinner-border spinner-border-sm me-1">
                    </span>

                    Saving...

                `),e.submit()}}),c.querySelectorAll(`.sale-item-row`).forEach(function(e){R(e);let r=e.querySelector(`.sale-location`);r&&(r.innerHTML=`

                        <span class="badge text-bg-primary">

                            <i class="fas fa-store me-1"></i>

                            Shop

                        </span>

                    `),t?.value&&n?.value&&he(e)}),z(),ie(),I(),a&&!a.disabled&&a.focus()}),document.addEventListener(`DOMContentLoaded`,function(){$(`#invoice_search`).keyup(function(){let e=$(this).val();if(e.length<2){$(`#invoiceResult`).hide().html(``);return}$.get($(`#searchUrl`).val(),{search:e},function(e){let t=``;$.each(e,function(e,n){t+=`
                <a href="#"
                    class="list-group-item list-group-item-action invoiceItem"
                    data-id="${n.id}">

                    <strong>${n.invoice_no}</strong><br>

                    <small>${n.customer.customer_name}</small>

                </a>`}),$(`#invoiceResult`).html(t).show()})}),$(document).on(`click`,`.invoiceItem`,function(t){t.preventDefault();let n=$(this).data(`id`);$.get($(`#invoiceUrl`).val()+`/`+n,function(t){e(t),console.log(t),$(`#invoice_search`).val(t.invoice_no),$(`#invoiceResult`).hide()})});function e(e){$(`#sale_id`).val(e.id),$(`#customer_id`).val(e.customer_id),$(`#customer_name`).val(e.customer.customer_name),$(`#customer_mobile`).val(e.customer.mobile??``),$(`#customer_address`).val(e.customer.billing_address??``);let n=``;e.items.forEach(function(e){n+=`

            <tr>

                <td>

                    ${e.product.product_code}

                    <input type="hidden"

                        name="product_id[]"

                        value="${e.product_id}">

                </td>

                <td>

                    ${e.product.product_name}

                </td>

                <td>

                    Sold : ${e.quantity}<br>

                    Returned : ${e.returned_qty}<br>

                    <strong>Balance : ${e.balance_qty}</strong>

                </td>

                <td>

                    <input

                        type="number"

                        class="form-control returnQty"

                        name="qty[]"

                        value="0"

                        min="0"

                        max="${e.balance_qty}"

                        data-price="${e.sale_price}"

                        data-gst="${e.gst_percent}">

                </td>

                <td>

                    ${parseFloat(e.sale_price).toFixed(2)}

                    <input

                        type="hidden"

                        name="price[]"

                        value="${e.sale_price}">

                </td>

                <td>

                    ${parseFloat(e.gst_percent).toFixed(2)} %

                    <br>

                    GST :
                    <span class="gstDisplay">0.00</span>

                    <input

                        type="hidden"

                        name="gst[]"

                        value="${e.gst_percent}">

                    <input

                        type="hidden"

                        class="gstAmount"

                        name="gst_amount[]"

                        value="0">

                </td>

                <td>

                    <span class="returnAmount">0.00</span>

                    <input

                        type="hidden"

                        class="amount"

                        name="amount[]"

                        value="0">

                </td>

            </tr>
            `}),$(`#returnItems`).html(n),t()}$(document).on(`keyup change`,`.returnQty`,function(){let e=$(this).closest(`tr`),n=parseFloat($(this).val())||0,r=parseFloat($(this).attr(`max`));n>r&&(alert(`Return quantity exceeds available balance.`),n=r,$(this).val(r));let i=parseFloat($(this).data(`price`))||0,a=parseFloat($(this).data(`gst`))||0,o=n*i,s=o*a/100,c=o+s;e.find(`.gstDisplay`).text(s.toFixed(2)),e.find(`.gstAmount`).val(s.toFixed(2)),e.find(`.returnAmount`).text(c.toFixed(2)),e.find(`.amount`).val(c.toFixed(2)),t()});function t(){let e=0,t=0;$(`.amount`).each(function(){e+=parseFloat($(this).val())||0}),$(`.gstAmount`).each(function(){t+=parseFloat($(this).val())||0}),$(`#grand_total`).val(e.toFixed(2)),$(`#gst_total`).val(t)}$(document).click(function(e){$(e.target).closest(`#invoice_search,#invoiceResult`).length||$(`#invoiceResult`).hide()})}),document.addEventListener(`DOMContentLoaded`,function(){$(`#purchase_search`).on(`keyup`,function(){let e=$(this).val().trim();if(e.length<2){$(`#purchaseResult`).hide().html(``);return}$.get($(`#searchUrl`).val(),{search:e}).done(function(e){let t=``;e.length?e.forEach(function(e){t+=`
                        <a href="#"
                           class="list-group-item list-group-item-action purchaseItem"
                           data-id="${e.id}">

                            <strong>
                                ${e.purchase_no}
                            </strong>

                            <br>

                            <small>
                                ${e.supplier?.supplier_name??``}
                            </small>

                        </a>
                    `}):t=`
                    <div class="list-group-item text-muted">
                        No purchase found
                    </div>
                `,$(`#purchaseResult`).html(t).show()}).fail(function(){$(`#purchaseResult`).html(`
                    <div class="list-group-item text-danger">
                        Error loading purchase
                    </div>
                `).show()})}),$(document).on(`click`,`.purchaseItem`,function(t){t.preventDefault();let n=$(this).data(`id`);$.get($(`#purchaseUrl`).val()+`/`+n).done(function(t){e(t),$(`#purchase_search`).val(t.purchase_no),$(`#purchaseResult`).hide().html(``)}).fail(function(){alert(`Unable to load purchase.`)})});function e(e){$(`#purchase_id`).val(e.id),$(`#supplier_id`).val(e.supplier_id),$(`#supplier_name`).val(e.supplier?.supplier_name??``),$(`#supplier_mobile`).val(e.supplier?.mobile??``),$(`#supplier_address`).val(e.supplier?.address??e.supplier?.billing_address??``);let n=``;if(!e.items||e.items.length===0){n=`
                <tr>
                    <td colspan="11"
                        class="text-center text-muted">

                        No items found in this purchase.

                    </td>
                </tr>
            `,$(`#returnItems`).html(n),t();return}e.items.forEach(function(e){let t=parseFloat(e.quantity)||0,r=parseFloat(e.returned_qty)||0,i=parseFloat(e.balance_qty)||0,a=parseFloat(e.purchase_price)||0,o=parseFloat(e.gst_percent)||0,s=e.godown_id??``,c=e.godown?.godown_name??`Shop / No Godown`;n+=`
                <tr>

                    <!-- Product Code -->
                    <td>

                        ${e.product?.product_code??``}

                        <input
                            type="hidden"
                            name="product_id[]"
                            value="${e.product_id}">

                    </td>


                    <!-- Product -->
                    <td>

                        ${e.product?.product_name??``}

                    </td>


                    <!-- Godown -->
                    <td>

                        ${c}

                        <input
                            type="hidden"
                            name="godown_id[]"
                            value="${s}">

                    </td>


                    <!-- Purchased -->
                    <td class="text-center">

                        ${t.toFixed(2)}

                    </td>


                    <!-- Returned -->
                    <td class="text-center">

                        ${r.toFixed(2)}

                    </td>


                    <!-- Balance -->
                    <td class="text-center">

                        <strong>
                            ${i.toFixed(2)}
                        </strong>

                    </td>


                    <!-- Return Qty -->
                    <td>

                        <input
                            type="number"
                            class="form-control returnQty text-end"
                            name="qty[]"
                            value="0"
                            min="0"
                            max="${i}"
                            step="0.01"

                            data-price="${a}"
                            data-gst="${o}">

                    </td>


                    <!-- Rate -->
                    <td class="text-end">

                        ${a.toFixed(2)}

                        <input
                            type="hidden"
                            name="price[]"
                            value="${a}">

                    </td>


                    <!-- GST -->
                    <td class="text-end">

                        ${o.toFixed(2)}%

                        <input
                            type="hidden"
                            name="gst[]"
                            value="${o}">

                    </td>


                    <!-- GST Amount -->
                    <td class="text-end">

                        <span class="gstAmount">
                            0.00
                        </span>

                        <input
                            type="hidden"
                            name="gst_amount[]"
                            value="0">

                    </td>


                    <!-- Total -->
                    <td class="text-end">

                        <span class="returnAmount">
                            0.00
                        </span>

                        <input
                            type="hidden"
                            name="amount[]"
                            value="0">

                    </td>

                </tr>
            `}),$(`#returnItems`).html(n),t()}$(document).on(`input`,`.returnQty`,function(){let e=$(this),n=e.closest(`tr`),r=parseFloat(e.val())||0,i=parseFloat(e.attr(`max`))||0;r<0&&(r=0,e.val(0)),r>i&&(r=i,e.val(i),alert(`Return quantity exceeds balance quantity.`));let a=parseFloat(e.data(`price`))||0,o=parseFloat(e.data(`gst`))||0,s=r*a,c=s*o/100,l=s+c;n.find(`.gstAmount`).text(c.toFixed(2)),n.find(`.returnAmount`).text(l.toFixed(2)),n.find(`input[name="gst_amount[]"]`).val(c.toFixed(2)),n.find(`input[name="amount[]"]`).val(l.toFixed(2)),t()});function t(){let e=0,t=0,n=0;$(`#returnItems tr`).each(function(){let r=$(this).find(`.returnQty`);if(!r.length)return;let i=parseFloat(r.val())||0,a=parseFloat(r.data(`price`))||0,o=parseFloat(r.data(`gst`))||0,s=i*a,c=s*o/100,l=s+c;e+=s,t+=c,n+=l}),$(`#purchase_return_sub_total`).val(e.toFixed(2)),$(`#purchase_return_gst_total`).val(t.toFixed(2)),$(`#purchase_return_grand_total`).val(n.toFixed(2)),$(`#purchase_return_sub_total_hidden`).val(e.toFixed(2)),$(`#purchase_return_gst_total_hidden`).val(t.toFixed(2))}$(document).on(`click`,function(e){$(e.target).closest(`#purchase_search, #purchaseResult`).length||$(`#purchaseResult`).hide()}),t()}),document.addEventListener(`DOMContentLoaded`,function(){$(function(){$(`#btnSearch`).click(function(){let e=!1;if($(`#reportForm`).find(`input, select`).not(`[name="_token"]`).each(function(){if($(this).val().trim()!==``)return e=!0,!1}),!e){alert(`Please select at least one search filter.`);return}$.ajax({url:`/purchase-reports/search`,type:`POST`,data:$(`#reportForm`).serialize(),success:function(e){$(`#reportResult`).html(e)},error:function(){alert(`Unable to load report.`)}})})})}),window.$=window.jQuery=i,document.addEventListener(`DOMContentLoaded`,function(){let e=document.getElementById(`fullscreenBtn`);e&&e.addEventListener(`click`,function(){document.fullscreenElement?document.exitFullscreen():document.documentElement.requestFullscreen()})});