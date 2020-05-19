(window.webpackJsonp=window.webpackJsonp||[]).push([["idleTimeout"],{"7K2e":function(e,t,n){"use strict";function i(e){return void 0===e||(""===e||(null===e||(e===[]||e==={})))}n.d(t,"a",(function(){return i}))},HNiu:function(e,t,n){"use strict";n.r(t);var i=n("q1tI"),o=n.n(i),r=n("i8i4"),a=(n("pNMO"),n("4Brf"),n("0oug"),n("4mDm"),n("brp2"),n("DQNa"),n("wLYn"),n("uL8W"),n("eoL8"),n("NBAS"),n("ExoC"),n("07d7"),n("SuFq"),n("JfAA"),n("PKPk"),n("3bBZ"),n("R5XZ"),n("17x9")),l=n.n(a);function c(e){return(c="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function u(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}function s(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function d(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&function(e,t){(Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}(e,t)}function f(e){return(f=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function h(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function p(e,t){var n;return function(){for(var i=arguments.length,o=new Array(i),r=0;r<i;r++)o[r]=arguments[r];n&&clearTimeout(n),n=setTimeout((function(){e.apply(void 0,o),n=null}),t)}}function b(e,t){var n=0;return function(){var i=(new Date).getTime();if(!(i-n<t))return n=i,e.apply(void 0,arguments)}}var m="object"===("undefined"==typeof window||"undefined"==typeof window?"undefined":c(window)),v=m?document:{},y=function(e){function t(e){var n;if(function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),n=function(e,t){return!t||"object"!=typeof t&&"function"!=typeof t?h(e):t}(this,f(t).call(this,e)),s(h(n),"state",{idle:!1,oldDate:+new Date,lastActive:+new Date,remaining:null,pageX:null,pageY:null}),s(h(n),"tId",null),s(h(n),"_handleEvent",(function(e){var t=n.state,i=t.remaining,o=t.pageX,r=t.pageY,a=t.idle,l=n.props,c=l.timeout,u=l.onAction,s=l.debounce,d=l.throttle,f=l.stopOnIdle;if(s>0?n.debouncedAction(e):d>0?n.throttledAction(e):u(e),!i){if("mousemove"===e.type){if(e.pageX===o&&e.pageY===r)return;if(void 0===e.pageX&&void 0===e.pageY)return;if(n.getElapsedTime()<200)return}clearTimeout(n.tId),n.tId=null;var h=new Date-n.getLastActiveTime();(a&&!f||!a&&h>c)&&n.toggleIdleState(e),n.setState({lastActive:+new Date,pageX:e.pageX,pageY:e.pageY}),a&&f||(n.tId=setTimeout(n.toggleIdleState,c))}})),e.debounce>0&&e.throttle>0)throw new Error("onAction can either be throttled or debounced (not both)");return e.debounce>0&&(n.debouncedAction=p(e.onAction,e.debounce)),e.throttle>0&&(n.throttledAction=b(e.onAction,e.throttle)),e.startOnMount||(n.state.idle=!0),n.toggleIdleState=n._toggleIdleState.bind(h(n)),n.reset=n.reset.bind(h(n)),n.pause=n.pause.bind(h(n)),n.resume=n.resume.bind(h(n)),n.getRemainingTime=n.getRemainingTime.bind(h(n)),n.getElapsedTime=n.getElapsedTime.bind(h(n)),n.getLastActiveTime=n.getLastActiveTime.bind(h(n)),n.isIdle=n._isIdle.bind(h(n)),n}return d(t,e),function(e,t,n){t&&u(e.prototype,t),n&&u(e,n)}(t,[{key:"componentDidMount",value:function(){this._bindEvents(),this.props.startOnMount&&this.reset()}},{key:"componentDidUpdate",value:function(e){e.debounce!==this.props.debounce&&(this.debouncedAction=p(this.props.onAction,this.props.debounce)),e.throttle!==this.props.throttle&&(this.throttledAction=b(this.props.onAction,this.props.throttle))}},{key:"componentWillUnmount",value:function(){clearTimeout(this.tId),this._unbindEvents(!0)}},{key:"render",value:function(){return this.props.children||null}},{key:"_bindEvents",value:function(){var e=this;if(m){var t=this.state.eventsBound,n=this.props,i=n.element,o=n.events,r=n.passive,a=n.capture;t||(o.forEach((function(t){i.addEventListener(t,e._handleEvent,{capture:a,passive:r})})),this.setState({eventsBound:!0}))}}},{key:"_unbindEvents",value:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]&&arguments[0];if(m){var n=this.props,i=n.element,o=n.events,r=n.passive,a=n.capture;(this.state.eventsBound||t)&&(o.forEach((function(t){i.removeEventListener(t,e._handleEvent,{capture:a,passive:r})})),this.setState({eventsBound:!1}))}}},{key:"_toggleIdleState",value:function(e){var t=this;this.setState((function(e){return{idle:!e.idle}}),(function(){var n=t.props,i=n.onActive,o=n.onIdle,r=n.stopOnIdle;t.state.idle?(r&&(clearTimeout(t.tId),t.tId=null,t._unbindEvents()),o(e)):r||(t._bindEvents(),i(e))}))}},{key:"reset",value:function(){clearTimeout(this.tId),this.tId=null,this._bindEvents(),this.setState({idle:!1,oldDate:+new Date,lastActive:+new Date,remaining:null});var e=this.props.timeout;this.tId=setTimeout(this.toggleIdleState,e)}},{key:"pause",value:function(){null===this.state.remaining&&(this._unbindEvents(),clearTimeout(this.tId),this.tId=null,this.setState({remaining:this.getRemainingTime()}))}},{key:"resume",value:function(){var e=this.state,t=e.remaining,n=e.idle;null!==t&&(this._bindEvents(),n||(this.setState({remaining:null,lastActive:+new Date}),this.tId=setTimeout(this.toggleIdleState,t)))}},{key:"getRemainingTime",value:function(){var e=this.state,t=e.remaining,n=e.lastActive,i=this.props.timeout;if(null!==t)return t<0?0:t;var o=i-(+new Date-n);return o<0?0:o}},{key:"getElapsedTime",value:function(){var e=this.state.oldDate;return+new Date-e}},{key:"getLastActiveTime",value:function(){return this.state.lastActive}},{key:"_isIdle",value:function(){return this.state.idle}}]),t}(i.Component);s(y,"propTypes",{timeout:l.a.number,events:l.a.arrayOf(l.a.string),onIdle:l.a.func,onActive:l.a.func,onAction:l.a.func,debounce:l.a.number,throttle:l.a.number,element:l.a.oneOfType([l.a.object,l.a.element]),startOnMount:l.a.bool,stopOnIdle:l.a.bool,passive:l.a.bool,capture:l.a.bool}),s(y,"defaultProps",{timeout:12e5,element:v,events:["mousemove","keydown","wheel","DOMMouseScroll","mouseWheel","mousedown","touchstart","touchmove","MSPointerDown","MSPointerMove","visibilitychange"],onIdle:function(){},onActive:function(){},onAction:function(){},debounce:0,throttle:0,startOnMount:!0,stopOnIdle:!1,capture:!0,passive:!0});var g=y,_=n("ohO+");function w(e){return(w="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function O(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}function j(e,t){return(j=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function k(e,t){return!t||"object"!==w(t)&&"function"!=typeof t?T(e):t}function T(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function E(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}function A(e){return(A=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var S=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&j(e,t)}(l,e);var t,n,i,r,a=(t=l,function(){var e,n=A(t);if(E()){var i=A(this).constructor;e=Reflect.construct(n,arguments,i)}else e=n.apply(this,arguments);return k(this,e)});function l(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,l),(t=a.call(this,e)).idleTimer=null,t.state={timeout:1e3*e.duration,remaining:null,lastActive:null,elapsed:null,display:!1},t.onActive=t._onActive.bind(T(t)),t.onIdle=t._onIdle.bind(T(t)),t.reset=t._reset.bind(T(t)),t.changeTimeout=t._changeTimeout.bind(T(t)),t.route=e.route,t}return n=l,(i=[{key:"componentDidMount",value:function(){var e=this;null!==this.idleTimer&&(this.setState({remaining:this.idleTimer.getRemainingTime(),lastActive:this.idleTimer.getLastActiveTime(),elapsed:this.idleTimer.getElapsedTime()}),setInterval((function(){e.setState({remaining:e.idleTimer.getRemainingTime(),lastActive:e.idleTimer.getLastActiveTime(),elapsed:e.idleTimer.getElapsedTime(),display:!(e.state.timeout-e.idleTimer.getElapsedTime()>3e4)}),e.wasLastActive!==e.idleTimer.getLastActiveTime()&&e.refreshPage(),e.wasLastActive=e.idleTimer.getLastActiveTime(),e.state.elapsed>e.state.timeout&&e.logout()}),1e3))}},{key:"render",value:function(){var e=this;return o.a.createElement("section",null,o.a.createElement(g,{ref:function(t){e.idleTimer=t},onActive:this.onActive,onIdle:this.onIdle,timeout:this.state.timeout,throttle:50,startOnLoad:!0}),this.state.display?o.a.createElement("div",{className:"absolute w-full top-0 left-0 min-h-full bg-gray-900",style:{zIndex:99999}},o.a.createElement("div",{className:"absolute w-full top-0 left-0 min-h-screen"},o.a.createElement("div",{className:"bg-orange-700 absolute border-color-white border-4 rounded-lg h-40 w-64",style:{transform:"translate(-50%,-35%)",top:"35%",left:"50%"}},o.a.createElement("div",{className:"w-full p-2"},o.a.createElement("img",{className:"float-right",src:"/build/static/kookaburra.png",height:75}),o.a.createElement("h3",{className:"absolute top-0 left-0 pt-10 px-2 m-0 ml-1 border-color-white border-b-2"},"Kookaburra"),o.a.createElement("span",{className:"float-left"},this.props.trans_sessionExpire))))):"")}},{key:"refreshPage",value:function(){this.state.elapsed>this.state.timeout&&this.logout(),this.reset()}},{key:"_onActive",value:function(){this.refreshPage()}},{key:"_onIdle",value:function(){}},{key:"_changeTimeout",value:function(){this.setState({timeout:this.refs.timeoutInput.state.value()})}},{key:"_reset",value:function(){this.idleTimer.reset(),this.setState({display:!1,lastActive:Date.now()})}},{key:"logout",value:function(){window.localStorage.setItem("logged_in","false"),Object(_.a)(this.route,{method:"GET"},!1)}}])&&O(n.prototype,i),r&&O(n,r),l}(i.Component),I=n("koSi"),D=document.getElementById("idleTimeout");window.localStorage.setItem("logged_in","true"),window.addEventListener("storage",(function(e){"logged_in"===e.key&&Object(I.p)("/home/timeout/","_self")}),"false"),Object(r.render)(o.a.createElement(S,window.IDLETIMEOUT_PROPS),D)},J5mc:function(e,t,n){"use strict";n.d(t,"a",(function(){return r}));n("yq1k"),n("zKZe"),n("07d7"),n("5s+n"),n("rB9j"),n("JTJg"),n("UxlC");var i=n("koSi");function o(){return(o=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var i in n)Object.prototype.hasOwnProperty.call(n,i)&&(e[i]=n[i])}return e}).apply(this,arguments)}function r(e,t,n){var i={};t&&t.headers&&(i=t.headers,delete t.headers),i=o({},i,{"Content-Type":"application/json; charset=utf-8"}),null===n&&(n="en_GB"),!1!==n&&""!==e||(n="");var r=window.location.protocol+"//"+window.location.hostname+"/";return"/"===e[0]&&(e=e.substring(1)),"http"===e.substring(0,4)&&(r=""),fetch(r+n+e,o({},t,{credentials:"same-origin",headers:i})).then(a).then((function(e){return e.text().then((function(e){return(e=e.replace("<?php","")).includes("window.Sfdump")||e.includes("<?php")?(console.log(e),[]):"string"==typeof e?JSON.parse(e):""}))}))}function a(e){if(e.status>=200&&e.status<400)return e;if(403===e.status){var t=window.location.protocol+"//"+window.location.hostname,n=btoa(e.url);Object(i.p)(t+"/route/"+n+"/error/","_self")}var o=new Error(e.statusText);throw o.response=e,o}},brp2:function(e,t,n){n("I+eb")({target:"Date",stat:!0},{now:function(){return(new Date).getTime()}})},koSi:function(e,t,n){"use strict";n.d(t,"t",(function(){return s})),n.d(t,"j",(function(){return d})),n.d(t,"u",(function(){return f})),n.d(t,"h",(function(){return h})),n.d(t,"p",(function(){return p})),n.d(t,"b",(function(){return b})),n.d(t,"k",(function(){return m})),n.d(t,"l",(function(){return v})),n.d(t,"o",(function(){return y})),n.d(t,"q",(function(){return g})),n.d(t,"r",(function(){return _})),n.d(t,"g",(function(){return w})),n.d(t,"c",(function(){return O})),n.d(t,"e",(function(){return j})),n.d(t,"n",(function(){return k})),n.d(t,"i",(function(){return T})),n.d(t,"a",(function(){return E})),n.d(t,"m",(function(){return A})),n.d(t,"d",(function(){return S})),n.d(t,"s",(function(){return I})),n.d(t,"f",(function(){return D}));n("pNMO"),n("4Brf"),n("0oug"),n("yq1k"),n("yXV3"),n("4mDm"),n("2B1R"),n("+2oP"),n("pDQq"),n("DQNa"),n("sMBO"),n("zKZe"),n("tkto"),n("07d7"),n("4l63"),n("rB9j"),n("JfAA"),n("JTJg"),n("PKPk"),n("UxlC"),n("3bBZ"),n("R5XZ");var i=n("q1tI"),o=n.n(i),r=n("7K2e"),a=n("ohO+"),l=n("J5mc");function c(){return(c=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var i in n)Object.prototype.hasOwnProperty.call(n,i)&&(e[i]=n[i])}return e}).apply(this,arguments)}function u(e){return(u="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function s(e,t){return void 0===e.children||Object.keys(e.children).map((function(n){var i=e.children[n];s(i,t),Object.keys(i.errors).length>0&&!1!==i.panel&&(void 0===t[i.panel]&&(t[i.panel]={}),t[i.panel].problem=!0)})),t}function d(e,t,n){var i=[];return Object(r.a)(e)||i.push(o.a.createElement("a",{key:"remove",className:"close-button gray ml-3",onClick:function(t){return n.handleAddClick(e,"_self")},title:n.translate("Return")},o.a.createElement("span",{className:"fas fa-reply fa-fw text-gray-800 hover:text-indigo-500"}))),Object(r.a)(t)||i.push(o.a.createElement("a",{key:"add",className:"close-button gray ml-3",onClick:function(e){return n.handleAddClick(t,"_self")},title:n.translate("Add")},o.a.createElement("span",{className:"fas fa-plus-circle fa-fw text-gray-800 hover:text-indigo-500"}))),i}function f(e,t){return Object(r.a)(e[t])?(console.error("Unable to translate: "+t),t):e[t]}function h(e){var t=e.value,n="/resource/"+btoa(t)+"/"+this.actionRoute+"/download/";void 0!==e.delete_security&&!1!==e.delete_security&&(n="/resource/"+btoa(e.value)+"/"+e.delete_security+"/download/"),Object(a.a)(n,{target:"_blank"},!1)}function p(e,t){void 0===t&&(t="_blank");var n="";"object"===u(e)&&(e=(n=e).url,t=n.target,n=void 0!==n.options?n.options:""),window.open(e,t,n)}function b(e,t){var n={};return t&&(n=s(e[Object.keys(e)[0]],n)),{forms:e,panelErrors:n}}function m(e,t,n){return void 0===n&&(n={},Object.keys(e).map((function(t){var i=e[t];n[i.name]=t}))),e[v(n,t)]}function v(e,t){return e[t.full_name.substring(0,t.full_name.indexOf("["))]}function y(e,t,n){return e[t]=c({},n),c({},e)}function g(e,t){return"object"===u(e.children)?Object.keys(e.children).map((function(n){g(e.children[n],t).id===t.id&&(e.children[n]=t)})):"array"==typeof e.children&&e.children.map((function(n,i){(n=g(n,t)).id===t.id&&(e.children[i]=t)})),e.id===t.id&&(e=t),e}function _(e,t){return"object"===u((e=c({},e)).children)&&(e.children=c({},e.children),Object.keys(e.children).map((function(n){var i=_(e.children[n],t);e.children[n]=i}))),e.name=e.name.replace("__name__",t),e.id=e.id.replace("__name__",t),e.full_name=e.full_name.replace("__name__",t),"string"==typeof e.chained_child&&(e.chained_child=e.chained_child.replace("__name__",t)),"string"==typeof e.label&&(e.label=e.label.replace("__name__",t)),e}function w(e,t){return"object"===u(e.children)&&Object.keys(e.children).map((function(n){w(e.children[n],t).id===t.id&&delete e.children[n]})),"array"==typeof e.children&&e.children.map((function(n,i){(n=w(n,t)).id===t.id&&e.children.splice(i,1)})),e}function O(e,t,n){var i=c({},e);if("object"===u(i.children)&&Object.keys(i.children).length>0){var o=c({},i.children);return Object.keys(o).map((function(e){var i=c({},o[e]);i.id===t.id?(i.value=n,Object.assign(o[e],c({},i))):Object.assign(o[e],O(c({},i),t,n))})),Object.assign(i.children,c({},o)),c({},i)}return c({},i)}function j(e){return Object.keys(e).map((function(t){var n=e[t];e[t]=function e(t,n){void 0!==t.children&&Object.keys(t.children).map((function(i){var o=t.children[i];e(o,n)}));return Object.keys(n).map((function(e){var i=n[e];"string"==typeof t.row_class&&t.row_class.includes(e)&&(i||"hidden"===t.row_style||(t.row_style_visible=t.visible,t.row_style="hidden"))})),c({},t)}(n,function e(t,n){void 0!==t.children&&Object.keys(t.children).map((function(i){var o=t.children[i];if(void 0!==o.visibleByClass&&!1!==o.visibleByClass){var r=!1;o.value===o.visibleWhen&&(r=!0),n[o.visibleByClass]=r}n=e(o,n)}));return n}(n,{}))})),c({},e)}function k(e){var t=!1;return Object.keys(e).map((function(n){e[n]&&(t=!0)})),t}function T(e,t,n){return"string"==typeof n.id&&n.id===t?n:"object"===u(e.children)?(Object.keys(e.children).map((function(i){var o=e.children[i];o.id===t&&(n=o),n=T(e.children[i],t,n)})),n):"array"==typeof e.children?(e.children.map((function(e,i){e.id===t&&(n=e),n=T(e,t,n)})),n):n}function E(e,t){if("expanded_choice"===t.type)return t.value;if("date"===t.type){if(void 0===t.value)t.value=null;else if(console.log(t.value),void 0!==t.value.date)return t.value.date.toString().slice(0,10);return t.value}return"object"===u(t.children)&&Object.keys(t.children).length>0?(Object.keys(t.children).map((function(n){var i=t.children[n];e[i.name]=E({},i)})),e):"array"==typeof t.children&&t.children.length>0?(t.children.map((function(t){e[t.name]=E({},t)})),e):t.value}function A(e,t){null!==e&&e.map((function(e){setTimeout(function e(t,n){Object(l.a)(t.route,{},!1).then((function(i){"success"===i.status&&n(t,i.content),t.timer>0&&setTimeout(e(t,n),t.timer)}))}(e,t),50)}))}function S(e,t){return Object.keys(e).map((function(n){e[n]=function e(t,n,i){void 0!==t.children&&Object.keys(t.children).length>0&&Object.keys(t.children).map((function(o){t.children[o]=e(t.children[o],n,i)}));"choice"===t.type&&void 0!==t.chained_child&&null!==t.chained_child&&(n=I(t,n,i));return c({},t)}(e[n],e,t)})),c({},e)}function I(e,t,n){t=c({},t);var i=c({},m(t,e,n)),o=v(n,e),r=T(i,e.chained_child,{}),a=e.value,l=e.chained_values[a];return("object"!==u(l)||0===Object.keys(l).length&&parseInt(a)>0)&&(l=e.chained_values[""+a]),"object"!==u(l)||0===Object.keys(l).length?(r.disabled=!0,r.choices={}):(r.disabled=!1,r.choices=c({},l),0===Object.keys(r.choices).length&&(r.disabled=!0)),t=c({},y(t,o,i)),c({},t)}function D(e){return Object.keys(e).map((function(t){Object.assign(e[t],function e(t,n){"object"===u(t.children)&&Object.keys(t.children).length>0&&Object.keys(t.children).map((function(i){t.children[i]=e(t.children[i],n)}));"toggle"===t.type&&!1!==t.visible_by_choice&&(n=P(t,n));"choice"===t.type&&!0===t.visible_by_choice&&(n=P(t,n));return c({},t)}(e[t],c({},e[t])))})),c({},e)}function P(e,t){return Object.keys(e.choices).map((function(n){var i=e.choices[n].data;t=function e(t,n){"object"===u(n.children)&&Object.keys(n.children).length>0&&Object.keys(n.children).map((function(i){Object.assign(n.children[i],e(t,n.children[i]))}));void 0!==n.visible_values&&Object.keys(n.visible_values).length>0&&("hidden"!==n.row_style&&(n.row_style_visible=n.row_style),n.row_style="hidden");return c({},n)}(i,t)})),Object.keys(e.choices).map((function(n){var i=e.choices[n];i.value===e.value&&(t=function e(t,n){"object"===u(n.children)&&Object.keys(n.children).length>0&&Object.keys(n.children).map((function(i){Object.assign(n.children[i],e(t,n.children[i]))}));void 0!==n.visible_values&&Object.keys(n.visible_values).length>0&&n.visible_values.includes(t)&&("hidden"===n.row_style&&(void 0===n.row_style_visible?n.row_style="standard":(n.row_style=n.row_style_visible,delete n.row_style_visible)),Object.keys(n.visible_labels).length>0&&void 0!==n.visible_labels[t]&&(n.label=n.visible_labels[t].label,"string"==typeof n.visible_labels[t].help?n.help=n.visible_labels[t].help:n.help=null));return c({},n)}(i.data,t))})),c({},t)}},"ohO+":function(e,t,n){"use strict";function i(e,t,n){var i="_self";t&&"string"==typeof t.target&&(i=t.target);var o="";t&&"string"==typeof t.specs&&(o=t.specs),"boolean"==typeof n&&!1===n&&(n=""),null==n&&(n="en_GB"),""!==n&&(n="/"+n),window.open(window.location.protocol+"//"+window.location.hostname+n+e,i,o)}n.d(t,"a",(function(){return i}))}},[["HNiu","runtime",0,1]]]);