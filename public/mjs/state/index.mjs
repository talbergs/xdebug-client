import side_effects from './side_effects.mjs';
import proxied_state from './proxy.mjs';

export const bind = (path, ctx) => side_effects.bind(path, ctx);
export const state = proxied_state;

export default {state, bind};
window.mod_state = {state, bind};
