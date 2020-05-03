const canvas = document.createElement('canvas', {desynchronized: true});
const context = canvas.getContext('2d');
const gradient = context.createLinearGradient(0, 0, 32, 32);

canvas.setAttribute('width', 32);
canvas.setAttribute('height', 32);

gradient.addColorStop(0, '#c7f0fe');
gradient.addColorStop(1, '#56d3c9');

context.strokeStyle = gradient;
context.lineWidth = 5;


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
  context.clearRect(0, 0, 32, 32)

  line(context, 0, 10);
  line(context, 10, 30);
  line(context, 20, 20);
  line(context, 30, 15);
  line(context, 40, 5);

  favicon.href = canvas.toDataURL('image/png');
}


function inSessionAnim() {
  var mod = 35;
  intervalid = setInterval(function() {
    inSession(mod % 70);
    mod += 3;
    if (mod >= 70) mod = -35;
  }, 500);
}

function inSession(mod = 0) {
  context.clearRect(0, 0, 32, 32)

  line(context, mod - 60, 5);
  line(context, mod - 50, 20);
  line(context, mod - 40, 30);
  line(context, mod - 30, 15);
  line(context, mod - 40, 5);
  line(context, mod - 20, 20);
  line(context, mod - 10, 30);
  line(context, mod, 10);
  line(context, mod + 10, 30);
  line(context, mod + 20, 20);
  line(context, mod + 30, 15);
  line(context, mod + 40, 5);
  line(context, mod + 50, 30);
  line(context, mod + 60, 20);
  line(context, mod + 70, 15);
  favicon.href = canvas.toDataURL('image/png');
}


export {
  waiting,
  inSession,
  inSessionAnim,
};
