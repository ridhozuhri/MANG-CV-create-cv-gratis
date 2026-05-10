(function () {
  var tokenEl = document.querySelector('meta[name="csrf-token"]');
  var token = tokenEl ? tokenEl.content : '';
  var status = document.getElementById('save-status');
  var photoInput = document.getElementById('photo');
  var photoStatus = document.getElementById('photo-status');
  var photoThumb = document.getElementById('photo-thumb');
  var photoDeleteBtn = document.getElementById('photo-delete');

  function setToken(nextToken) {
    if (!nextToken) return;
    token = nextToken;
    if (tokenEl) tokenEl.content = nextToken;
  }

  function collectPersonalFromDom() {
    var nameEl = document.getElementById('name');
    var emailEl = document.getElementById('email');
    var phoneEl = document.getElementById('phone');
    var birthEl = document.getElementById('birth_date');
    var locationEl = document.getElementById('location');
    var linkedinEl = document.getElementById('linkedin');
    var websiteEl = document.getElementById('website');
    var summaryEl = document.getElementById('summary');
    if (!nameEl && !emailEl && !summaryEl) return null;
    return {
      name: nameEl ? nameEl.value : '',
      email: emailEl ? emailEl.value : '',
      phone: phoneEl ? phoneEl.value : '',
      birth_date: birthEl ? birthEl.value : '',
      location: locationEl ? locationEl.value : '',
      linkedin: linkedinEl ? linkedinEl.value : '',
      website: websiteEl ? websiteEl.value : '',
      summary: summaryEl ? summaryEl.value : '',
      photo_path: (window.__STEP_DATA__ && window.__STEP_DATA__.personal) ? window.__STEP_DATA__.personal.photo_path : undefined
    };
  }

  function collectEducationFromDom() {
    var list = document.getElementById('education-list');
    if (!list) return null;
    var items = Array.from(list.querySelectorAll('.row-card')).map(function (card) {
      return {
        school: card.querySelector('.edu-school') ? card.querySelector('.edu-school').value : '',
        degree: card.querySelector('.edu-degree') ? card.querySelector('.edu-degree').value : '',
        year: card.querySelector('.edu-year') ? card.querySelector('.edu-year').value : ''
      };
    }).filter(function (it) { return it.school || it.degree || it.year; });
    return { items: items };
  }

  function collectExperienceFromDom() {
    var list = document.getElementById('experience-list');
    if (!list) return null;
    var items = Array.from(list.querySelectorAll('.row-card')).map(function (card) {
      return {
        company: card.querySelector('.exp-company') ? card.querySelector('.exp-company').value : '',
        role: card.querySelector('.exp-role') ? card.querySelector('.exp-role').value : '',
        year: card.querySelector('.exp-year') ? card.querySelector('.exp-year').value : '',
        desc: card.querySelector('.exp-desc') ? card.querySelector('.exp-desc').value : ''
      };
    }).filter(function (it) { return it.company || it.role || it.year || it.desc; });
    return { items: items };
  }

  function collectSkillsFromDom() {
    var list = document.getElementById('skill-list');
    if (!list) return null;
    var items = Array.from(list.querySelectorAll('.row-card')).map(function (card) {
      return {
        name: card.querySelector('.skill-name') ? card.querySelector('.skill-name').value : '',
        level: card.querySelector('.skill-level') ? card.querySelector('.skill-level').value : ''
      };
    }).filter(function (it) { return it.name; });
    return { items: items };
  }

  function collectLanguagesFromDom() {
    var list = document.getElementById('language-list');
    if (!list) return null;
    var items = Array.from(list.querySelectorAll('.row-card')).map(function (card) {
      return {
        name: card.querySelector('.lang-name') ? card.querySelector('.lang-name').value : '',
        level: card.querySelector('.lang-level') ? card.querySelector('.lang-level').value : ''
      };
    }).filter(function (it) { return it.name; });
    return { items: items };
  }

  function collectSectionsForCurrentStep() {
    var step = Number(window.__CURRENT_STEP__ || 1);
    var sections = {};

    if (step === 1) {
      var personal = collectPersonalFromDom();
      if (personal) sections.personal = personal;
    }
    if (step === 2) {
      var education = collectEducationFromDom();
      if (education) sections.education = education;
    }
    if (step === 3) {
      var experience = collectExperienceFromDom();
      if (experience) sections.experience = experience;
    }
    if (step === 4) {
      var skills = collectSkillsFromDom();
      if (skills) sections.skills = skills;
      var languages = collectLanguagesFromDom();
      if (languages) sections.languages = languages;
    }

    return sections;
  }

  function autosaveCurrentStep() {
    var sections = collectSectionsForCurrentStep();
    var keys = Object.keys(sections);
    if (!keys.length) return Promise.resolve();

    var form = new URLSearchParams();
    form.set('csrf_test_name', token);
    keys.forEach(function (sec) {
      form.set('sections[' + sec + ']', JSON.stringify(sections[sec]));
    });

    return fetch('/api/autosave', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': token
      },
      body: form.toString()
    }).then(function (r) { return r.json(); }).then(function (r) {
      setToken(r.csrf);
      if (r.ok) {
        if (status) status.textContent = 'Tersimpan: ' + r.saved_at;
        window.__STEP_DATA__ = window.__STEP_DATA__ || {};
        keys.forEach(function (sec) { window.__STEP_DATA__[sec] = sections[sec]; });
        document.dispatchEvent(new CustomEvent('cv:autosaved'));
      } else {
        if (status) status.textContent = 'Gagal simpan';
      }
    }).catch(function () {
      if (status) status.textContent = 'Gagal simpan (network)';
    });
  }

  var timer = null;
  function onChange() {
    clearTimeout(timer);
    timer = setTimeout(function () { autosaveCurrentStep(); }, 2000);
  }

  document.addEventListener('input', function (e) {
    var t = e.target;
    if (t && (t.matches('input') || t.matches('textarea') || t.matches('select'))) {
      if (t.id !== 'photo') onChange();
    }
  });

  document.addEventListener('click', function (e) {
    var t = e.target;
    if (t && (
      t.matches('#education-add') || t.matches('.edu-remove') ||
      t.matches('#experience-add') || t.matches('.exp-remove') ||
      t.matches('#skill-add') || t.matches('.skill-remove') ||
      t.matches('#language-add') || t.matches('.lang-remove')
    )) {
      onChange();
    }
  });

  if (photoInput) {
    photoInput.addEventListener('change', function () {
      var file = photoInput.files[0];
      if (!file) return;
      var formData = new FormData();
      formData.append('photo', file);
      formData.append('csrf_test_name', token);
      fetch('/api/upload-photo', { method: 'POST', headers: { 'X-CSRF-TOKEN': token }, body: formData })
        .then(function (r) { return r.json(); })
        .then(function (r) {
          setToken(r.csrf);
          if (r.ok) {
            if (photoStatus) photoStatus.textContent = 'Foto terpasang';
            if (photoThumb) photoThumb.src = '/media/photo?ts=' + Date.now();
            window.__STEP_DATA__ = window.__STEP_DATA__ || {};
            window.__STEP_DATA__.personal = window.__STEP_DATA__.personal || {};
            window.__STEP_DATA__.personal.photo_path = r.photo_path;
            document.dispatchEvent(new CustomEvent('cv:autosaved'));
          } else {
            if (photoStatus) photoStatus.textContent = r.message || 'Upload foto gagal';
          }
        });
    });
  }

  if (photoDeleteBtn) {
    photoDeleteBtn.addEventListener('click', function () {
      var body = new URLSearchParams();
      body.set('csrf_test_name', token);
      fetch('/api/delete-photo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token },
        body: body.toString()
      }).then(function (r) { return r.json(); }).then(function (r) {
        setToken(r.csrf);
        if (r.ok) {
          if (photoInput) photoInput.value = '';
          if (photoStatus) photoStatus.textContent = 'Belum ada foto';
          if (photoThumb) photoThumb.removeAttribute('src');
          window.__STEP_DATA__ = window.__STEP_DATA__ || {};
          window.__STEP_DATA__.personal = window.__STEP_DATA__.personal || {};
          delete window.__STEP_DATA__.personal.photo_path;
          document.dispatchEvent(new CustomEvent('cv:autosaved'));
        } else {
          if (photoStatus) photoStatus.textContent = r.message || 'Gagal hapus foto';
        }
      }).catch(function () {
        if (photoStatus) photoStatus.textContent = 'Gagal hapus foto (network)';
      });
    });
  }
})();
