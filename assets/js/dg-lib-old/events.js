import { postData } from './utils/network.js';
import regionEditor from './modules/regioneditor.js';

export const events = () => {
  Array.from(document.links).forEach((link) => {
    link.addEventListener('click', (event) => event.preventDefault());
  });

  document.querySelector('.dg-send').addEventListener('click', () => {
    postData(
      '/wp-admin/admin-ajax.php?action=save_regions_list',
        {regions_list: regionEditor.elements}
    ).then((data) => console.log(data));
  });
};
