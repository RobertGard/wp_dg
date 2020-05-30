class CustomMenu {
  static ACTIVE_CLASS = 'active';
  constructor(className = 'dg-menu') {
    this._className = className;
    this._menu = document.querySelector(`.${className}`);
    this._menuList = this._menu.querySelector('ul');
    this._currentTarget = null;

    this.onCloseMenu = this.onCloseMenu.bind(this);
    this.onOpenMenu = this.onOpenMenu.bind(this);
  }

  get className() {
    return this._className;
  }

  showMenu(event) {
    this._menu.style.top = `${event.clientY}px`;
    this._menu.style.left = `${event.clientX}px`;
    this._menu.classList.add(CustomMenu.ACTIVE_CLASS);
  }

  hideMenu() {
    this._menu.classList.remove(CustomMenu.ACTIVE_CLASS);
  }
  onOpenMenu(event) {
    event.preventDefault();
    this._currentTarget = event.currentTarget;
    this.showMenu(event);
  }

  onCloseMenu(event) {
    if (event.button !== 2) {
      this.hideMenu();
    }
  }

  _createActionItem(name, label) {
    const item = document.createElement('li');
    item.setAttribute('id', name);
    item.textContent = label;
    return item;
  }

  _addHandlerToActionItem(item, handler) {
    item.addEventListener('click', (event) => {
      handler(this._currentTarget, event);
    });
  }

  _appendActionItemToMenu(item) {
    this._menuList.append(item);
  }

  addAction({ name, label, handler }) {
    const actionItem = this._createActionItem(name, label);
    this._addHandlerToActionItem(actionItem, handler);
    this._appendActionItemToMenu(actionItem);
    return this;
  }

  addActions(actions = []) {
    actions.forEach((action) => this.addAction(action));
  }
}

export default CustomMenu;
