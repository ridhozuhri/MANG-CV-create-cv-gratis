var MangCvOverflow = (function () {
  'use strict';

  var tokenEl = null;
  var token = '';
  var currentTemplate = 'classic';
  var overflowAlert = null;
  var pageEstimate = null;
  var recommendation = null;

  function init(options) {
    options = options || {};
    tokenEl = document.querySelector('meta[name="csrf-token"]');
    token = tokenEl ? tokenEl.content : '';
    currentTemplate = window.__CURRENT_TEMPLATE__ || 'classic';

    overflowAlert = document.getElementById('overflow-alert');
    pageEstimate = document.getElementById('page-estimate');
    recommendation = document.getElementById('template-recommendation');

    if (options.autoCheck !== false) {
      checkOverflow();
    }
  }

  function setToken(nextToken) {
    if (!nextToken) return;
    token = nextToken;
    if (tokenEl) tokenEl.content = nextToken;
  }

  function checkOverflow() {
    var form = new URLSearchParams();
    form.set('csrf_test_name', token);
    form.set('template', currentTemplate);

    fetch('/api/check-overflow?' + form.toString(), {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(function(r) { return r.json(); })
    .then(function(r) {
      setToken(r.csrf || token);
      if (r.ok && r.analysis) {
        updateUI(r.analysis);
      }
    })
    .catch(function(err) {
      console.error('[overflow] Check failed:', err);
    });
  }

  function updateUI(analysis) {
    if (overflowAlert) {
      if (analysis.has_overflow) {
        var messages = [];
        var sectionNames = {
          'education': 'Pendidikan',
          'experience': 'Pengalaman',
          'skills': 'Keahlian',
          'languages': 'Bahasa'
        };
        analysis.overflow_sections.forEach(function(item) {
          var name = sectionNames[item.section] || item.section;
          messages.push(name + ' (' + item.current + ' dari ' + item.max + ' maks)');
        });
        overflowAlert.innerHTML = '<div class="overflow-icon">!</div>' +
          '<div class="overflow-content">' +
          '<strong>Perhatian</strong>' +
          '<p>Template ' + analysis.template_info.name + ' hanya bisa menampilkan ' + messages.join(', ') + '.</p>' +
          '<p>Silakan hapus beberapa item atau <a href="#" onclick="MangCvOverflow.switchToRecommended(); return false;">ganti ke template ' + analysis.recommended_template + '</a>.</p>' +
          '</div>';
        overflowAlert.style.display = 'flex';
      } else {
        overflowAlert.style.display = 'none';
      }
    }

    if (pageEstimate) {
      pageEstimate.textContent = analysis.estimated_pages + ' halaman';
    }

    if (recommendation && analysis.recommended_template !== currentTemplate) {
      recommendation.innerHTML = '<a href="#" onclick="MangCvTemplateSwitcher.selectTemplate(\'' + analysis.recommended_template + '\'); return false;">' +
        'Rekomendasi: Template ' + analysis.recommended_template.charAt(0).toUpperCase() + analysis.recommended_template.slice(1) +
        '</a>';
      recommendation.style.display = 'block';
    } else if (recommendation) {
      recommendation.style.display = 'none';
    }

    window.__OVERFLOW_ANALYSIS__ = analysis;
  }

  function switchToRecommended() {
    var analysis = window.__OVERFLOW_ANALYSIS__;
    if (analysis && analysis.recommended_template) {
      MangCvTemplateSwitcher.selectTemplate(analysis.recommended_template);
    }
  }

  function getCurrentAnalysis() {
    return window.__OVERFLOW_ANALYSIS__ || null;
  }

  function setTemplate(template) {
    currentTemplate = template;
    checkOverflow();
  }

  function onTemplateChange(template) {
    currentTemplate = template;
    checkOverflow();
  }

  return {
    init: init,
    checkOverflow: checkOverflow,
    switchToRecommended: switchToRecommended,
    getCurrentAnalysis: getCurrentAnalysis,
    setTemplate: setTemplate,
    onTemplateChange: onTemplateChange
  };
})();

document.addEventListener('cv:template-changed', function(e) {
  MangCvOverflow.onTemplateChange(e.detail.template);
});