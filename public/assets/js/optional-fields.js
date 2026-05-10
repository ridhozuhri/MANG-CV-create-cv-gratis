(function () {
  'use strict';

  function updateVisibility(checkbox, block) {
    if (checkbox.checked) {
      block.classList.add('is-visible');
    } else {
      block.classList.remove('is-visible');
    }
  }

  function init() {
    var checkboxes = document.querySelectorAll('.option-pill input[type="checkbox"]');
    if (!checkboxes.length) return;

    var personalData = (window.__STEP_DATA__ && window.__STEP_DATA__.personal)
      ? window.__STEP_DATA__.personal
      : {};

    checkboxes.forEach(function (cb) {
      var blockId = cb.id.replace('opt-', '') + '-block';
      var block = document.getElementById(blockId);
      if (!block) return;

      var fieldName = cb.id.replace('opt-', '');
      if (fieldName === 'location' && personalData.location) cb.checked = true;
      if (fieldName === 'birth' && personalData.birth_date) cb.checked = true;
      if (fieldName === 'linkedin' && personalData.linkedin) cb.checked = true;
      if (fieldName === 'website' && personalData.website) cb.checked = true;

      updateVisibility(cb, block);

      cb.addEventListener('change', function () {
        updateVisibility(cb, block);

        if (!cb.checked) {
          var input = block.querySelector('input, textarea, select');
          if (input && input.type !== 'file') {
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
          }
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
