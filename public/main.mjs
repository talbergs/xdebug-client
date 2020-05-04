import state from './state.mjs';
import * as favicon from './modules/favicon.mjs';

favicon.waiting();

window.xx = state;
window.ff = favicon;

console.log(state);

  var status = document.createElement('div');
  var messages = document.createElement('ul');
  document.body.append(status)
  document.body.append(messages)
  var s = new WebSocket('ws://localhost:8080');
  s.onopen = function (e) {
    status.innerText = 'CONNECTED!';
    state.connected = true;
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
