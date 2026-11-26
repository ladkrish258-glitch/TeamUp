import React, { useState, useEffect } from 'react';
import { Outlet, NavLink, Link, useLocation } from 'react-router-dom';

export default function Layout() {
  const [dark, setDark] = useState(() => localStorage.getItem('theme') === 'dark');
  const [searchQuery, setSearchQuery] = useState('');
  const location = useLocation();

  const [currentUser, setCurrentUser] = useState(() => {
    try {
      const saved = JSON.parse(localStorage.getItem('teamup_user') || 'null');
      if (saved) return saved;
    } catch {}
    return {
      user_id: 1,
      full_name: 'Arjun Patel',
      username: 'arjun_p',
      email: 'arjun@example.com',
      city: 'Surat',
      state: 'Gujarat',
      area: 'Adajan'
    };
  });

  useEffect(() => {
    fetch('/api/user-auth/session', { credentials: 'include' })
      .then((res) => res.json())
      .then((data) => {
        if (data && data.authenticated && data.user) {
          setCurrentUser(data.user);
          localStorage.setItem('teamup_user', JSON.stringify(data.user));
        }
      })
      .catch(() => {});
  }, []);

  const userInitial = (currentUser.full_name || 'U').charAt(0).toUpperCase();

  useEffect(() => {
    if (dark) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'default');
    }
  }, [dark]);

  const navBase = 'flex items-center gap-3 px-4 py-3 rounded-lg font-medium mb-1 no-underline transition-colors';
  const navActive = 'bg-[#e8f3ec] dark:bg-emerald-950/60 text-nd-green dark:text-emerald-400';
  const navIdle = 'text-gray-900 dark:text-gray-100 hover:bg-[#e8f3ec] dark:hover:bg-gray-700/60 hover:text-nd-green dark:hover:text-emerald-400';

  const widthClass =
    ['/', '/home', '/directory'].includes(location.pathname)
      ? 'max-w-[1140px]'
      : ['/profile', '/settings'].includes(location.pathname)
        ? 'max-w-[1100px]'
        : 'max-w-[680px]';

  const handleLogout = async () => {
    await fetch('/api/user-auth/logout', { method: 'POST', credentials: 'include' }).catch(() => {});
    localStorage.removeItem('teamup_user');
    window.location.href = 'http://localhost:5174/login';
  };

  return (
    <>
      {/* TOP NAVBAR */}
      <nav className="fixed top-0 left-0 right-0 h-[60px] bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 z-[1000]">
        <Link to="/" className="flex items-center gap-2 font-bold text-xl text-nd-green no-underline">
          <svg className="w-8 h-8" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
            <circle cx="32" cy="14" r="7" fill="#F4B400" />
            <circle cx="18" cy="26" r="5" fill="#1E88E5" />
            <path d="M18 31 L12 46 L18 42 L24 46 L18 31Z" fill="#1E88E5" />
            <circle cx="46" cy="26" r="5" fill="#43A047" />
            <path d="M46 31 L40 46 L46 42 L52 46 L46 31Z" fill="#43A047" />
            <path d="M23 28 Q32 36 41 28" stroke="#F4B400" strokeWidth="3" fill="none" />
          </svg>
          <span>TeamUp</span>
        </Link>

        <div className="hidden sm:block flex-1 max-w-[400px] mx-6 relative">
          <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 fill-gray-500 dark:fill-gray-400" viewBox="0 0 24 24">
            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
          </svg>
          <input
            type="text"
            placeholder="Search neighbors, sports, or matches..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full py-2.5 pr-4 pl-10 rounded-full border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 text-sm outline-none focus:border-nd-green focus:bg-white dark:focus:bg-gray-800 transition-colors"
          />
        </div>

        <div className="flex items-center gap-4">
          <button
            type="button"
            onClick={() => setDark(!dark)}
            title="Toggle theme"
            className="w-10 h-10 rounded-full flex items-center justify-center cursor-pointer bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors"
          >
            {!dark ? (
              <svg className="w-5 h-5 fill-gray-900" viewBox="0 0 24 24">
                <path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41L5.99 4.58zm12.37 12.37c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41l-1.06-1.06zm1.06-10.96c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41.39.39 1.03.39 1.41 0l1.06-1.06zM7.05 18.36c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41.39.39 1.03.39 1.41 0l1.06-1.06z" />
              </svg>
            ) : (
              <svg className="w-5 h-5 fill-gray-50" viewBox="0 0 24 24">
                <path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z" />
              </svg>
            )}
          </button>
          <Link
            to="/profile"
            className="w-10 h-10 rounded-full bg-nd-green text-white flex items-center justify-center font-semibold text-base no-underline hover:bg-nd-green-hover transition-colors"
          >
            {userInitial}
          </Link>
        </div>
      </nav>

      {/* LEFT SIDEBAR */}
      <aside className="hidden md:flex flex-col fixed top-[60px] left-0 w-[240px] h-[calc(100vh-60px)] bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 px-3.5 py-5 overflow-y-auto z-[900] box-border">
        <NavLink to="/" end className={({ isActive }) => `${navBase} ${isActive || location.pathname === '/home' ? navActive : navIdle}`}>
          <svg className="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" /></svg>
          <span>Home</span>
        </NavLink>
        <NavLink to="/directory" className={({ isActive }) => `${navBase} ${isActive ? navActive : navIdle}`}>
          <svg className="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" /></svg>
          <span>Neighbors</span>
        </NavLink>
        <NavLink to="/post" className={({ isActive }) => `${navBase} ${isActive ? navActive : navIdle}`}>
          <svg className="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 11h-5v5h-2v-5H6v-2h5V7h2v5h5v2z" /></svg>
          <span>Post</span>
        </NavLink>
        <NavLink to="/profile" className={({ isActive }) => `${navBase} ${isActive ? navActive : navIdle}`}>
          <svg className="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
          <span>Profile</span>
        </NavLink>
        <NavLink to="/settings" className={({ isActive }) => `${navBase} ${isActive ? navActive : navIdle}`}>
          <svg className="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .43-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.49-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" /></svg>
          <span>Settings</span>
        </NavLink>

        <div className="mt-auto">
          <button
            type="button"
            onClick={handleLogout}
            className="w-full flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 bg-transparent border-0 cursor-pointer transition-colors"
          >
            <svg className="w-6 h-6 fill-red-500" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" /></svg>
            <span>Log Out</span>
          </button>
        </div>
      </aside>

      {/* MAIN CONTAINER */}
      <div className="md:ml-[240px] mt-[80px] mb-16 md:mb-8 px-4 md:px-7">
        <div className={`w-full ${widthClass} mx-auto flex flex-col gap-4`}>
          <Outlet context={{ currentUser, searchQuery }} />
        </div>
      </div>
    </>
  );
}
