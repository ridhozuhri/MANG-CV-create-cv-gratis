var MangCvTemplateSwitcher = (function () {
  var tokenEl = null;
  var token = '';
  var currentTemplate = 'classic';
  var onSwitchCallback = null;
  var initialized = false;

  function init(options) {
    // Guard: init only runs once
    if (initialized) {
      // Update callback if provided on subsequent calls
      if (options && options.onSwitch) {
        onSwitchCallback = options.onSwitch;
      }
      return;
    }

    initialized = true;
    tokenEl = document.querySelector('meta[name="csrf-token"]');
    token = tokenEl ? tokenEl.content : '';
    currentTemplate = window.__CURRENT_TEMPLATE__ || 'classic';
    if (options && options.onSwitch) {
      onSwitchCallback = options.onSwitch;
    }

    // Expose globally for inline onclick (backup)
    window.selectTemplate = selectTemplate;

    // Event delegation on tsp-grid
    var grid = document.querySelector('.tsp-grid');
    if (grid) {
      grid.addEventListener('click', function (e) {
        var btn = e.target.closest('.tsp-card');
        if (!btn) return;
        var tmpl = btn.getAttribute('data-template');
        if (tmpl && tmpl !== currentTemplate) {
          selectTemplate(tmpl);
        }
      });
    }

    updateUI(currentTemplate);
  }

  function setToken(nextToken) {
    if (!nextToken) return;
    token = nextToken;
    if (tokenEl) tokenEl.content = nextToken;
  }

  function selectTemplate(template) {
    var valid = window.__AVAILABLE_TEMPLATES__ || ['classic', 'modern', 'sidebar', 'minimalist', 'professional'];
    if (valid.indexOf(template) === -1) {
      return Promise.reject('Invalid template');
    }

    if (template === currentTemplate) {
      return Promise.resolve({ ok: true, template: template });
    }

    var form = new URLSearchParams();
    form.set('csrf_test_name', token);
    form.set('template', template);

    return fetch('/api/switch-template', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: form.toString()
    })
      .then(function (r) { return r.json(); })
      .then(function (r) {
        setToken(r.csrf || token);
        if (r.ok) {
          currentTemplate = template;
          updateUI(template);
          if (onSwitchCallback) {
            onSwitchCallback(template);
          }
          document.dispatchEvent(new CustomEvent('cv:template-changed', {
            detail: { template: template }
          }));
        }
        return r;
      })
      .catch(function (err) {
        console.error('[TemplateSwitcher] Network error:', err);
        throw err;
      });
  }

  function updateUI(template) {
    var cards = document.querySelectorAll('.tsp-card');
    cards.forEach(function (card) {
      if (card.getAttribute('data-template') === template) {
        card.classList.add('tsp-active');
      } else {
        card.classList.remove('tsp-active');
      }
    });

    var nameEl = document.getElementById('tsp-current');
    var names = {
      'classic': 'Classic',
      'modern': 'Modern',
      'sidebar': 'Sidebar',
      'minimalist': 'Minimalist',
      'professional': 'Professional'
    };
    if (nameEl) {
      nameEl.textContent = names[template] || template;
    }

    window.__CURRENT_TEMPLATE__ = template;
  }

  function getCurrentTemplate() {
    return currentTemplate;
  }

  return {
    init: init,
    selectTemplate: selectTemplate,
    getCurrentTemplate: getCurrentTemplate
  };
})();