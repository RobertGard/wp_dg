import { getLast } from '../utils/common.js';
import { isBody, getAllParentsUntilBody } from '../utils/dom.js';

class Highlighter {
  constructor(menu, className = 'dg-highlight') {
    this._menu = menu;
    this._highlightedElements = [];
    this._className = className;

    this.highlightElement = this.highlightElement.bind(this);
    this.removeElement = this.removeElement.bind(this);
  }

  get highlightedElements() {
    return this._highlightedElements;
  }

  get className() {
    return this._className;
  }

  findElementIndex(element) {
    return this._highlightedElements.findIndex(
      (el) => el.target === element || el.parent === element
    );
  }

  highlightElement(element) {
    const elParents = getAllParentsUntilBody(element);
    const lastParent = getLast(elParents);
    if (this._hasLastParentHighlightClass(lastParent)) return;

    let currentElement = null;

    const elementIndex = this._highlightedElements.findIndex(
      (el) => el.target === element
    );

    if (elementIndex !== -1) {
      if (isBody(element) || isBody(element.parentElement)) {
        return;
      }

      element.classList.remove(this._className);
      element.removeEventListener('contextmenu', this._menu.onOpenMenu, true);

      let currentParent = this._highlightedElements[elementIndex].parent;

      if (currentParent) {
        currentElement = this.changeParent(currentParent, elementIndex);
      } else {
        currentElement = this.addParent(element.parentElement, elementIndex);
      }
    } else {
      currentElement = this.addElement(element, elParents);
    }

    return currentElement;
  }

  addElement(element, elParents) {
    if (this._hasAnyParentHighlightClass(elParents)) return;

    this._removeChildren(element);

    element.classList.add(this._className);

    const newElement = {
      target: element,
      parent: null
    };

    this._highlightedElements.push(newElement);

    return element;
  }

  removeElement(element) {
    const elementIndex = this.findElementIndex(element);
    if (elementIndex === -1) return;
    element.removeEventListener('contextmenu', this._menu.onOpenMenu, true);

    return this._highlightedElements.splice(elementIndex, 1)[0];
  }

  addParent(parent, elementIndex) {
    if (isBody(parent)) return;

    parent.classList.add(this._className);
    this._highlightedElements[elementIndex].parent = parent;
    this._removeChildren(parent);
    return parent;
  }

  changeParent(currentParent, elementIndex) {
    const newParent = currentParent.parentElement;

    if (isBody(currentParent) || isBody(newParent)) return;

    currentParent.classList.remove(this._className);
    currentParent.removeEventListener(
      'contextmenu',
      this._menu.onOpenMenu,
      true
    );

    if (newParent) {
      newParent.classList.add(this._className);
      this._highlightedElements[elementIndex].parent = newParent;
      return newParent;
    }
  }

  setSelectorAndRegion(index, selector, region) {
    if (index !== -1) {
      this._highlightedElements[index].region = region;
      this._highlightedElements[index].selector = selector;
    }
  }

  _removeChildren(element) {
    Array.from(element.querySelectorAll('*')).forEach((child) => {
      child.classList.remove(this._className);
    });
  }

  _hasLastParentHighlightClass(lastParent) {
    return lastParent && lastParent.classList.contains(this._className);
  }

  _hasAnyParentHighlightClass(parents) {
    for (let i = 0; i < parents.length; i++) {
      if (parents[i].classList.contains(this._className)) return true;
    }

    return false;
  }
}

export default Highlighter;
