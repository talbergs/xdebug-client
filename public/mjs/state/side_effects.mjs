export default new class SideEffects {
  constructor() {
    this.side_effects = {};
  }

  getPathSideEffects(path) {
    if (!this.side_effects[path]) {
      this.side_effects[path] = [];
    }
    return this.side_effects[path];
  }

  bind(path, cb) {
    this.getPathSideEffects(path).push(cb);
  }

  call(path, ctx) {
    this.getPathSideEffects(path).forEach(cb => cb(ctx))
  }
}
