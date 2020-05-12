const canvas = document.createElement('canvas', {desynchronized: true});
const context = canvas.getContext('2d');
// const gradient = context.createLinearGradient(0, 0, 32, 32);
const gradient = context.createRadialGradient(16, 16, 4, 16, 16, 18);

canvas.setAttribute('width', 32);
canvas.setAttribute('height', 32);

gradient.addColorStop(0, '#c7f0fe');
gradient.addColorStop(1, '#ffffff00');

context.strokeStyle = gradient;
context.fillStyle = gradient;
context.lineWidth = 2;

const favicon = document.querySelector('link[rel="icon"]');
function line(context, pos, end) {
  context.beginPath()
  context.moveTo(0,pos)
  context.lineTo(end, pos)
  context.stroke()
}

var intervalid;
function waiting() {
  clearInterval(intervalid);
  inSession(6);
  favicon.href = canvas.toDataURL('image/png');
}


function inSessionAnim() {
  var mod = 0;
  intervalid = setInterval(function() {
    inSession(mod);
    mod += 2;
    mod %= 8;
  }, 250);
}

function inSession(mod = 0) {
  context.clearRect(0, 0, 32, 32)

  line(context, -1 + mod, 32);
  line(context, 7 + mod, 32);
  line(context, 15 + mod, 32);
  line(context, 23 + mod, 32);
  line(context, 31 + mod, 32);
  line(context, 39 + mod, 32);

  favicon.href = canvas.toDataURL('image/png');
}


export {
  waiting,
  inSession,
  inSessionAnim,
};
