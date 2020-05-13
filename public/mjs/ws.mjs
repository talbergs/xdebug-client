const ws = new WebSocket('ws://localhost:8080');
const onopen = [];
const onclose = [];
const onmessage = [];

ws.onopen = () => onopen.forEach(cb => cb());
ws.onclose = () => onclose.forEach(cb => cb());
ws.onmessage = e => onmessage.forEach(cb => cb(e));

export default {
  onopen: cb => {
    ws.readyState === ws.OPEN && cb();
    onopen.push(cb);
  },
  onclose: cb => {
    ws.readyState === ws.CLOSED && cb();
    onclose.push(cb);
  },
  onmessage: cb => onmessage.push(cb),
  send: json => ws.send(json),
  close: () => ws.close(),
  ws,
}
