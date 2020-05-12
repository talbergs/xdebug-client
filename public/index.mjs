import {bind, state} from './mjs/state/index.mjs';
import Theme from './mjs/theme.mjs';
import Ws from './mjs/ws.mjs';
import * as favicon from './mjs/favicon.mjs';

bind('.lvl1.lvl2', ctx => {
  console.log('lvl1.lvl2', ctx, '++++_')
});
 
window.state = state
Object.assign(state, {lvl1: {lvl2: 'k2k'}})

Theme.check();

favicon.waiting();

window.Theme = Theme;
window.ff = favicon;

// console.log(state);

  var status = document.createElement('div');
  var messages = document.createElement('ul');
  document.body.append(status)
  document.body.append(messages)
  var s = new WebSocket('ws://localhost:8080');
  s.onopen = function (e) {
    status.innerText = 'Connected!';
    // state.connected = true;
    console.log(e);
  }
  s.onclose = function (e) {
    status.innerText = 'CLOSED!';
    console.log(e);
  }
  s.onmessage = function(e) {
    var message = document.createElement('li');
    message.innerText = e.data;
    messages.prepend(message);
  }

  var text = document.createElement('input');
  var button = document.createElement('button');
  button.innerText = 'send';
  document.body.append(text)
  document.body.append(button)
  button.onclick = function() {
    favicon.inSession();
    s.send(text.value)
  }

  var button2 = document.createElement('button');
  button2.innerText = 'close';
  document.body.append(button2)
  button2.onclick = function() {
    s.close()
  }
