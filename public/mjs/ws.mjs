export default class Theme {

  static themes = ['theme-light', 'theme-dark'];

  static get() {
    return localStorage.getItem('theme');
  }

  static set(theme) {
    if (!this.themes.includes(theme)) {
      throw `Unknown theme given: ${theme}`;
    }

    this.clear();

    localStorage.setItem('theme', theme);
    document.body.classList.add(theme);
  }

  static clear() {
    localStorage.removeItem('theme');
    this.themes.forEach(theme => document.body.classList.remove(theme));
  }

  static check() {
    this.get() && this.set(this.get());
  }
}
