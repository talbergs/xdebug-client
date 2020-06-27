import Vue from 'https://cdn.jsdelivr.net/npm/vue@2.6.11/dist/vue.esm.browser.js';
import data from './mjs/state/default.mjs';
import Theme from './mjs/theme.mjs';
import Ws from './mjs/ws.mjs';
import * as favicon from './mjs/favicon.mjs';


Theme.check();
favicon.waiting();

window.app = new Vue({
  el: '#app',
  data: Object.assign(data, {
    path_remap: '',
    view: '',
    ws_connedted: false,
  }),
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
Ws.onmessage(msg => app.extend(JSON.parse(msg.data)));

window.Ws = Ws;
window.Theme = Theme;
// window.ff = favicon;
