// Create our shared stylesheet:
const sheet = new CSSStyleSheet();
sheet.replaceSync('* {border: darkseagreen; color: darkseagreen; background: teal}');

// Apply the stylesheet to a document:
document.adoptedStyleSheets = [sheet];

document.addEventListener("DOMContentLoaded", function() {
  var status = document.createElement('div');
  var messages = document.createElement('ul');
  document.body.append(status)
  document.body.append(messages)
  var s = new WebSocket('ws://localhost:8080');

  status.innerText = {
    [s.CONNECTING]: 'CONNECTING..',
    [s.OPEN]: 'OPEN.',
    [s.CLOSING]: 'CLOSING.',
    [s.CLOSED]: 'CLOSED',
  }[s.readyState];

  s.onopen = function (e) {
    status.innerText = 'CONNECTED!';
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
    s.send(text.value)
  }

  var button2 = document.createElement('button');
  button.innerText = 'close';
  document.body.append(button2)
  button.onclick = function() {
    s.close()
  }
});

