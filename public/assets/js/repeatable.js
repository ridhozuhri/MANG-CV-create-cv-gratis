(function () {
  'use strict';

  function qs(sel) { return document.querySelector(sel); }

  // EDUCATION
  const eduList = qs('#education-list');
  const eduAdd = qs('#education-add');
  const eduTpl = qs('#education-row-template');
  if (eduList && eduAdd && eduTpl) {
    const initial = (window.__STEP_DATA__?.education?.items && Array.isArray(window.__STEP_DATA__.education.items))
      ? window.__STEP_DATA__.education.items
      : [];

    const addRow = (item) => {
      const node = eduTpl.content.cloneNode(true);
      const card = node.querySelector('.row-card');
      card.querySelector('.edu-school').value = item?.school || '';
      card.querySelector('.edu-degree').value = item?.degree || '';
      card.querySelector('.edu-year').value = item?.year || '';
      card.querySelector('.edu-remove').addEventListener('click', () => card.remove());
      eduList.appendChild(node);
    };

    (initial.length ? initial : [{}]).forEach(addRow);
    eduAdd.addEventListener('click', () => addRow({}));
  }

  // EXPERIENCE
  const expList = qs('#experience-list');
  const expAdd = qs('#experience-add');
  const expTpl = qs('#experience-row-template');
  if (expList && expAdd && expTpl) {
    const initial = (window.__STEP_DATA__?.experience?.items && Array.isArray(window.__STEP_DATA__.experience.items))
      ? window.__STEP_DATA__.experience.items
      : [];

    const addRow = (item) => {
      const node = expTpl.content.cloneNode(true);
      const card = node.querySelector('.row-card');
      card.querySelector('.exp-company').value = item?.company || '';
      card.querySelector('.exp-role').value = item?.role || '';
      card.querySelector('.exp-year').value = item?.year || '';
      card.querySelector('.exp-desc').value = item?.desc || '';
      card.querySelector('.exp-remove').addEventListener('click', () => card.remove());
      expList.appendChild(node);
    };

    (initial.length ? initial : [{}]).forEach(addRow);
    expAdd.addEventListener('click', () => addRow({}));
  }

  // SKILLS
  const skillList = qs('#skill-list');
  const skillAdd = qs('#skill-add');
  const skillTpl = qs('#skill-row-template');
  if (skillList && skillAdd && skillTpl) {
    const initial = (window.__STEP_DATA__?.skills?.items && Array.isArray(window.__STEP_DATA__.skills.items))
      ? window.__STEP_DATA__.skills.items
      : [];

    const addRow = (item) => {
      const node = skillTpl.content.cloneNode(true);
      const card = node.querySelector('.row-card');
      card.querySelector('.skill-name').value = item?.name || '';
      const levelSelect = card.querySelector('.skill-level');
      if (levelSelect && item?.level) {
        levelSelect.value = item.level;
      }
      card.querySelector('.skill-remove').addEventListener('click', () => card.remove());
      skillList.appendChild(node);
    };

    (initial.length ? initial : [{}]).forEach(addRow);
    skillAdd.addEventListener('click', () => addRow({}));
  }

  // LANGUAGES
  const langList = qs('#language-list');
  const langAdd = qs('#language-add');
  const langTpl = qs('#language-row-template');
  if (langList && langAdd && langTpl) {
    const initial = (window.__STEP_DATA__?.languages?.items && Array.isArray(window.__STEP_DATA__.languages.items))
      ? window.__STEP_DATA__.languages.items
      : [];

    const addRow = (item) => {
      const node = langTpl.content.cloneNode(true);
      const card = node.querySelector('.row-card');
      card.querySelector('.lang-name').value = item?.name || '';
      const levelSelect = card.querySelector('.lang-level');
      if (levelSelect && item?.level) {
        levelSelect.value = item.level;
      }
      card.querySelector('.lang-remove').addEventListener('click', () => card.remove());
      langList.appendChild(node);
    };

    (initial.length ? initial : [{}]).forEach(addRow);
    langAdd.addEventListener('click', () => addRow({}));
  }
})();