// This file is used by the standalone /preview page.
// The step-5 wizard page uses its own inline script (in cv/step.php).
// Do NOT call MangCvTemplateSwitcher.init() here to avoid overriding the callback.
(function () {
  var tokenEl = document.querySelector('meta[name="csrf-token"]');
  var token = tokenEl ? tokenEl.content : '';
  var container = document.getElementById('preview-container');

  function setToken(nextToken) {
    if (!nextToken) return;
    token = nextToken;
    if (tokenEl) tokenEl.content = nextToken;
  }

  function getVal(id) {
    var el = document.getElementById(id);
    return el ? el.value : '';
  }

  function collectSectionsDraft() {
    var base = window.__STEP_DATA__ || {};

    var personal = {
      name: getVal('name') || (base.personal ? base.personal.name : ''),
      email: getVal('email') || (base.personal ? base.personal.email : ''),
      phone: getVal('phone') || (base.personal ? base.personal.phone : ''),
      location: getVal('location') || (base.personal ? base.personal.location : ''),
      birth_date: getVal('birth_date') || (base.personal ? base.personal.birth_date : ''),
      linkedin: getVal('linkedin') || (base.personal ? base.personal.linkedin : ''),
      website: getVal('website') || (base.personal ? base.personal.website : ''),
      summary: getVal('summary') || (base.personal ? base.personal.summary : ''),
      photo_path: (base.personal ? base.personal.photo_path : undefined)
    };

    var educationList = document.getElementById('education-list');
    var education = base.education || { items: [] };
    if (educationList) {
      var items = Array.from(educationList.querySelectorAll('.row-card')).map(function (card) {
        return {
          school: card.querySelector('.edu-school') ? card.querySelector('.edu-school').value : '',
          degree: card.querySelector('.edu-degree') ? card.querySelector('.edu-degree').value : '',
          year: card.querySelector('.edu-year') ? card.querySelector('.edu-year').value : ''
        };
      }).filter(function (it) { return it.school || it.degree || it.year; });
      education = { items: items };
    }

    var experienceList = document.getElementById('experience-list');
    var experience = base.experience || { items: [] };
    if (experienceList) {
      var items2 = Array.from(experienceList.querySelectorAll('.row-card')).map(function (card) {
        return {
          company: card.querySelector('.exp-company') ? card.querySelector('.exp-company').value : '',
          role: card.querySelector('.exp-role') ? card.querySelector('.exp-role').value : '',
          year: card.querySelector('.exp-year') ? card.querySelector('.exp-year').value : '',
          desc: card.querySelector('.exp-desc') ? card.querySelector('.exp-desc').value : ''
        };
      }).filter(function (it) { return it.company || it.role || it.year || it.desc; });
      experience = { items: items2 };
    }

    var skillList = document.getElementById('skill-list');
    var skills = base.skills || { items: [] };
    if (skillList) {
      var items3 = Array.from(skillList.querySelectorAll('.row-card')).map(function (card) {
        return {
          name: card.querySelector('.skill-name') ? card.querySelector('.skill-name').value : '',
          level: card.querySelector('.skill-level') ? card.querySelector('.skill-level').value : ''
        };
      }).filter(function (it) { return it.name; });
      skills = { items: items3 };
    }

    var langList = document.getElementById('language-list');
    var languages = base.languages || { items: [] };
    if (langList) {
      var items4 = Array.from(langList.querySelectorAll('.row-card')).map(function (card) {
        return {
          name: card.querySelector('.lang-name') ? card.querySelector('.lang-name').value : '',
          level: card.querySelector('.lang-level') ? card.querySelector('.lang-level').value : ''
        };
      }).filter(function (it) { return it.name; });
      languages = { items: items4 };
    }

    return { personal: personal, education: education, experience: experience, skills: skills, languages: languages };
  }

  function renderDraftPreview() {
    var sections = collectSectionsDraft();
    var body = new URLSearchParams();
    body.set('csrf_test_name', token);
    body.set('template', window.__CURRENT_TEMPLATE__ || 'classic');
    Object.keys(sections).forEach(function (sec) {
      body.set('sections[' + sec + ']', JSON.stringify(sections[sec] || {}));
    });

    fetch('/api/preview-draft', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': token
      },
      body: body.toString()
    }).then(function (r) { return r.json(); }).then(function (r) {
      setToken(r.csrf);
      if (r.ok && container) container.innerHTML = r.html;
    });
  }

  var t = null;
  function scheduleDraftPreview() {
    clearTimeout(t);
    t = setTimeout(renderDraftPreview, 300);
  }

  document.addEventListener('input', function (e) {
    var el = e.target;
    if (el && (el.matches('input') || el.matches('textarea') || el.matches('select'))) {
      if (el.id !== 'photo') scheduleDraftPreview();
    }
  });

  document.addEventListener('click', function (e) {
    var el = e.target;
    if (el && (
      el.matches('#education-add') || el.matches('.edu-remove') ||
      el.matches('#experience-add') || el.matches('.exp-remove') ||
      el.matches('#skill-add') || el.matches('.skill-remove') ||
      el.matches('#language-add') || el.matches('.lang-remove')
    )) {
      scheduleDraftPreview();
    }
  });

  document.addEventListener('cv:autosaved', scheduleDraftPreview);
  document.addEventListener('DOMContentLoaded', renderDraftPreview);
})();
