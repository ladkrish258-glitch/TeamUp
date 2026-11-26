<header class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle" type="button" aria-label="Open menu" aria-controls="sidebar" aria-expanded="false">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <nav class="breadcrumb" aria-label="Breadcrumb"><span class="current" aria-current="page">Home</span></nav>
    </div>
    <div class="search-box cursor-pointer" role="button" tabindex="0" title="Search pages (Ctrl+K)" onclick="if(window.__openCmdk){window.__openCmdk();}else{document.dispatchEvent(new KeyboardEvent('keydown',{key:'k',ctrlKey:true,bubbles:true}));}" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();if(window.__openCmdk){window.__openCmdk();}else{document.dispatchEvent(new KeyboardEvent('keydown',{key:'k',ctrlKey:true,bubbles:true}));}}">
      <svg class="s-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <circle cx="7" cy="7" r="5" />
        <path d="M11 11l3.5 3.5" />
      </svg>
      <input type="text" placeholder="Search pages..." aria-label="Open command palette" readonly style="cursor:pointer">
      <kbd>CTRL+K</kbd>
    </div>
    <div class="topbar-right">
      
      </a>
      <button class="tb-btn theme-toggle" type="button" title="Toggle theme" aria-label="Toggle theme" aria-pressed="false">
        <svg class="theme-icon-light" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <circle cx="12" cy="12" r="4" />
          <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
        </svg>
        <svg class="theme-icon-dark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
        </svg>
      </button>
      <button class="tb-avatar" type="button" aria-label="Account menu" aria-haspopup="menu" aria-expanded="false">
        <?php if (!empty($teamup_admin_photo)): ?>
          <img src="<?= htmlspecialchars($teamup_admin_photo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($teamup_admin_name, ENT_QUOTES, 'UTF-8') ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block">
        <?php else: ?>
          <?= htmlspecialchars($teamup_admin_initials ?? 'A', ENT_QUOTES, 'UTF-8') ?>
        <?php endif; ?>
      </button>
    </div>
  </header>