var MangCvAccessibility = (function () {
  'use strict';

  var STORAGE_KEY = 'mangcv_a11y_';
  var currentFontSize = parseInt(localStorage.getItem(STORAGE_KEY + 'fontsize') || '16');
  var currentContrast = localStorage.getItem(STORAGE_KEY + 'contrast') || 'normal';

  var styles = document.createElement('style');
  styles.id = 'a11y-dynamic-styles';
  document.head.appendChild(styles);

  function init() {
    applyFontSize(currentFontSize);
    applyContrast(currentContrast);
    renderToolbar();
    injectSkipLink();
    injectLiveRegion();
  }

  function renderToolbar() {
    if (document.getElementById('a11y-toolbar')) return;

    var toolbar = document.createElement('div');
    toolbar.id = 'a11y-toolbar';
    toolbar.setAttribute('role', 'toolbar');
    toolbar.setAttribute('aria-label', 'Pengaturan aksesibilitas');
    toolbar.innerHTML = getToolbarHTML();
    document.body.appendChild(toolbar);

    bindToolbarEvents();
  }

  function getToolbarHTML() {
    return '<button id="a11y-contrast" aria-label="Ubah kontras warna" title="Kontras">Kontras</button>' +
           '<button id="a11y-font-up" aria-label="Perbesar teks" title="Perbesar">A+</button>' +
           '<button id="a11y-font-down" aria-label="Perkecil teks" title="Perkecil">A-</button>' +
           '<button id="a11y-voice" aria-label="Baca instruksi" title="Voice">Suara</button>';
  }

  function bindToolbarEvents() {
    document.getElementById('a11y-contrast').addEventListener('click', toggleContrast);
    document.getElementById('a11y-font-up').addEventListener('click', increaseFontSize);
    document.getElementById('a11y-font-down').addEventListener('click', decreaseFontSize);
    document.getElementById('a11y-voice').addEventListener('click', readInstructions);
  }

  function toggleContrast() {
    var modes = ['normal', 'high-contrast'];
    var idx = modes.indexOf(currentContrast);
    currentContrast = modes[(idx + 1) % modes.length];
    localStorage.setItem(STORAGE_KEY + 'contrast', currentContrast);
    applyContrast(currentContrast);
    announce('Mode kontras berubah ke ' + (currentContrast === 'high-contrast' ? 'kontras tinggi' : 'normal'));
  }

  function applyContrast(mode) {
    var html = document.documentElement;
    html.classList.remove('hc-normal', 'hc-high');
    if (mode === 'high-contrast') {
      html.classList.add('hc-high');
    } else {
      html.classList.add('hc-normal');
    }
  }

  function increaseFontSize() {
    if (currentFontSize >= 22) return;
    currentFontSize += 2;
    applyFontSize(currentFontSize);
    announce('Ukuran teks: ' + currentFontSize + ' piksel');
  }

  function decreaseFontSize() {
    if (currentFontSize <= 12) return;
    currentFontSize -= 2;
    applyFontSize(currentFontSize);
    announce('Ukuran teks: ' + currentFontSize + ' piksel');
  }

  function applyFontSize(size) {
    localStorage.setItem(STORAGE_KEY + 'fontsize', size);
    styles.textContent = 'html{font-size:' + size + 'px!important;}';
  }

  function readInstructions() {
    if (!('speechSynthesis' in window)) {
      announce('Browser tidak mendukung pembaca teks');
      return;
    }
    window.speechSynthesis.cancel();
    var step = parseInt(window.__CURRENT_STEP__ || 1);
    var texts = {
      1: 'Langkah pertama. Isi nama lengkap, email, dan data diri Anda.',
      2: 'Langkah kedua. Masukkan riwayat pendidikan.',
      3: 'Langkah ketiga. Tulis pengalaman kerja Anda.',
      4: 'Langkah keempat. Tambahkan keahlian dan bahasa.',
      5: 'Langkah kelima. Pratinjau dan unduh CV Anda.'
    };
    var text = texts[step] || texts[1];
    speak(text);
  }

  function speak(text) {
    if (!('speechSynthesis' in window)) return;
    var utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'id-ID';
    utterance.rate = 0.9;
    utterance.pitch = 1;
    window.speechSynthesis.speak(utterance);
  }

  function announce(message) {
    var live = document.getElementById('a11y-live');
    if (!live) return;
    live.textContent = message;
    setTimeout(function() { live.textContent = ''; }, 1000);
  }

  function injectSkipLink() {
    if (document.getElementById('skip-link')) return;
    var link = document.createElement('a');
    link.id = 'skip-link';
    link.href = '#main-content';
    link.textContent = 'Langsung ke konten utama';
    link.className = 'skip-link';
    document.body.insertBefore(link, document.body.firstChild);
  }

  function injectLiveRegion() {
    if (document.getElementById('a11y-live')) return;
    var region = document.createElement('div');
    region.id = 'a11y-live';
    region.setAttribute('role', 'status');
    region.setAttribute('aria-live', 'polite');
    region.setAttribute('aria-atomic', 'true');
    region.className = 'sr-only';
    document.body.appendChild(region);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  return {
    announce: announce,
    speak: speak,
    increaseFontSize: increaseFontSize,
    decreaseFontSize: decreaseFontSize,
    toggleContrast: toggleContrast
  };
})();