import {bind, state} from './mjs/state/index.mjs';
import Theme from './mjs/theme.mjs';
import Ws from './mjs/ws.mjs';
import * as favicon from './mjs/favicon.mjs';

bind('.lvl1.lvl2', ctx => {
  document.title = ctx
  console.log('lvl1.lvl2', ctx, '++++_')
});

Ws.onopen(_ => {
  Ws.send('app:state');
});

Ws.onmessage(msg => {
  console.log(msg);
  const state_patch = JSON.parse(msg.data);
  Object.assign(state, state_patch);
});
 
Theme.check();
favicon.waiting();

window.Ws = Ws;
window.Theme = Theme;
window.ff = favicon;
