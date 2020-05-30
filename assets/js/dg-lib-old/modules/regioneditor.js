import { isParent } from '../utils/dom.js';
import { uniqueByKey } from '../utils/common.js';

class RegionEditor {
  constructor() {
    this._elements = [];

    this.addElement = this.addElement.bind(this);
    this.removeElement = this.removeElement.bind(this);
  }

  get elements() {
    return this._elements;
  }

  addElement(region, path, selector) {
    const regionIndex = this._elements.findIndex((el) =>
      el.hasOwnProperty(region)
    );

    if (regionIndex !== -1) {
      this.addElementToExistsRegion(regionIndex, region, selector);
    } else {
      this.addNewRegion(region, path, selector);
    }
  }

  addNewRegion(region, path, selector) {
    this._elements.push({
      [region]: {
        path,
        items: [
          {
            selector,
            type: 'text',
            name: `Name${Date.now()}`
          }
        ]
      }
    });
  }

  addElementToExistsRegion(regionIndex, region, selector) {
    const items = this._elements[regionIndex][region].items;
    const isSelectorExists = items.some((item) => item.selector === selector);

    if (isSelectorExists) return;

    const parentIndex = items.findIndex((item) =>
      isParent(item.selector, selector)
    );

    if (parentIndex !== -1) {
      items[parentIndex].selector = selector;
    } else {
      items.push({
        selector,
        type: 'text',
        name: `Name${Date.now()}`
      });
    }

    items.forEach((item, index) => {
      if (isParent(item.selector, selector)) {
        items[index].selector = selector;
      }
    });
    
    this._elements[regionIndex][region].items = uniqueByKey(items, 'selector');
  }

  removeElement({ region, selector }) {
    const regionIndex = this._elements.findIndex((element) =>
      element.hasOwnProperty(region)
    );

    if (regionIndex !== -1) {
      const regionItems = this._elements[regionIndex][region].items;
      const itemIndex = regionItems.findIndex(
        (item) => item.selector === selector
      );

      if (itemIndex !== -1) {
        regionItems.splice(itemIndex, 1);

        if (regionItems.length === 0) {
          return this._elements.splice(regionIndex, 1);
        }
      }
    }
  }

  setType(type, region, selector) {}
}

export default new RegionEditor();
