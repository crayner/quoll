(window.webpackJsonp=window.webpackJsonp||[]).push([["idleTimeout"],{"7K2e":function(e,t,n){"use strict";function r(e){return void 0===e||(""===e||(null===e||(e===[]||e==={})))}n.d(t,"a",(function(){return r}))},HNiu:function(e,t,n){"use strict";n.r(t);var r=n("q1tI"),i=n.n(r),o=n("i8i4"),u=(n("pNMO"),n("4Brf"),n("0oug"),n("4mDm"),n("brp2"),n("DQNa"),n("wLYn"),n("uL8W"),n("eoL8"),n("NBAS"),n("ExoC"),n("07d7"),n("SuFq"),n("JfAA"),n("PKPk"),n("3bBZ"),n("R5XZ"),n("17x9")),c=n.n(u);function a(e){return(a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function l(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function s(e){return(s=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function f(e,t){return(f=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function d(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function p(e,t){return!t||"object"!=typeof t&&"function"!=typeof t?d(e):t}var h="object"===("undefined"==typeof window||"undefined"==typeof window?"undefined":a(window)),v=h?document:{},m=["mousemove","keydown","wheel","DOMMouseScroll","mousewheel","mousedown","touchstart","touchmove","MSPointerDown","MSPointerMove","visibilitychange"];function b(e,t){var n;return function(){for(var r=arguments.length,i=new Array(r),o=0;o<r;o++)i[o]=arguments[o];n&&clearTimeout(n),n=setTimeout((function(){e.apply(void 0,i),n=null}),t)}}function y(e,t){var n=0;return function(){var r=(new Date).getTime();if(!(r-n<t))return n=r,e.apply(void 0,arguments)}}var g=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&f(e,t)}(o,r.Component);var t,n,i=function(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=s(e);if(t){var i=s(this).constructor;n=Reflect.construct(r,arguments,i)}else n=r.apply(this,arguments);return p(this,n)}}(o);function o(e){var t;if(function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,o),(t=i.call(this,e)).state={idle:!1,oldDate:+new Date,lastActive:+new Date,remaining:null,pageX:null,pageY:null},t.tId=null,t.eventsBound=!1,e.debounce>0&&e.throttle>0)throw new Error("onAction can either be throttled or debounced (not both)");return e.debounce>0?t._onAction=b(e.onAction,e.debounce):e.throttle>0?t._onAction=y(e.onAction,e.throttle):e.onAction?t._onAction=e.onAction:t._onAction=function(){},e.eventsThrottle>0?t._handleEvent=y(t._handleEvent.bind(d(t)),e.eventsThrottle):t._handleEvent=t._handleEvent.bind(d(t)),e.startOnMount||(t.state.idle=!0),t._toggleIdleState=t._toggleIdleState.bind(d(t)),t.reset=t.reset.bind(d(t)),t.pause=t.pause.bind(d(t)),t.resume=t.resume.bind(d(t)),t.getRemainingTime=t.getRemainingTime.bind(d(t)),t.getElapsedTime=t.getElapsedTime.bind(d(t)),t.getLastActiveTime=t.getLastActiveTime.bind(d(t)),t.isIdle=t.isIdle.bind(d(t)),t}return t=o,(n=[{key:"componentDidMount",value:function(){this._bindEvents(),this.props.startOnMount&&this.reset()}},{key:"componentDidUpdate",value:function(e){e.debounce!==this.props.debounce&&(this._onAction=b(this._onAction,this.props.debounce)),e.throttle!==this.props.throttle&&(this._onAction=y(this._onAction,this.props.throttle)),e.eventsThrottle!==this.props.eventsThrottle&&(this._handleEvent=y(this._handleEvent,this.props.eventsThrottle))}},{key:"componentWillUnmount",value:function(){clearTimeout(this.tId),this._unbindEvents(!0)}},{key:"render",value:function(){return this.props.children||null}},{key:"_bindEvents",value:function(){var e=this;if(h){var t=this.props,n=t.element,r=t.events,i=t.passive,o=t.capture;this.eventsBound||(r.forEach((function(t){n.addEventListener(t,e._handleEvent,{capture:o,passive:i})})),this.eventsBound=!0)}}},{key:"_unbindEvents",value:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]&&arguments[0];if(h){var n=this.props,r=n.element,i=n.events,o=n.passive,u=n.capture;(this.eventsBound||t)&&(i.forEach((function(t){r.removeEventListener(t,e._handleEvent,{capture:u,passive:o})})),this.eventsBound=!1)}}},{key:"_toggleIdleState",value:function(e){var t=this;this.setState((function(e){return{idle:!e.idle}}),(function(){var n=t.props,r=n.onActive,i=n.onIdle,o=n.stopOnIdle;t.state.idle?(o&&(clearTimeout(t.tId),t.tId=null,t._unbindEvents()),i(e)):o||(t._bindEvents(),r(e))}))}},{key:"_handleEvent",value:function(e){var t=this.state,n=t.remaining,r=t.pageX,i=t.pageY,o=t.idle,u=this.props,c=u.timeout,a=u.stopOnIdle;if(this._onAction(e),!n){if("mousemove"===e.type){if(e.pageX===r&&e.pageY===i)return;if(void 0===e.pageX&&void 0===e.pageY)return;if(this.getElapsedTime()<200)return}clearTimeout(this.tId),this.tId=null;var l=+new Date-this.getLastActiveTime();(o&&!a||!o&&l>c)&&this._toggleIdleState(e),this.setState({lastActive:+new Date,pageX:e.pageX,pageY:e.pageY}),o&&a||(this.tId=setTimeout(this._toggleIdleState,c))}}},{key:"reset",value:function(){clearTimeout(this.tId),this.tId=null,this._bindEvents(),this.setState({idle:!1,oldDate:+new Date,lastActive:+new Date,remaining:null});var e=this.props.timeout;this.tId=setTimeout(this._toggleIdleState,e)}},{key:"pause",value:function(){null===this.state.remaining&&(this._unbindEvents(),clearTimeout(this.tId),this.tId=null,this.setState({remaining:this.getRemainingTime()}))}},{key:"resume",value:function(){var e=this.state,t=e.remaining,n=e.idle;null!==t&&(this._bindEvents(),n||(this.tId=setTimeout(this._toggleIdleState,t),this.setState({remaining:null,lastActive:+new Date})))}},{key:"getRemainingTime",value:function(){var e=this.state,t=e.remaining,n=e.lastActive,r=this.props.timeout;if(null!==t)return t<0?0:t;var i=r-(+new Date-n);return i<0?0:i}},{key:"getElapsedTime",value:function(){var e=this.state.oldDate;return+new Date-e}},{key:"getLastActiveTime",value:function(){return this.state.lastActive}},{key:"isIdle",value:function(){return this.state.idle}}])&&l(t.prototype,n),o}();function _(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},t=e.timeout,n=void 0===t?12e5:t,i=e.element,o=void 0===i?v:i,u=e.events,c=void 0===u?m:u,a=e.onIdle,l=void 0===a?function(){}:a,s=e.onActive,f=void 0===s?function(){}:s,d=e.onAction,p=void 0===d?function(){}:d,g=e.debounce,_=void 0===g?0:g,O=e.throttle,w=void 0===O?0:O,T=e.eventsThrottle,j=void 0===T?200:T,k=e.startOnMount,E=void 0===k||k,A=e.stopOnIdle,I=void 0!==A&&A,S=e.capture,D=void 0===S||S,R=e.passive,P=void 0===R||R,N=Object(r.useRef)(!1),L=Object(r.useRef)(!0),x=Object(r.useRef)(+new Date),M=Object(r.useRef)(+new Date),B=Object(r.useRef)(null),C=Object(r.useRef)(null),X=Object(r.useRef)(null),Y=Object(r.useRef)(null),J=Object(r.useRef)(l),K=Object(r.useRef)(f),q=Object(r.useRef)(p),U=function(e){var t=!L.current;L.current=t,t?(I&&(clearTimeout(Y.current),Y.current=null,G()),J.current(e)):I||(z(),K.current(e))},Z=function(e){if(q.current(e),!B.current){if("mousemove"===e.type){if(e.pageX===C&&e.pageY===X)return;if(void 0===e.pageX&&void 0===e.pageY)return;if(H()<200)return}clearTimeout(Y.current),Y.current=null;var t=+new Date-W();(L.current&&!I||!L.current&&t>n)&&U(e),M.current=+new Date,C.current=e.pageX,X.current=e.pageY,L.current&&I||(Y.current=setTimeout(U,n))}},z=function(){h&&(N.current||(c.forEach((function(e){o.addEventListener(e,Z,{capture:D,passive:P})})),N.current=!0))},G=function(){var e=arguments.length>0&&void 0!==arguments[0]&&arguments[0];h&&(N.current||e)&&(c.forEach((function(e){o.removeEventListener(e,Z,{capture:D,passive:P})})),N.current=!1)},Q=function(){if(null!==B.current)return B.current<0?0:B.current;var e=n-(+new Date-M.current);return e<0?0:e},H=function(){return+new Date-x.current},W=function(){return M.current},F=function(){return L.current},V=function(){clearTimeout(Y.current),Y.current=null,z(),L.current=!1,x.current=+new Date,M.current=+new Date,B.current=null,Y.current=setTimeout(U,n)},$=function(){null===B.current&&(G(),clearTimeout(Y.current),Y.current=null,B.current=Q())},ee=function(){null!==B.current&&(z(),L.current||(Y.current=setTimeout(U,B.current),B.current=null,M.current=+new Date))};return Object(r.useEffect)((function(){if(_>0&&w>0)throw new Error("onAction can either be throttled or debounced (not both)");return j>0&&(Z=y(Z,j)),z(),E&&V(),function(){clearTimeout(Y.current),G(!0)}}),[]),Object(r.useEffect)((function(){J.current=l}),[l]),Object(r.useEffect)((function(){K.current=f}),[f]),Object(r.useEffect)((function(){q.current=_>0?b(p,_):w>0?y(p,w):p}),[p]),{isIdle:F,pause:$,reset:V,resume:ee,getLastActiveTime:W,getElapsedTime:H,getRemainingTime:Q}}g.propTypes={timeout:c.a.number,events:c.a.arrayOf(c.a.string),onIdle:c.a.func,onActive:c.a.func,onAction:c.a.func,debounce:c.a.number,throttle:c.a.number,eventsThrottle:c.a.number,element:c.a.oneOfType([c.a.object,c.a.element]),startOnMount:c.a.bool,stopOnIdle:c.a.bool,passive:c.a.bool,capture:c.a.bool},g.defaultProps={timeout:12e5,element:v,events:m,onIdle:function(){},onActive:function(){},onAction:function(){},debounce:0,throttle:0,eventsThrottle:200,startOnMount:!0,stopOnIdle:!1,capture:!0,passive:!0},_.propTypes={timeout:c.a.number,events:c.a.arrayOf(c.a.string),onIdle:c.a.func,onActive:c.a.func,onAction:c.a.func,debounce:c.a.number,throttle:c.a.number,eventsThrottle:c.a.number,element:c.a.oneOfType([c.a.object,c.a.element]),startOnMount:c.a.bool,stopOnIdle:c.a.bool,passive:c.a.bool,capture:c.a.bool},_.defaultProps={timeout:12e5,element:v,events:m,onIdle:function(){},onActive:function(){},onAction:function(){},debounce:0,throttle:0,eventsThrottle:200,startOnMount:!0,stopOnIdle:!1,capture:!0,passive:!0};var O=g,w=n("ohO+");function T(e){return(T="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function j(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function k(e,t){return(k=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function E(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,r=S(e);if(t){var i=S(this).constructor;n=Reflect.construct(r,arguments,i)}else n=r.apply(this,arguments);return A(this,n)}}function A(e,t){return!t||"object"!==T(t)&&"function"!=typeof t?I(e):t}function I(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function S(e){return(S=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var D=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&k(e,t)}(u,e);var t,n,r,o=E(u);function u(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,u),(t=o.call(this,e)).idleTimer=null,t.state={timeout:1e3*e.duration,remaining:null,lastActive:null,elapsed:null,display:!1},t.onActive=t._onActive.bind(I(t)),t.onIdle=t._onIdle.bind(I(t)),t.reset=t._reset.bind(I(t)),t.changeTimeout=t._changeTimeout.bind(I(t)),t.route=e.route,t}return t=u,(n=[{key:"componentDidMount",value:function(){var e=this;null!==this.idleTimer&&(this.setState({remaining:this.idleTimer.getRemainingTime(),lastActive:this.idleTimer.getLastActiveTime(),elapsed:this.idleTimer.getElapsedTime()}),setInterval((function(){e.setState({remaining:e.idleTimer.getRemainingTime(),lastActive:e.idleTimer.getLastActiveTime(),elapsed:e.idleTimer.getElapsedTime(),display:!(e.state.timeout-e.idleTimer.getElapsedTime()>3e4)}),e.wasLastActive!==e.idleTimer.getLastActiveTime()&&e.refreshPage(),e.wasLastActive=e.idleTimer.getLastActiveTime(),e.state.elapsed>e.state.timeout&&e.logout()}),1e3))}},{key:"render",value:function(){var e=this;return i.a.createElement("section",null,i.a.createElement(O,{ref:function(t){e.idleTimer=t},onActive:this.onActive,onIdle:this.onIdle,timeout:this.state.timeout,throttle:50,startOnLoad:!0}),this.state.display?i.a.createElement("div",{className:"absolute w-full top-0 left-0 min-h-full bg-gray-900",style:{zIndex:99999}},i.a.createElement("div",{className:"absolute w-full top-0 left-0 min-h-screen"},i.a.createElement("div",{className:"bg-orange-700 absolute border-color-white border-4 rounded-lg h-40 w-64",style:{transform:"translate(-50%,-35%)",top:"35%",left:"50%"}},i.a.createElement("div",{className:"w-full p-2"},i.a.createElement("img",{className:"float-right",src:"/build/static/kookaburra.png",height:75}),i.a.createElement("h3",{className:"absolute top-0 left-0 pt-10 px-2 m-0 ml-1 border-color-white border-b-2"},"Kookaburra"),i.a.createElement("span",{className:"float-left"},this.props.trans_sessionExpire))))):"")}},{key:"refreshPage",value:function(){this.state.elapsed>this.state.timeout&&this.logout(),this.reset()}},{key:"_onActive",value:function(){this.refreshPage()}},{key:"_onIdle",value:function(){}},{key:"_changeTimeout",value:function(){this.setState({timeout:this.refs.timeoutInput.state.value()})}},{key:"_reset",value:function(){this.idleTimer.reset(),this.setState({display:!1,lastActive:Date.now()})}},{key:"logout",value:function(){window.localStorage.setItem("logged_in","false"),Object(w.a)(this.route,{method:"GET"},!1)}}])&&j(t.prototype,n),r&&j(t,r),u}(r.Component),R=n("koSi"),P=document.getElementById("idleTimeout");window.localStorage.setItem("logged_in","true"),window.addEventListener("storage",(function(e){"logged_in"===e.key&&Object(R.p)("/home/timeout/","_self")}),"false"),Object(o.render)(i.a.createElement(D,window.IDLETIMEOUT_PROPS),P)},J5mc:function(e,t,n){"use strict";n.d(t,"a",(function(){return o}));n("yq1k"),n("zKZe"),n("07d7"),n("5s+n"),n("rB9j"),n("JTJg"),n("UxlC");var r=n("koSi");function i(){return(i=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e}).apply(this,arguments)}function o(e,t,n){var r={};t&&t.headers&&(r=t.headers,delete t.headers),r=i({},r,{"Content-Type":"application/json; charset=utf-8"}),null===n&&(n="en_GB"),!1!==n&&""!==e||(n="");var o=window.location.protocol+"//"+window.location.hostname+"/";return"/"===e[0]&&(e=e.substring(1)),"http"===e.substring(0,4)&&(o=""),fetch(o+n+e,i({},t,{credentials:"same-origin",headers:r})).then(u).then((function(e){return e.text().then((function(e){return(e=e.replace("<?php","")).includes("window.Sfdump")||e.includes("<?php")?(console.error(e),[]):"string"==typeof e?JSON.parse(e):""}))}))}function u(e){if(e.status>=200&&e.status<400)return e;if(403===e.status){var t=window.location.protocol+"//"+window.location.hostname,n=btoa(e.url);Object(r.p)(t+"/route/"+n+"/error/","_self")}var i=new Error(e.statusText);throw i.response=e,i}},brp2:function(e,t,n){n("I+eb")({target:"Date",stat:!0},{now:function(){return(new Date).getTime()}})},koSi:function(e,t,n){"use strict";n.d(t,"u",(function(){return s})),n.d(t,"j",(function(){return f})),n.d(t,"w",(function(){return d})),n.d(t,"h",(function(){return p})),n.d(t,"p",(function(){return h})),n.d(t,"b",(function(){return v})),n.d(t,"k",(function(){return m})),n.d(t,"l",(function(){return b})),n.d(t,"o",(function(){return y})),n.d(t,"q",(function(){return g})),n.d(t,"r",(function(){return _})),n.d(t,"g",(function(){return O})),n.d(t,"c",(function(){return w})),n.d(t,"e",(function(){return T})),n.d(t,"v",(function(){return j})),n.d(t,"n",(function(){return k})),n.d(t,"i",(function(){return E})),n.d(t,"a",(function(){return A})),n.d(t,"m",(function(){return I})),n.d(t,"d",(function(){return S})),n.d(t,"s",(function(){return D})),n.d(t,"f",(function(){return R})),n.d(t,"t",(function(){return P}));n("pNMO"),n("4Brf"),n("0oug"),n("yXV3"),n("4mDm"),n("2B1R"),n("+2oP"),n("pDQq"),n("DQNa"),n("sMBO"),n("zKZe"),n("tkto"),n("07d7"),n("rB9j"),n("JfAA"),n("PKPk"),n("UxlC"),n("3bBZ"),n("R5XZ");var r=n("q1tI"),i=n.n(r),o=n("7K2e"),u=n("ohO+"),c=n("J5mc");function a(){return(a=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e}).apply(this,arguments)}function l(e){return(l="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function s(e,t){return void 0===e.children||Object.keys(e.children).map((function(n){var r=e.children[n];s(r,t),Object.keys(r.errors).length>0&&!1!==r.panel&&(void 0===t[r.panel]&&(t[r.panel]={}),t[r.panel].problem=!0)})),t}function f(e,t,n){var r=[];return Object(o.a)(e)||r.push(i.a.createElement("a",{key:"remove",className:"close-button gray ml-3",onClick:function(t){return n.handleAddClick(e.url,"_self")},title:e.prompt},i.a.createElement("span",{className:"fas fa-reply fa-fw text-gray-800 hover:text-indigo-500"}))),Object(o.a)(t)||r.push(i.a.createElement("a",{key:"add",className:"close-button gray ml-3",onClick:function(e){return n.handleAddClick(t.url,"_self")},title:t.prompt},i.a.createElement("span",{className:"fas fa-plus-circle fa-fw text-gray-800 hover:text-indigo-500"}))),r}function d(e,t){return Object(o.a)(e[t])?(console.error("Unable to translate: "+t),t):e[t]}function p(e){var t=e.value,n="/resource/"+btoa(t)+"/"+this.actionRoute+"/download/";void 0!==e.delete_security&&!1!==e.delete_security&&(n="/resource/"+btoa(e.value)+"/"+e.delete_security+"/download/"),Object(u.a)(n,{target:"_blank"},!1)}function h(e,t){void 0===t&&(t="_blank");var n="";"object"===l(e)&&(e=(n=e).url,t=n.target,n=void 0!==n.options?n.options:""),window.open(e,t,n)}function v(e,t){var n={};return t&&(n=s(e[Object.keys(e)[0]],n)),{forms:e,panelErrors:n}}function m(e,t,n){return void 0===n&&(n={},Object.keys(e).map((function(t){var r=e[t];n[r.name]=t}))),e[b(n,t)]}function b(e,t){return e[t.full_name.substring(0,t.full_name.indexOf("["))]}function y(e,t,n){return e[t]=a({},n),a({},e)}function g(e,t){return"object"===l(e.children)?Object.keys(e.children).map((function(n){g(e.children[n],t).id===t.id&&(e.children[n]=t)})):"array"==typeof e.children&&e.children.map((function(n,r){(n=g(n,t)).id===t.id&&(e.children[r]=t)})),e.id===t.id&&(e=t),e}function _(e,t){return"object"===l((e=a({},e)).children)&&(e.children=a({},e.children),Object.keys(e.children).map((function(n){var r=_(e.children[n],t);e.children[n]=r}))),e.name=e.name.replace("__name__",t),e.id=e.id.replace("__name__",t),e.full_name=e.full_name.replace("__name__",t),"string"==typeof e.chained_child&&(e.chained_child=e.chained_child.replace("__name__",t)),"string"==typeof e.label&&(e.label=e.label.replace("__name__",t)),e}function O(e,t){return"object"===l(e.children)&&Object.keys(e.children).map((function(n){O(e.children[n],t).id===t.id&&delete e.children[n]})),"array"==typeof e.children&&e.children.map((function(n,r){(n=O(n,t)).id===t.id&&e.children.splice(r,1)})),e}function w(e,t,n){var r=a({},e);if("object"===l(r.children)&&Object.keys(r.children).length>0){var i=a({},r.children);return Object.keys(i).map((function(e){var r=a({},i[e]);r.id===t.id?(r.value=n,Object.assign(i[e],a({},r))):Object.assign(i[e],w(a({},r),t,n))})),Object.assign(r.children,a({},i)),a({},r)}return a({},r)}function T(e,t){return Object.keys(e).map((function(n){var r=e[n];e[n]=function e(t,n){void 0!==t.children&&Object.keys(t.children).map((function(r){var i=t.children[r];e(i,n),n=j(i,a({},i.visible_keys))}));return t.visible_keys=n,a({},t)}(r,t),t=a({},r.visible_keys)})),e.visible_keys=t,a({},e)}function j(e,t){if("string"==typeof e.visible_by_choice){var n=!1;e.value===e.visible_by_choice&&(n=!0),"toggle"!==e.type||"Y"!==e.value&&"1"!==e.value||(n=!0),t[e.id+"_"+e.visible_by_choice]=n}else"object"===l(e.visible_by_choice)?e.visible_by_choice.map((function(n){var r=!1;e.value===n&&(r=!0),t[e.id+"_"+n]=r})):"boolean"==typeof e.visible_by_choice&&!0===e.visible_by_choice&&Object.keys(e.choices).map((function(n){var r=e.choices[n],i=!1;e.value===r.value&&(i=!0),t[e.id+"_"+r.value]=i}));return t}function k(e){var t=!1;return Object.keys(e).map((function(n){e[n]&&(t=!0)})),t}function E(e,t,n){return"string"==typeof n.id&&n.id===t?n:"object"===l(e.children)?(Object.keys(e.children).map((function(r){var i=e.children[r];i.id===t&&(n=i),n=E(e.children[r],t,n)})),n):"array"==typeof e.children?(e.children.map((function(e,r){e.id===t&&(n=e),n=E(e,t,n)})),n):n}function A(e,t){if("expanded_choice"===t.type)return t.value;if("date"===t.type){if(void 0===t.value)t.value=null;else if(void 0!==t.value.date)return t.value.date.toString().slice(0,10);return t.value}return"object"===l(t.children)&&Object.keys(t.children).length>0?(Object.keys(t.children).map((function(n){var r=t.children[n];e[r.name]=A({},r)})),e):"array"==typeof t.children&&t.children.length>0?(t.children.map((function(t){e[t.name]=A({},t)})),e):t.value}function I(e,t){null!==e&&e.map((function(e){setTimeout(function e(t,n){Object(c.a)(t.route,{},!1).then((function(r){"success"===r.status&&n(t,r.content),t.timer>0&&setTimeout(e(t,n),t.timer)}))}(e,t),50)}))}function S(e,t){return Object.keys(e).map((function(n){e[n]=function e(t,n,r){void 0!==t.children&&Object.keys(t.children).length>0&&Object.keys(t.children).map((function(i){t.children[i]=e(t.children[i],n,r)}));"choice"===t.type&&void 0!==t.chained_child&&null!==t.chained_child&&(n=D(t,n,r));return a({},t)}(e[n],e,t)})),a({},e)}function D(e,t,n){t=a({},t);var r=a({},m(t,e,n)),i=b(n,e),o=E(r,e.chained_child,{}),u=e.value,c=e.chained_values[u];return("object"!==l(c)||0===Object.keys(c).length&&null!==u)&&(c=e.chained_values[""+u]),"object"!==l(c)||0===Object.keys(c).length?(o.disabled=!0,o.choices={}):(o.disabled=!1,o.choices=a({},c),0===Object.keys(o.choices).length&&(o.disabled=!0)),t=a({},y(t,i,r)),a({},t)}function R(e,t){var n=e[t];return void 0===n.attr&&(n.attr={}),n.attr.className="",e[t]=a({},n),a({},e)}function P(e,t){return void 0===e.attr&&(e.attr={}),"string"==typeof e.attr.className?e.attr.className=e.attr.className+" "+t:e.attr.className=t,a({},e)}},"ohO+":function(e,t,n){"use strict";function r(e,t,n){var r="_self";t&&"string"==typeof t.target&&(r=t.target);var i="";t&&"string"==typeof t.specs&&(i=t.specs),"boolean"==typeof n&&!1===n&&(n=""),null==n&&(n="en_GB"),""!==n&&(n="/"+n),window.open(window.location.protocol+"//"+window.location.hostname+n+e,r,i)}n.d(t,"a",(function(){return r}))}},[["HNiu","runtime",0,1]]]);