export default new class SideEffects {
  constructor() {
    this.side_effects = {};
  }

  bind(path, cb) {
    if (!this.side_effects[path]) {
      this.side_effects[path] = [];
    }
    this.side_effects[path].push(cb);
  }

  call(path, ctx) {
    console.log(path, this.side_effects)
    if (this.side_effects[path]) {
      this.side_effects[path].forEach(cb => cb(ctx))
    }
  }
}
