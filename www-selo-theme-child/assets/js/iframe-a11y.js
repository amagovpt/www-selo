// --- A11Y: forçar alt="" em imagens sem alt (inclui a do seletor reportado) ---
(function(){
  // ⬇️ coloca aqui o seletor exato reportado pela ferramenta
  const PROBLEM_SELECTOR = 'html > body:nth-child(2) > div:nth-child(1) > div:nth-child(1) > div:nth-child(1) > img:nth-child(1)';

  function ensureAltEmpty(img) {
    if (!(img instanceof Element) || img.tagName !== 'IMG') return;
    const current = img.getAttribute('alt');
    if (current == null || current.trim() === '' || /^(undefined|null)$/i.test(current.trim())) {
      img.setAttribute('alt', '');
      // opcional: torná-la decorativa no AOM
      img.setAttribute('role', 'presentation');
      img.setAttribute('aria-hidden', 'true');
    }
  }

  function fixAllMissingAlt(root = document) {
    root.querySelectorAll('img:not([alt])').forEach(ensureAltEmpty);
    // também corrige casos onde alguém pôs alt="undefined" ou "null"
    root.querySelectorAll('img[alt="undefined"], img[alt="null"]').forEach(ensureAltEmpty);
  }

  function fixSpecific() {
    const img = document.querySelector(PROBLEM_SELECTOR);
    if (img) ensureAltEmpty(img);
  }

  // 1) Correção imediata
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      fixSpecific();
      fixAllMissingAlt();
    });
  } else {
    fixSpecific();
    fixAllMissingAlt();
  }

  // 2) Observer para apanhar imagens que entrem depois ou mudem src/srcset
  const mo = new MutationObserver((muts) => {
    for (const m of muts) {
      if (m.type === 'childList') {
        m.addedNodes.forEach((n) => {
          if (!(n instanceof Element)) return;
          if (n.tagName === 'IMG') {
            ensureAltEmpty(n);
          } else {
            // se entrou um bloco de HTML, corrige as imagens lá dentro
            fixAllMissingAlt(n);
          }
        });
      } else if (m.type === 'attributes' && m.target.tagName === 'IMG') {
        if (m.attributeName === 'src' || m.attributeName === 'srcset' || m.attributeName === 'alt') {
          ensureAltEmpty(m.target);
        }
      }
    }
  });

  // observar documento inteiro
  const startObserver = () => mo.observe(document.documentElement, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['src', 'srcset', 'alt']
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startObserver);
  } else {
    startObserver();
  }

  // 3) Repassada tardia (cobre mutações fora do observer inicial)
  setTimeout(() => {
    fixSpecific();
    fixAllMissingAlt();
  }, 1200);

  // util de debug opcional
  window.__forceFixAlt = () => { fixSpecific(); fixAllMissingAlt(); };
})();
