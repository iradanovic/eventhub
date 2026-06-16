/**
 * EventHub - mobilna navigacija
 */
(function () {
  'use strict';

  var toggle = document.getElementById('navToggle');
  var nav    = document.getElementById('glavnaNavigacija');

  if (!toggle || !nav) {
    return;
  }

  toggle.addEventListener('click', function () {
    var open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();
