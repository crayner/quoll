(window.webpackJsonp=window.webpackJsonp||[]).push([["notificationTray"],{"4nQW":function(e,t,n){"use strict";n.r(t);var o=n("q1tI"),i=n.n(o),r=n("i8i4");n("pNMO"),n("4Brf"),n("0oug"),n("4mDm"),n("DQNa"),n("wLYn"),n("zKZe"),n("uL8W"),n("eoL8"),n("NBAS"),n("ExoC"),n("07d7"),n("SuFq"),n("JfAA"),n("PKPk"),n("3bBZ"),n("R5XZ"),n("17x9");function a(e){var t=e.messengerCount,n=e.showMessenger,o=e.messengerTitle,r=t;return i.a.createElement("div",{id:"messageWall",className:"relative"},i.a.createElement("a",{href:"#",title:o,className:0===r?"inactive inline-block relative mr-4 fa-layers fa-fw fa-3x":"inline-block relative mr-4 fa-layers fa-fw fa-3x",onClick:function(){return n()}},0===r?i.a.createElement("span",{className:"far fa-comment-dots text-gray-500"}):i.a.createElement("span",{className:"fas fa-comment-dots text-yellow-500 hover:text-orange-500 ignore-mouse-down"},i.a.createElement("span",{className:"fa-layers-counter absolute",style:{color:"white",fontSize:"0.8rem",top:"18px",left:"6px"}},r))))}function s(e){var t=e.notificationCount,n=e.showNotifications,o=e.notificationTitle,r=t;return i.a.createElement("div",{id:"notifications"},i.a.createElement("a",{className:0===r?"inactive inline-block relative mr-4 fa-layers fa-fw fa-3x":"inline-block relative mr-4 fa-layers fa-fw fa-3x",title:o,onClick:function(){return n()}},0===r?i.a.createElement("span",{className:"far fa-sticky-note text-gray-500 ignore-mouse-down"}):i.a.createElement("span",{className:"fas fa-sticky-note text-yellow-500 hover:text-orange-500 ignore-mouse-down"},i.a.createElement("span",{className:"fa-layers-counter absolute",style:{color:"white",fontSize:"0.8rem",top:"22px",left:"9px"}},r))))}a.defaultProps={messengerCount:0,messengerTitle:"Message Wall"},s.defaultProps={notificationCount:0,notificationTitle:"Notifications"};var c=n("ohO+"),l=n("J5mc");function u(e){return(u="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function f(){return(f=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(e[o]=n[o])}return e}).apply(this,arguments)}function m(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}function p(e,t){return(p=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function h(e,t){return!t||"object"!==u(t)&&"function"!=typeof t?y(e):t}function y(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function d(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}function g(e){return(g=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}var w=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&p(e,t)}(w,e);var t,n,o,r,u=(t=w,function(){var e,n=g(t);if(d()){var o=g(this).constructor;e=Reflect.construct(n,arguments,o)}else e=n.apply(this,arguments);return h(this,e)});function w(e){var t,n;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,w),(t=u.call(this,e)).otherProps=f({},e),t.state={notificationCount:0,messengerCount:0,notificationTitle:"Notifications",messengerTitle:"Message Wall"},t.timeout=!0===t.isStaff?1e4:12e4,t.showNotifications=t.showNotifications.bind(y(t)),t.showMessenger=t.showMessenger.bind(y(t)),t.handleLogout=t.handleLogout.bind(y(t)),t.displayTray=!0,t.delay=(n=0,function(e,t){clearTimeout(n),n=setTimeout(e,t)}),t}return n=w,(o=[{key:"componentDidMount",value:function(){this.displayTray&&(this.loadNotification(250+2e3*Math.random()),this.loadMessenger(250+2e3*Math.random()))}},{key:"componentWillUnmount",value:function(){clearTimeout(this.notificationTime),clearTimeout(this.messengerTime)}},{key:"loadNotification",value:function(e){var t=this;this.notificationTime=setTimeout((function(){Object(l.a)("api/notification/refresh/",{method:"GET"},!1).then((function(e){e.count!==t.state.notiificationCount&&t.setState({notificationCount:e.count,notificationTitle:e.title}),e.redirect&&Object(c.a)("/")})),t.loadNotification(t.timeout)}),e)}},{key:"loadMessenger",value:function(e){var t=this;this.messengerTime=setTimeout((function(){Object(l.a)("api/messenger/refresh/",{method:"GET"},!1).then((function(e){e.count!==t.state.messengerCount&&t.setState({messengerCount:e.count,messengerTitle:e.title}),e.redirect&&Object(c.a)("/")})),t.loadMessenger(t.timeout)}),e)}},{key:"showNotifications",value:function(){this.state.notificationCount>0&&Object(c.a)("/notifications/manage/",{method:"GET"},!1)}},{key:"showMessenger",value:function(){this.state.messengerCount>0&&Object(c.a)("/messenger/today/show/",{method:"GET"},!1)}},{key:"handleLogout",value:function(){Object(c.a)("/logout/",{method:"GET"},!1)}},{key:"render",value:function(){return i.a.createElement("div",{className:"flex flex-row-reverse mb-1"},i.a.createElement(a,{messengerCount:this.state.messengerCount,showMessenger:this.showMessenger,title:this.state.messengerTitle}),i.a.createElement(s,{notificationCount:this.state.notificationCount,showNotifications:this.showNotifications,title:this.state.notificationTitle}))}}])&&m(n.prototype,o),r&&m(n,r),w}(o.Component);w.defaultProps={displayTray:!1,locale:!1};var b=document.getElementById("notificationTray");null===b?Object(r.render)(i.a.createElement("div",null," "),document.getElementById("dumpStuff")):Object(r.render)(i.a.createElement(w,null),b)},J5mc:function(e,t,n){"use strict";n.d(t,"a",(function(){return i}));n("yq1k"),n("zKZe"),n("07d7"),n("5s+n"),n("rB9j"),n("JTJg"),n("UxlC");function o(){return(o=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(e[o]=n[o])}return e}).apply(this,arguments)}function i(e,t,n){var i={};t&&t.headers&&(i=t.headers,delete t.headers),i=o({},i,{"Content-Type":"application/json; charset=utf-8"}),null===n&&(n="en_GB"),!1!==n&&""!==e||(n="");var a=window.location.protocol+"//"+window.location.hostname+"/";return"/"===e[0]&&(e=e.substring(1)),"http"===e.substring(0,4)&&(a=""),fetch(a+n+e,o({},t,{credentials:"same-origin",headers:i})).then(r).then((function(e){return e.text().then((function(e){return(e=e.replace("<?php","")).includes("window.Sfdump")||e.includes("<?php")?(console.log(e),[]):"string"==typeof e?JSON.parse(e):""}))}))}function r(e){if(e.status>=200&&e.status<400)return e;var t=new Error(e.statusText);throw t.response=e,t}},"ohO+":function(e,t,n){"use strict";function o(e,t,n){var o="_self";t&&"string"==typeof t.target&&(o=t.target);var i="";t&&"string"==typeof t.specs&&(i=t.specs),"boolean"==typeof n&&!1===n&&(n=""),null==n&&(n="en_GB"),""!==n&&(n="/"+n),window.open(window.location.protocol+"//"+window.location.hostname+n+e,o,i)}n.d(t,"a",(function(){return o}))}},[["4nQW","runtime",0,1]]]);