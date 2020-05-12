import default_state from './default.mjs';
import side_effects from './side_effects.mjs';

function mkHandler(path) {
  return {
    path,
    get(obj, prop) {
      return obj[prop];
    },
    set(obj, prop, value) {
      if (typeof obj[prop] == 'object') {
        if (typeof value != 'object') {
          throw `Unmatched types: ${this.path}.${prop}`;
        }
        Object.assign(obj[prop], value);
      } else if (typeof obj[prop] == 'undefined') {
        throw `Uninitialized property: ${prop}`;
      } else {
        obj[prop] = value;
      }

      side_effects.call(`${this.path}.${prop}`, value);

      return true;
    }
  }
}

const proxify = (obj, path = '', proxied = {}) => {
  for (let prop in obj) {
    proxied[prop] = typeof obj[prop] == 'object'
      ? proxify(obj[prop], `${path}.${prop}`)
      : obj[prop];
  }

  return new Proxy(proxied, mkHandler(path));
};

export default proxify(default_state);
