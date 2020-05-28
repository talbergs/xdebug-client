import Vue from 'https://cdn.jsdelivr.net/npm/vue@2.6.11/dist/vue.esm.browser.js';
import data from './mjs/state/default.mjs';
import Theme from './mjs/theme.mjs';
import Ws from './mjs/ws.mjs';
import * as favicon from './mjs/favicon.mjs';

Ws.onopen(_ => Ws.send('app:state'));
Ws.onmessage(msg => app.extend(JSON.parse(msg.data)));

Theme.check();
favicon.waiting();

window.app = new Vue({
  el: '#app',
  data: Object.assign(data, {
    path_remap: ''
  }),
  computed: {
    xdebug_session_status() {
    },
  },
  methods: {
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

window.Ws = Ws;
window.Theme = Theme;
// window.ff = favicon;
