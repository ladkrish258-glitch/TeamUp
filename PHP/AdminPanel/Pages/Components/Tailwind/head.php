<?php
// Shared Tailwind CSS Play CDN setup for AdminPanel (replaces main-v4-DDS6x4g-.css)
?>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          primary: '#1ABB9C',
          'primary-dk': '#169F85',
        },
        fontFamily: {
          sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
        }
      }
    }
  };

  (function() {
    function syncDarkClass() {
      var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      document.documentElement.classList.toggle('dark', isDark);
    }
    syncDarkClass();
    new MutationObserver(syncDarkClass).observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['data-theme']
    });
  })();
</script>
<style type="text/tailwindcss">
  :root {
    --primary: #1ABB9C;
    --primary-dk: #169F85;
    --primary-lt: rgba(26, 187, 156, 0.14);
    --blue: #3498DB;
    --yellow: #F39C12;
    --purple: #9B59B6;
    --green: #26B99A;
    --red: #E74C3C;
    --bg-body: #f4f6f9;
    --bg-surface: #ffffff;
    --bg-surface-secondary: #f8fafc;
    --text: #1e293b;
    --text-secondary: #64748b;
    --text-muted: #94a3b8;
    --border: #e2e8f0;
    --border-color: #e2e8f0;
    --border-color-light: #e2e8f0;
    --radius: 0.75rem;
  }

  [data-theme="dark"] {
    --bg-body: #0f172a;
    --bg-surface: #1e293b;
    --bg-surface-secondary: #0f172a;
    --text: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    --border: #334155;
    --border-color: #334155;
    --border-color-light: #334155;
  }

  @layer base {
    body {
      @apply m-0 p-0 font-sans antialiased bg-[var(--bg-body)] text-[var(--text)];
    }

    a {
      @apply no-underline text-inherit;
    }
  }

  @layer components {
    .skip-link {
      @apply sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[9999] focus:bg-primary focus:text-white focus:px-3 focus:py-2 focus:rounded-lg;
    }

    /* SIDEBAR */
    .sidebar {
      @apply fixed top-0 left-0 bottom-0 w-[250px] bg-slate-800 text-slate-200 flex flex-col z-[1000] border-r border-slate-700 transition-transform duration-200 -translate-x-full md:translate-x-0;
    }
    .sidebar.open,
    body.sidebar-open .sidebar {
      @apply translate-x-0;
    }
    .sidebar-brand {
      @apply h-16 px-5 flex items-center gap-3 border-b border-slate-700 shrink-0;
    }
    .brand-icon {
      @apply w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white bg-primary shrink-0;
    }
    .brand-name {
      @apply font-bold text-lg tracking-tight text-white;
    }
    .sidebar-nav {
      @apply flex-1 overflow-y-auto px-3 py-4 space-y-5;
    }
    .nav-group {
      @apply space-y-1;
    }
    .nav-label {
      @apply px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400;
    }
    .nav-tree {
      @apply space-y-0.5;
    }
    .nav-link {
      @apply w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-colors cursor-pointer bg-transparent border-0 text-left;
    }
    .nav-link.active {
      @apply bg-primary/20 text-primary;
    }
    .nav-link .icon {
      @apply shrink-0;
    }
    .nav-link .nav-text {
      @apply flex-1 truncate;
    }
    .nav-chev {
      @apply transition-transform duration-200 shrink-0;
    }
    .nav-link[aria-expanded="true"] .nav-chev {
      @apply rotate-90;
    }
    .nav-sub {
      @apply hidden pl-9 space-y-0.5;
    }
    .nav-sub.open,
    .nav-tree.open > .nav-sub,
    .nav-toggle[aria-expanded="true"] + .nav-sub {
      @apply block;
    }
    .nav-sub-inner {
      @apply flex flex-col space-y-0.5 py-1;
    }
    .nav-sublink {
      @apply block px-3 py-1.5 rounded-md text-[13px] text-slate-400 hover:text-white hover:bg-slate-700/70 transition-colors;
    }
    .nav-sublink.active {
      @apply text-primary font-semibold bg-primary/10;
    }
    .sidebar-footer {
      @apply p-3 border-t border-slate-700 shrink-0;
    }
    .sidebar-user {
      @apply flex items-center gap-3 p-2 rounded-lg bg-slate-900/60;
    }
    .sidebar-user .avatar {
      @apply relative w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xs shrink-0;
    }
    .sidebar-user .online {
      @apply absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-slate-800;
    }
    .sidebar-user-info {
      @apply flex-1 min-w-0;
    }
    .sidebar-user-info .name {
      @apply text-sm font-semibold text-white truncate;
    }
    .sidebar-user-info .role {
      @apply text-xs text-slate-400;
    }
    .more-btn {
      @apply bg-transparent border-0 text-slate-400 hover:text-white p-1 rounded cursor-pointer;
    }
    body.sidebar-collapsed .sidebar {
      @apply md:w-[72px];
    }
    body.sidebar-collapsed .topbar {
      @apply md:left-[72px];
    }
    body.sidebar-collapsed .main {
      @apply md:ml-[72px];
    }
    body.sidebar-collapsed .brand-name,
    body.sidebar-collapsed .nav-label,
    body.sidebar-collapsed .nav-text,
    body.sidebar-collapsed .nav-chev,
    body.sidebar-collapsed .sidebar-user-info,
    body.sidebar-collapsed .more-btn,
    body.sidebar-collapsed .nav-sub {
      @apply md:hidden;
    }

    /* TOPBAR */
    .topbar {
      @apply fixed top-0 left-0 md:left-[250px] right-0 h-16 bg-[var(--bg-surface)] border-b border-[var(--border)] px-5 flex items-center justify-between gap-4 z-[900];
    }
    .topbar-left {
      @apply flex items-center gap-3;
    }
    .sidebar-toggle {
      @apply md:hidden p-2 rounded-lg border border-[var(--border)] bg-transparent text-[var(--text)] cursor-pointer;
    }
    .breadcrumb {
      @apply text-sm text-[var(--text-secondary)] flex items-center gap-2;
    }
    .breadcrumb .current {
      @apply font-semibold text-[var(--text)];
    }
    .search-box {
      @apply hidden sm:flex items-center gap-2 bg-[var(--bg-surface-secondary)] border border-[var(--border)] rounded-lg px-3 py-1.5 w-full max-w-xs;
    }
    .s-icon {
      @apply w-4 h-4 text-[var(--text-muted)] shrink-0;
    }
    .search-box input {
      @apply bg-transparent border-0 outline-none text-sm text-[var(--text)] flex-1;
    }
    .search-box kbd {
      @apply text-[10px] font-mono px-1.5 py-0.5 rounded bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-muted)];
    }
    .topbar-right {
      @apply flex items-center gap-3;
    }
    .tb-btn {
      @apply w-9 h-9 rounded-lg border border-[var(--border)] bg-[var(--bg-surface-secondary)] text-[var(--text)] flex items-center justify-center cursor-pointer hover:border-primary transition-colors;
    }
    [data-theme="dark"] .theme-icon-light { @apply hidden; }
    html:not([data-theme="dark"]) .theme-icon-dark { @apply hidden; }
    .tb-avatar {
      @apply w-9 h-9 rounded-full bg-primary text-white font-bold text-xs flex items-center justify-center border-0 cursor-pointer overflow-hidden;
    }

    /* MAIN CONTENT */
    .main {
      @apply md:ml-[250px] pt-16 min-h-screen flex flex-col;
    }
    .page-wrapper {
      @apply flex-1 p-6 max-w-[1400px] w-full mx-auto box-border;
    }
    .page-header {
      @apply mb-6;
    }
    .page-header-row {
      @apply flex items-center justify-between flex-wrap gap-4;
    }
    .page-pretitle {
      @apply text-xs uppercase tracking-wider font-semibold text-primary mb-1;
    }
    .page-title {
      @apply text-2xl font-bold text-[var(--text)] m-0;
    }

    /* GRID & CARDS */
    .row {
      @apply grid gap-4;
    }
    .row.col-2 {
      @apply grid-cols-1 md:grid-cols-2;
    }
    .row.col-3 {
      @apply grid-cols-1 md:grid-cols-3;
    }
    .row.col-4 {
      @apply grid-cols-1 sm:grid-cols-2 lg:grid-cols-4;
    }
    .row.col-4-4 {
      @apply grid-cols-1 lg:grid-cols-2;
    }
    .row.col-4-8 {
      @apply grid-cols-1 lg:grid-cols-[1fr_2fr];
    }
    .card {
      @apply bg-[var(--bg-surface)] border border-[var(--border)] rounded-xl shadow-sm overflow-hidden;
    }
    .card-head,
    .card-header {
      @apply px-5 py-4 border-b border-[var(--border)] flex items-center justify-between gap-3;
    }
    .card-title {
      @apply text-base font-semibold text-[var(--text)] m-0;
    }
    .card-subtitle {
      @apply text-xs text-[var(--text-secondary)] mt-0.5;
    }
    .card-options {
      @apply flex items-center gap-1 relative;
    }
    .card-opt-btn {
      @apply w-7 h-7 rounded-md border-0 bg-transparent text-[var(--text-muted)] hover:bg-[var(--bg-surface-secondary)] hover:text-[var(--text)] flex items-center justify-center cursor-pointer transition-colors;
    }
    .card-opt-btn svg {
      @apply w-4 h-4;
    }
    .card-body {
      @apply p-5;
    }
    .card-body.p-0 {
      padding: 0;
    }
    .card-footer {
      @apply px-5 py-3.5 border-t border-[var(--border)] bg-[var(--bg-surface-secondary)] flex items-center justify-between flex-wrap gap-3;
    }
    .text-muted {
      @apply text-[var(--text-muted)];
    }

    /* STAT CARDS */
    .stat {
      @apply p-5 flex items-center gap-4;
    }
    .stat-icon {
      @apply w-12 h-12 rounded-xl flex items-center justify-center shrink-0 bg-primary/15 text-primary;
    }
    .stat-icon.teal {
      @apply bg-teal-500/15 text-teal-500;
    }
    .stat-icon.blue {
      @apply bg-sky-500/15 text-sky-500;
    }
    .stat-icon.yellow {
      @apply bg-amber-500/15 text-amber-500;
    }
    .stat-icon.purple {
      @apply bg-purple-500/15 text-purple-500;
    }
    .stat-icon.green {
      @apply bg-emerald-500/15 text-emerald-500;
    }
    .stat-icon.red {
      @apply bg-red-500/15 text-red-500;
    }
    .stat-icon svg {
      @apply w-6 h-6;
    }
    .stat-content {
      @apply flex-1 min-w-0;
    }
    .stat-label {
      @apply text-xs font-medium text-[var(--text-secondary)] uppercase tracking-wide;
    }
    .stat-value-row {
      @apply flex items-baseline gap-2 mt-1;
    }
    .stat-value {
      @apply text-2xl font-bold text-[var(--text)];
    }

    /* TABLES */
    .table-responsive,
    .table-wrap {
      @apply w-full overflow-x-auto;
    }
    .table {
      @apply w-full border-collapse text-sm text-left;
    }
    .table th {
      @apply px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--text-secondary)] bg-[var(--bg-surface-secondary)] border-b border-[var(--border)] whitespace-nowrap;
    }
    .table td {
      @apply px-4 py-3 border-b border-[var(--border)] text-[var(--text)] align-middle whitespace-nowrap;
    }
    .table tbody tr:hover {
      @apply bg-[var(--bg-surface-secondary)];
    }
    .cell-mono {
      @apply font-mono text-xs text-[var(--text-secondary)];
    }
    .cell-strong {
      @apply font-semibold text-[var(--text)];
    }
    .description-col {
      @apply max-w-[260px] truncate;
    }
    .users-filters {
      @apply flex flex-wrap items-center gap-3 w-full;
    }
    .users-filters .search-box {
      @apply flex;
    }

    /* STATUS & ACTIVITY FEED */
    .status {
      @apply inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold;
    }
    .status-green {
      @apply bg-emerald-500/15 text-emerald-500;
    }
    .status-red {
      @apply bg-red-500/15 text-red-500;
    }
    .status-yellow {
      @apply bg-amber-500/15 text-amber-500;
    }
    .status-blue {
      @apply bg-sky-500/15 text-sky-500;
    }
    .activity-list {
      @apply list-none m-0 p-0 divide-y divide-[var(--border)];
    }
    .activity-item {
      @apply flex items-start gap-3 py-3;
    }
    .activity-avatar {
      @apply w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0;
    }
    .activity-body {
      @apply text-sm text-[var(--text)] leading-snug;
    }
    .activity-time {
      @apply text-xs text-[var(--text-muted)] mt-0.5;
    }

    /* BUTTONS & BADGES */
    .btn {
      @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-transparent cursor-pointer transition-colors no-underline;
    }
    .btn-primary {
      @apply bg-primary hover:bg-primary-dk text-white;
    }
    .btn-outline {
      @apply bg-transparent border-[var(--border)] text-[var(--text)] hover:bg-[var(--bg-surface-secondary)];
    }
    .btn-secondary {
      @apply bg-slate-500/15 text-[var(--text)] hover:bg-slate-500/25;
    }
    .btn-danger {
      @apply bg-red-500 hover:bg-red-600 text-white;
    }
    .btn[disabled] {
      @apply opacity-60 cursor-not-allowed pointer-events-none;
    }
    .btn-sm {
      @apply px-2.5 py-1 text-xs rounded-md;
    }
    .badge {
      @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200;
    }
    .badge-success {
      @apply bg-emerald-500/15 text-emerald-500;
    }
    .badge-warning {
      @apply bg-amber-500/15 text-amber-500;
    }
    .badge-danger {
      @apply bg-red-500/15 text-red-500;
    }

    /* FORMS */
    .form-row {
      @apply grid grid-cols-1 md:grid-cols-2 gap-4;
    }
    .form-group {
      @apply mb-4;
    }
    .form-label {
      @apply block text-xs font-semibold uppercase tracking-wide text-[var(--text-secondary)] mb-1.5;
    }
    .required {
      @apply text-red-500;
    }
    .form-control {
      @apply w-full px-3 py-2 rounded-lg border border-[var(--border)] bg-[var(--bg-surface-secondary)] text-[var(--text)] text-sm outline-none focus:border-primary box-border;
    }
    .form-control.is-invalid {
      @apply border-red-500 focus:border-red-500;
    }
    .field-error,
    .error-message {
      @apply text-xs text-red-500 mt-1 mb-0 block;
    }
    .avatar-upload {
      @apply relative w-24 h-24 rounded-full overflow-hidden cursor-pointer border-2 border-dashed border-[var(--border)] block shrink-0;
    }
    .avatar-upload .overlay {
      @apply absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity pointer-events-none;
    }
    .avatar-upload .overlay svg {
      @apply w-6 h-6;
    }
    .password-field-wrapper {
      @apply relative;
    }
    .password-toggle-btn {
      @apply absolute top-1/2 right-2.5 -translate-y-1/2 bg-transparent border-0 p-1 cursor-pointer text-[var(--text-muted)] hover:text-[var(--text)] flex items-center;
    }
    .form-check {
      @apply inline-flex items-center gap-2 text-sm text-[var(--text-secondary)] cursor-pointer;
    }
    .input-group {
      @apply relative flex items-center;
    }
    .input-icon {
      @apply absolute left-3 w-4 h-4 text-[var(--text-muted)] pointer-events-none;
    }
    .input-group .form-control {
      @apply pl-9;
    }
    .form-control.has-toggle {
      @apply pr-9;
    }
    .password-toggle {
      @apply absolute right-2.5 top-1/2 -translate-y-1/2 bg-transparent border-0 cursor-pointer text-[var(--text-muted)] p-1 flex items-center justify-center hover:text-[var(--text)];
    }

    /* ALERTS */
    .alert {
      @apply px-4 py-3 rounded-lg text-sm font-medium mb-4 border;
    }
    .alert-danger {
      @apply bg-red-500/10 border-red-500/30 text-red-500;
    }
    .alert-success {
      @apply bg-emerald-500/10 border-emerald-500/30 text-emerald-500;
    }

    /* AUTH PAGES */
    .auth-page {
      @apply min-h-screen flex items-center justify-center p-6 bg-[var(--bg-body)];
    }
    .auth-card {
      @apply w-full max-w-[420px] bg-[var(--bg-surface)] border border-[var(--border)] rounded-2xl p-8 shadow-lg;
    }
    .auth-brand {
      @apply flex items-center gap-3 mb-6;
    }
    .auth-title {
      @apply text-xl font-bold text-[var(--text)] mb-1;
    }
    .auth-subtitle {
      @apply text-sm text-[var(--text-secondary)] mb-6;
    }
    .auth-actions {
      @apply flex items-center justify-between text-xs mb-5 text-[var(--text-secondary)];
    }
    .auth-actions a {
      @apply text-primary hover:underline;
    }
    .auth-divider {
      @apply text-center text-xs text-[var(--text-muted)] my-5 relative;
    }
    .auth-footer {
      @apply text-center text-xs text-[var(--text-secondary)] mt-6;
    }
    .auth-footer a {
      @apply text-primary font-semibold hover:underline;
    }
    .lock-avatar {
      @apply relative w-16 h-16 mx-auto;
    }
    .lock-avatar-circle {
      @apply w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold;
    }
    .lock-icon {
      @apply absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-[var(--bg-surface)] border border-[var(--border)] flex items-center justify-center text-[var(--text)];
    }

    /* SHARED ADMIN WIDGETS */
    .avatar-initials {
      @apply w-9 h-9 rounded-full flex items-center justify-center bg-primary text-white text-xs font-semibold uppercase shrink-0;
    }
    .chart-container {
      @apply w-full h-[260px];
    }
    .row-opt-btn {
      @apply inline-flex items-center justify-center w-7 h-7 p-0 border-0 rounded-md bg-transparent text-[var(--text-muted)] cursor-pointer hover:bg-[var(--bg-surface-secondary)] hover:text-[var(--text)] transition-colors;
    }
    .row-opt-btn svg {
      @apply w-4 h-4;
    }
    .page-link {
      @apply inline-flex items-center justify-center min-w-[32px] h-8 px-2.5 rounded-lg border border-[var(--border)] bg-[var(--bg-surface)] text-xs font-medium text-[var(--text)] hover:border-primary hover:text-primary transition-all cursor-pointer;
    }
    .page-link.active {
      @apply bg-primary border-primary text-white font-semibold pointer-events-none;
    }
    .pagination {
      @apply flex items-center gap-1.5 flex-wrap list-none m-0 p-0;
    }
    .menu-popover,
    .row-menu-popover {
      @apply bg-[var(--bg-surface)] border border-[var(--border)] rounded-xl shadow-2xl p-1.5 z-[1050] flex flex-col gap-0.5 min-w-[150px] text-[var(--text)];
    }
    .menu-item,
    .row-view-opt,
    .row-delete-opt {
      @apply w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-[var(--text)] hover:bg-[var(--bg-surface-secondary)] rounded-md border-0 bg-transparent cursor-pointer text-left transition-colors;
    }
    .row-delete-opt {
      @apply text-red-500 hover:bg-red-500/10;
    }
    /* MODAL */
    .modal-backdrop {
      @apply fixed inset-0 z-[1100] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4;
    }
    .modal-dialog {
      @apply bg-[var(--bg-surface)] text-[var(--text)] border border-[var(--border)] rounded-2xl shadow-2xl w-full max-h-[90vh] flex flex-col overflow-hidden;
    }
    .modal-sm {
      @apply max-w-sm;
    }
    .modal-md {
      @apply max-w-lg;
    }
    .modal-lg {
      @apply max-w-2xl;
    }
    .modal-header {
      @apply px-6 py-4 border-b border-[var(--border)] flex items-center justify-between gap-3 shrink-0;
    }
    .modal-title {
      @apply text-lg font-bold text-[var(--text)] m-0;
    }
    .modal-close {
      @apply w-8 h-8 rounded-lg flex items-center justify-center border-0 bg-transparent text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--bg-surface-secondary)] cursor-pointer transition-colors;
    }
    .modal-body {
      @apply p-6 overflow-y-auto flex-1;
    }
    .modal-footer {
      @apply px-6 py-4 border-t border-[var(--border)] bg-[var(--bg-surface-secondary)] flex items-center justify-end gap-2 shrink-0;
    }
    .pc-toast {
      @apply fixed bottom-5 right-5 z-[1200] px-4 py-2.5 rounded-lg text-sm font-medium shadow-lg bg-slate-800 text-white opacity-0 translate-y-2 pointer-events-none transition-all duration-200;
    }
    .pc-toast.is-visible {
      @apply opacity-100 translate-y-0 pointer-events-auto;
    }
    .pc-toast-success {
      @apply bg-emerald-600 text-white;
    }
    .pc-toast-error {
      @apply bg-red-600 text-white;
    }
    .profile-ring-box {
      @apply flex flex-col items-center text-center gap-2;
    }
    .ring-wrap {
      @apply relative w-20 h-20 flex items-center justify-center;
    }
    .ring-wrap svg {
      @apply w-20 h-20 -rotate-90;
    }
    .ring-bg {
      fill: none;
      stroke: var(--border);
      stroke-width: 6;
    }
    .ring-fill {
      fill: none;
      stroke: var(--primary);
      stroke-width: 6;
      stroke-linecap: round;
      transition: stroke-dashoffset 0.4s ease;
    }
    .ring-center {
      @apply absolute inset-0 flex items-center justify-center text-base font-bold text-[var(--text)];
    }
    .profile-ring-box .note {
      @apply text-xs text-[var(--text-secondary)];
    }
    .ring-link {
      @apply text-xs font-semibold text-primary hover:underline;
    }
    .footer {
      @apply px-6 py-4 text-xs text-[var(--text-muted)] border-t border-[var(--border)];
    }

    /* TOGGLE SWITCH */
    .switch {
      font-size: 10px;
      @apply relative inline-block w-[3.5em] h-[2em];
    }
    .switch input {
      @apply opacity-0 w-0 h-0;
    }
    .slider {
      @apply absolute inset-0 cursor-pointer bg-[#f5aeae] rounded-[30px] transition-all duration-300;
    }
    .slider-card {
      @apply absolute w-[1.4em] h-[1.4em] rounded-full left-[0.3em] bottom-[0.3em] transition-transform duration-300 pointer-events-none;
    }
    .slider-card-face {
      backface-visibility: hidden;
      @apply absolute inset-0 rounded-full transition-transform duration-300;
    }
    .slider-card-front { @apply bg-[#DC3535]; }
    .slider-card-back { @apply bg-[#379237]; transform: rotateY(180deg); }
    .switch input:checked ~ .slider-card { transform: translateX(1.5em); }
    .switch input:checked ~ .slider-card .slider-card-back { transform: rotateY(0); }
    .switch input:checked ~ .slider-card .slider-card-front { transform: rotateY(-180deg); }
    .switch input:checked ~ .slider { @apply bg-[#9ed99c]; }
  }

  /* COMMAND PALETTE (CMDK) */
  .cmdk-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 10000; display: flex; justify-content: center; align-items: flex-start; padding-top: 12vh; animation: cmdkFadeIn 0.15s ease-out; }
  .cmdk-dialog { background: var(--bg-surface, #1e293b); border: 1px solid var(--border, #334155); border-radius: var(--radius, 12px); width: min(580px, calc(100vw - 32px)); max-height: 72vh; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px var(--border, #334155); display: flex; flex-direction: column; overflow: hidden; animation: cmdkPopIn 0.16s cubic-bezier(0.16, 1, 0.3, 1); }
  .cmdk-input-wrap { border-bottom: 1px solid var(--border, #334155); align-items: center; gap: 12px; padding: 14px 18px; display: flex; background: var(--bg-surface, #1e293b); }
  .cmdk-search-icon { color: var(--text-muted, #94a3b8); flex-shrink: 0; width: 18px; height: 18px; }
  .cmdk-input { font-family: inherit; color: var(--text, #f8fafc); background: transparent; border: 0; outline: 0; flex: 1; font-size: 15px; }
  .cmdk-input::placeholder { color: var(--text-muted, #94a3b8); }
  .cmdk-esc { font-family: inherit; color: var(--text-muted, #94a3b8); background: var(--bg-surface-secondary, #0f172a); border: 1px solid var(--border, #334155); border-radius: 4px; padding: 2px 7px; font-size: 11px; cursor: pointer; }
  .cmdk-list { flex: 1; padding: 8px; overflow-y: auto; max-height: 380px; }
  .cmdk-section { text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted, #94a3b8); padding: 10px 10px 4px; font-size: 11px; font-weight: 600; }
  .cmdk-item { border-radius: 8px; width: 100%; color: var(--text, #f8fafc); cursor: pointer; text-align: left; background: transparent; border: 0; align-items: center; gap: 12px; padding: 10px 12px; font-size: 13.5px; display: flex; transition: all 0.1s ease; }
  .cmdk-item:hover, .cmdk-item.active { background: var(--primary-lt, rgba(16, 185, 129, 0.15)); color: var(--primary, #10b981); }
  .cmdk-item-icon { background: var(--bg-surface-secondary, #0f172a); border-radius: 6px; width: 28px; height: 28px; color: var(--text-muted, #94a3b8); flex-shrink: 0; justify-content: center; align-items: center; display: flex; }
  .cmdk-item.active .cmdk-item-icon { color: var(--primary, #10b981); background: var(--primary-lt, rgba(16, 185, 129, 0.15)); }
  .cmdk-item-label { flex: 1; font-weight: 500; }
  .cmdk-item-kbd { color: var(--text-muted, #94a3b8); font-size: 11px; opacity: 0.7; }
  .cmdk-empty { text-align: center; color: var(--text-muted, #94a3b8); padding: 36px 16px; font-size: 13.5px; }
  .cmdk-footer { border-top: 1px solid var(--border, #334155); color: var(--text-muted, #94a3b8); justify-content: flex-end; gap: 16px; padding: 10px 18px; font-size: 11.5px; display: flex; background: var(--bg-surface-secondary, #0f172a); }
  .cmdk-footer kbd { background: var(--bg-surface, #1e293b); border: 1px solid var(--border, #334155); border-radius: 4px; margin-right: 4px; padding: 1px 5px; font-size: 10.5px; color: var(--text, #f8fafc); }
  .cmdk-open { overflow: hidden; }
  @keyframes cmdkFadeIn { from { opacity: 0; } to { opacity: 1; } }
  @keyframes cmdkPopIn { from { opacity: 0; transform: scale(0.97) translateY(-6px); } to { opacity: 1; transform: scale(1) translateY(0); } }
</style>
