import Vue from 'https://cdn.jsdelivr.net/npm/vue@2.6.11/dist/vue.esm.browser.js';
import Vuex from 'https://cdn.jsdelivr.net/npm/vuex@3.5.1/dist/vuex.esm.browser.js';
import Theme from './mjs/theme.mjs';
import Ws from './mjs/ws.mjs';
import * as favicon from './mjs/favicon.mjs';

Theme.check();
favicon.waiting();

Vue.use(Vuex);

const store = new Vuex.Store({
  state: {
    count: 0,
    sessions: [],
  },
  mutations: {
    sessions(state, sessions) {
      state.sessions = sessions;
    }
  }
});

window.app = new Vue({
  el: '#app',
  store,
  data: {
    show_add_session_modal: false,
    hl_session_id: 0,
    hl_session_idx: 0,
    hl_menu: '',
  },
  computed: {
    sessions() {
      return this.$store.state.sessions;
    },
    in_xdebub_session() {
      return this.xdebug.appid != '';
    },
  },
  methods: {
    current_session_run() {
      const session_id = this.$store.state.sessions[this.hl_session_idx].id;
      API.xd_run(session_id);
    },
    current_session_step_into() {
      const session_id = this.$store.state.sessions[this.hl_session_idx].id;
      API.xd_step_into(session_id);
    },
    current_session_step_over() {
      const session_id = this.$store.state.sessions[this.hl_session_idx].id;
      API.xd_step_over(session_id);
    },
    current_session_step_out() {
      const session_id = this.$store.state.sessions[this.hl_session_idx].id;
      API.xd_step_out(session_id);
    },
    current_session_stop() {
      const session_id = this.$store.state.sessions[this.hl_session_idx].id;
      API.xd_stop(session_id);
    },
    setSessions(sessions) {
      this.$store.commit('setSessions', sessions)
    },
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
  if (req.type == 'patch') {
    app.$store.commit(req.path, JSON.parse(req.msg));
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

window.API.dd_hub = function() {
  window.Ws.ws.send('app:files ' + JSON.stringify({}));
}

window.API.list_sessions = function() {
  window.Ws.ws.send('app:list_sessions ' + JSON.stringify({}));
}

// window.API.add_session = function(listener_id, idekey) {
//   window.Ws.ws.send('app:add_session ' + JSON.stringify({
//     listener_id, idekey
//   }));
// }

window.API.app_state = function() {
  window.Ws.ws.send('app:state');
}

window.API.xd_stop = function(sessionid) {
  window.Ws.ws.send('xdebug:stop ' + JSON.stringify({
    sessionid
  }));
}

window.API.xd_step_over = function(sessionid) {
  window.Ws.ws.send('xdebug:step_over ' + JSON.stringify({
    sessionid
  }));
}

window.API.xd_step_out = function(sessionid) {
  window.Ws.ws.send('xdebug:step_out ' + JSON.stringify({
    sessionid
  }));
}

window.API.xd_step_into = function(sessionid) {
  window.Ws.ws.send('xdebug:step_into ' + JSON.stringify({
    sessionid
  }));
}

window.API.xd_run = function(sessionid) {
  window.Ws.ws.send('xdebug:run ' + JSON.stringify({
    sessionid
  }));
}

window.API.xd_breakpoint_list = function(sessionid) {
  window.Ws.ws.send('xdebug:breakpoint_list ' + JSON.stringify({
    sessionid
  }));
}

window.API.xd_stack_get = function(sessionid) {
  window.Ws.ws.send('xdebug:stack_get ' + JSON.stringify({
    sessionid
  }));
}

window.API.xd_status = function(sessionid) {
  window.Ws.ws.send('xdebug:status ' + JSON.stringify({
    sessionid
  }));
}

setTimeout(_ => {
  API.start_session('xd-xd', 9000, "KEY1");
}, 1000);

// window.ff = favicon;
