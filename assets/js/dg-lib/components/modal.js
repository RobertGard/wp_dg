export function bindModal(modalSelector) {
  const $modal = document.querySelector(modalSelector);

  if (!$modal) return;

  const $modalWrapper = $modal.closest('.dg-modal');
  const $closeModalBtn = $modal.querySelector('.dg-modal-close');
  const $modalForm = $modal.querySelector('.dg-modal-form');

  const handlers = {};

  function _handleSubmit(callback) {
    return (
      handlers['submit'] ||
      (handlers['submit'] = (event) => {
        if (!callback || typeof callback !== 'function') {
          return;
        }

        event.preventDefault();
        callback(_getAllDataFromForm(event.target));

        _hide();

        event.target.removeEventListener('submit', _handleSubmit(callback));
      })
    );
  }

  function _hide() {
    $modalWrapper.classList.remove('active');
  }

  document.body.addEventListener('click', function(event) {
    if (event.target === $modalWrapper || event.target === $closeModalBtn) {
      this.classList.remove('modal-active');
      _hide();
    }
  });

  return {
    show() {
      $modalWrapper.classList.add('active');
      document.body.classList.add('modal-active');
      return this;
    },

    getData(callback) {
      if (!$modalForm) return;

      $modalForm.addEventListener('submit', _handleSubmit(callback));
    }
  };
}

function _getAllDataFromForm($form) {
  return Object.fromEntries(new FormData($form).entries());
}
