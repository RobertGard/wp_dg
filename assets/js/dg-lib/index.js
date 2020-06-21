import CustomMenu from './components/custommenu.js';
import Highlighter from './modules/highlighter.js';
import regionEditor from './modules/regioneditor.js';
import { getDomData } from './utils/dom.js';
import { events } from './events.js';
import { bindModal } from './components/modal.js';

window.onload = () => {
  events();

  const regionModal = bindModal('#region-modal');

  const customMenu = new CustomMenu();
  const highlighter = new Highlighter(customMenu);

  // @ts-ignore
  const elementPicker = new ElementPicker({
    container: document.body,
    selectors: '*',
    background: 'rgba(255, 219, 0, 0.5)',
    borderWidth: 0,
    // @ts-ignore
    ignoreElements: [document.body].concat([
      ...document.querySelectorAll(
        `.${customMenu.className}, .${customMenu.className} *`
      ),
      ...document.querySelectorAll('.dg-send'),
      ...document.querySelectorAll('.dg-modal, .dg-modal *'),
      ...document.querySelectorAll('#wpadminbar, #wpadminbar *')
    ]),
    action: {}
  });

  const highlightedDomElements = document.getElementsByClassName(
    highlighter.className
  );

  document.body.addEventListener('click', (e) => {
    const target = e.target;
    var default_value = '';

    if (target.tagName === 'IMG') {
      default_value = target.getAttribute('src');
    }

    if (
      target.classList.contains(customMenu.className) ||
      target.closest(`.${customMenu.className}`) ||
      target.classList.contains('dg-send') ||
      target.classList.contains('dg-modal') ||
      target.closest('.dg-modal') ||
      target.id === 'wpadminbar' ||
      target.closest('#wpadminbar')
    )
      return;

    const highlightedElement = highlighter.highlightElement(target);
    const domData = getDomData(highlightedElement);
    if (domData) {
      const [region, dgPath, selector] = domData;

      regionEditor.addElement(region, dgPath, selector, default_value);

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
      label: 'Задать тип и имя региона',
      handler(currentTarget) {
        if (currentTarget) {
          regionModal.show().getData(({ name, type }) => {
            const { region, selector } = highlighter.highlightedElements.find(
              (element) =>
                element.target === currentTarget ||
                element.parent === currentTarget
            );

            regionEditor.setTypeAndName({ region, selector, type, name });

            console.log(regionEditor.elements);
          });
        }
      }
    }
  ]);
};
