import Vue from 'https://cdn.jsdelivr.net/npm/vue@2.6.11/dist/vue.esm.browser.js';
import Theme from './mjs/theme.mjs';
import Ws from './mjs/ws.mjs';
import * as favicon from './mjs/favicon.mjs';

Theme.check();
favicon.waiting();

window.app = new Vue({
  el: '#app',
  data: {
    show_new_session_modal: false,
    sessions: []
  },
  computed: {
    in_xdebub_session() {
      return this.xdebug.appid != '';
    },
  },
  methods: {
    exit_session() {
      alert("exit");
    },
    app_view_session() {
      this.view = 'session'
    },
    app_view_code() {
      this.view = 'code'
    },
    app_view_filetree() {
      this.view = 'filetree'
    },
    app_view_settings() {
      this.view = 'settings'
    },
    icon_off() {
      favicon.waiting();
    },
    icon_on() {
      favicon.inSessionAnim();
    },
    ws(txt) {
      Ws.send(txt);
    },
    extend(obj) {
      console.log(obj);
      for (let key in obj) {
        this[key] = Object.assign({}, data[key], obj[key]);
      }
    },
    theme_light() {
      Theme.set('theme-light');
    },
    theme_dark() {
      Theme.set('theme-dark');
    },
  }
});

Ws.onopen(_ => {
  Ws.send('app:state');
  app.$data.ws_connedted = true;
});

Ws.onclose(_ => {
  app.$data.ws_connedted = false;
});

Ws.onmessage(msg => {
  const req = JSON.parse(msg.data);
  if (req.type == 'notify') {
    new Notification(req.msg);
  }
});

Notification.requestPermission();

// register service worker:
// if (navigator.serviceWorker) {
//   navigator.serviceWorker.register('/sw.js')
//     .then(function() {
//       return navigator.serviceWorker.ready;
//     })
//     .then(function(registration) {
//       console.log(registration); // service worker is ready and working...
//     });

//   navigator.serviceWorker.addEventListener('message', function(event) {
//     console.log(event.data.message); // Hello World !
//   });
// }

window.Ws = Ws;
window.Theme = Theme;

window.API = {}

window.API.list_connections = function() {
  window.Ws.ws.send('app:list_connections');
}

window.API.start_session = function(host, port, idekey) {
  window.Ws.ws.send('app:start_session ' + JSON.stringify({
    host, port, idekey
  }));
}

window.API.add_listener = function(host, port) {
  window.Ws.ws.send('app:add_listener ' + JSON.stringify({
    host, port
  }));
}

window.API.list_sessions = function() {
  window.Ws.ws.send('app:list_sessions ' + JSON.stringify({}));
}

window.API.add_session = function(listener_id, idekey) {
  window.Ws.ws.send('app:add_session ' + JSON.stringify({
    listener_id, idekey
  }));
}

// setTimeout(_ => {
//   window.API.add_session('0.0.0.0', 9000, 'dd');
//   setTimeout(_ => {
//     window.API.list_connections();
//   }, 1000);
// }, 1000);

// window.ff = favicon;
