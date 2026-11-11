/* Dossier técnico WACRM — interacciones mínimas
   1. Barra de progreso de lectura
   2. Botón de copiar en cada bloque de código
   3. Aparición suave de secciones al hacer scroll
*/

(function () {
  'use strict';

  /* ---------- 1. Progreso de lectura + borde de la barra superior ------- */

  var bar = document.querySelector('.progress');
  var topbar = document.querySelector('.topbar');

  function onScroll() {
    var max = document.documentElement.scrollHeight - window.innerHeight;
    var pct = max > 0 ? (window.scrollY / max) * 100 : 0;
    if (bar) bar.style.width = pct + '%';
    if (topbar) topbar.classList.toggle('is-stuck', window.scrollY > 8);
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- 2. Copiar código ------------------------------------------ */

  document.querySelectorAll('.code').forEach(function (block) {
    var btn = block.querySelector('.code__copy');
    var pre = block.querySelector('pre');
    if (!btn || !pre) return;

    btn.addEventListener('click', function () {
      var text = pre.innerText;
      var done = function () {
        btn.textContent = 'Copiado';
        btn.classList.add('is-done');
        setTimeout(function () {
          btn.textContent = 'Copiar';
          btn.classList.remove('is-done');
        }, 1600);
      };

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done, fallback);
      } else {
        fallback();
      }

      function fallback() {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch (e) { /* silencio */ }
        document.body.removeChild(ta);
      }
    });
  });

  /* ---------- 3. Aparición al entrar en pantalla ------------------------ */

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var items = document.querySelectorAll('.reveal');

  if (reduce || !('IntersectionObserver' in window)) {
    items.forEach(function (el) { el.classList.add('is-in'); });
    return;
  }

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-in');
        io.unobserve(entry.target);
      }
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

  items.forEach(function (el) { io.observe(el); });
})();

/* Menú móvil del header: abrir/cerrar, accesibilidad y cierre automático */
(function () {
'use strict';

var topbar = document.querySelector('.topbar');
var toggle = document.querySelector('.topbar__toggle');
var menu = document.getElementById('topbar-menu');
if (!topbar || !toggle || !menu) return;

function setOpen(open) {
  topbar.classList.toggle('is-open', open);
  toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
}

toggle.addEventListener('click', function () {
  setOpen(!topbar.classList.contains('is-open'));
});

menu.addEventListener('click', function (e) {
  if (e.target.closest('a')) setOpen(false);
});

document.addEventListener('click', function (e) {
  if (!topbar.contains(e.target)) setOpen(false);
});

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') { setOpen(false); toggle.focus(); }
});

window.addEventListener('resize', function () {
  if (window.innerWidth > 900) setOpen(false);
});
})();