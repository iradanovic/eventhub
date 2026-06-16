</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <p class="footer-logo">Event<span>Hub</span><i aria-hidden="true">*</i></p>
    <p>Agregator događanja · studentski projekt iz kolegija web servisa.</p>
    <p class="footer-sources">
      Izvori podataka:
      <span class="badge badge-tm">Ticketmaster API</span>
      <span class="badge badge-ical">iCal feed</span>
      <span class="badge badge-scrape">Web scraping</span>
    </p>
    <ul class="social-links" aria-label="Društvene mreže">
      <li>
        <a href="https://www.facebook.com" target="_blank" rel="noopener" aria-label="Facebook">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7h2.4l.4-3h-2.8V9.1c0-.9.3-1.5 1.6-1.5h1.3V5.1C16 5 15.1 5 14.2 5c-2.2 0-3.7 1.3-3.7 3.8V11H8v3h2.5v7h3z"/></svg>
        </a>
      </li>
      <li>
        <a href="https://www.instagram.com" target="_blank" rel="noopener" aria-label="Instagram">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg>
        </a>
      </li>
      <li>
        <a href="https://www.youtube.com" target="_blank" rel="noopener" aria-label="YouTube">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a2.6 2.6 0 0 0-1.8-1.9C18.2 5 12 5 12 5s-6.2 0-7.8.3A2.6 2.6 0 0 0 2.4 7.2 27 27 0 0 0 2 12a27 27 0 0 0 .4 4.8 2.6 2.6 0 0 0 1.8 1.9C5.8 19 12 19 12 19s6.2 0 7.8-.3a2.6 2.6 0 0 0 1.8-1.9A27 27 0 0 0 22 12a27 27 0 0 0-.4-4.8zM10 15V9l5.2 3L10 15z"/></svg>
        </a>
      </li>
      <li>
        <a href="https://github.com" target="_blank" rel="noopener" aria-label="GitHub">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-3.2 19.5c.5.1.7-.2.7-.5v-1.8c-2.8.6-3.4-1.2-3.4-1.2-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.5 1 1.5 1 .9 1.6 2.4 1.1 3 .9.1-.7.4-1.1.6-1.4-2.2-.3-4.6-1.1-4.6-5 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.8 1a9.4 9.4 0 0 1 5 0c1.9-1.3 2.8-1 2.8-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.9-2.4 4.7-4.6 5 .4.3.7.9.7 1.9v2.8c0 .3.2.6.7.5A10 10 0 0 0 12 2z"/></svg>
        </a>
      </li>
    </ul>
    <p class="footer-copy">&copy; <?= date('Y') ?> EventHub · PHP · MySQL · XML/JSON</p>
  </div>
</footer>

<script src="<?= e(APP_URL) ?>/assets/js/main.js"></script>
</body>
</html>
