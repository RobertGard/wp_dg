import CustomMenu from './components/custommenu.js';
import Highlighter from './modules/highlighter.js';
import regionEditor from './modules/regioneditor.js';
import { getDomData } from './utils/dom.js';
import { events } from './events.js';

window.onload = () => {
  events();

  const customMenu = new CustomMenu();
  const highlighter = new Highlighter(customMenu);

  const elementPicker = new ElementPicker({
    container: document.body,
    selectors: '*',
    background: 'rgba(255, 219, 0, 0.5)',
    borderWidth: 0,
    ignoreElements: [document.body].concat([
      ...document.querySelectorAll(
        `.${customMenu.className}, .${customMenu.className} *`
      ),
      ...document.querySelectorAll('.dg-send')
    ]),
    action: {}
  });

  const highlightedDomElements = document.getElementsByClassName(
    highlighter.className
  );

  document.body.addEventListener('click', (e) => {
    const target = e.target;

    if (
      target.classList.contains(customMenu.className) ||
      target.closest(`.${customMenu.className}`) ||
      target.classList.contains('dg-send')
    )
      return;

    const highlightedElement = highlighter.highlightElement(target);
    const domData = getDomData(highlightedElement);
    if (domData) {
      const [region, dgPath, selector] = domData;

      regionEditor.addElement(region, dgPath, selector);

      const highlightedElementIndex = highlighter.findElementIndex(
        highlightedElement
      );
      highlighter.setSelectorAndRegion(
        highlightedElementIndex,
        selector,
        region
      );
      console.log(regionEditor.elements);
      console.log(highlighter.highlightedElements);
    }
    Array.from(highlightedDomElements).forEach((element) => {
      element.addEventListener('contextmenu', customMenu.onOpenMenu, true);
    });
  });

  document.documentElement.addEventListener('click', customMenu.onCloseMenu);

  customMenu.addActions([
    {
      name: 'deselect',
      label: 'Отменить выбранную область',
      handler(currentTarget) {
        if (currentTarget) {
          currentTarget.classList.remove(highlighter.className);
          const removedElement = highlighter.removeElement(currentTarget);
          regionEditor.removeElement(removedElement);
          console.log(regionEditor.elements);
        }
      }
    },
    {
      name: 'setType',
      label: 'Установить тип элемента',
      handler(currentTarget) {
        if (currentTarget) {
          const [region, , selector] = getDomData(currentTarget);
          console.log(region, selector);
        }
      }
    }
  ]);
};
