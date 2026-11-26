import React, { useState } from 'react';
import { useOutletContext, Link } from 'react-router-dom';

export default function Settings() {
  const { currentUser } = useOutletContext();
  const [activeTab, setActiveTab] = useState('profile');
  const [dark, setDark] = useState(() => localStorage.getItem('theme') === 'dark');

  const [form, setForm] = useState({
    full_name: currentUser.full_name || '',
    username: currentUser.username || '',
    email: currentUser.email || '',
    mobile: currentUser.mobile || '',
    dob: currentUser.dob || '',
    gender: currentUser.gender || 'Male',
    area: currentUser.area || '',
    city: currentUser.city || '',
    state: currentUser.state || '',
    country: currentUser.country || 'India'
  });

  const [successMsg, setSuccessMsg] = useState('');
  const [errorMsg, setErrorMsg] = useState('');

  const userSports = Array.isArray(currentUser.sports) ? currentUser.sports : [];
  const initial = (form.full_name || 'U').charAt(0).toUpperCase();

  const toggleDark = () => {
    const next = !dark;
    setDark(next);
    if (next) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'default');
    }
  };

  const handleSave = async (e) => {
    e.preventDefault();
    setSuccessMsg('');
    setErrorMsg('');
    try {
      const res = await fetch(`/api/users/${currentUser.user_id}`, {
        method: 'PUT',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form)
      });
      const data = await res.json();
      if (data.success) {
        localStorage.setItem('teamup_user', JSON.stringify(data.user));
        setSuccessMsg('Settings updated successfully in MongoDB!');
      } else {
        setErrorMsg(data.message || 'Failed to update settings.');
      }
    } catch {
      setErrorMsg('Server error updating settings.');
    }
  };

  const inputClass =
    'w-full px-3.5 py-2.5 rounded-[10px] border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 text-sm outline-none focus:border-nd-green transition-colors';
  const labelClass = 'block text-[13.5px] font-semibold text-gray-900 dark:text-gray-100 mb-1.5';
  const tabBase =
    'px-[18px] py-3 text-[14.5px] font-semibold border-b-[3px] no-underline inline-flex items-center gap-2 whitespace-nowrap transition-colors bg-transparent border-t-0 border-l-0 border-r-0 cursor-pointer';
  const tabActive = 'text-nd-green border-nd-green';
  const tabIdle = 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-900 dark:hover:text-gray-100';

  return (
    <>
      <div className="mb-5 flex justify-between items-end flex-wrap gap-3.5">
        <div>
          <h2 className="m-0 mb-1.5 text-[26px] text-gray-900 dark:text-gray-50 font-extrabold">Account Settings</h2>
          <p className="m-0 text-gray-500 dark:text-gray-400 text-[14.5px]">
            Manage your profile, sports skills, neighborhood location, and security preferences.
          </p>
        </div>
        <Link
          to="/profile"
          className="inline-flex items-center gap-1.5 px-4 py-2 rounded-full font-semibold text-[13.5px] border border-nd-green text-nd-green hover:bg-nd-green hover:text-white no-underline transition-colors"
        >
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
          </svg>
          <span>View Public Profile</span>
        </Link>
      </div>

      {successMsg && (
        <div className="px-4 py-3 rounded-xl text-sm font-medium mb-5 flex items-center gap-2.5 bg-nd-green/15 text-nd-green border border-nd-green/30">
          <span>{successMsg}</span>
        </div>
      )}

      {errorMsg && (
        <div className="px-4 py-3 rounded-xl text-sm font-medium mb-5 flex items-center gap-2.5 bg-red-500/15 text-red-500 border border-red-500/30">
          <span>{errorMsg}</span>
        </div>
      )}

      {/* Tabs Navigation */}
      <div className="flex gap-2 border-b border-gray-200 dark:border-gray-700 mb-6 overflow-x-auto">
        <button type="button" onClick={() => setActiveTab('profile')} className={`${tabBase} ${activeTab === 'profile' ? tabActive : tabIdle}`}>
          <span>Profile Info</span>
        </button>
        <button type="button" onClick={() => setActiveTab('sports')} className={`${tabBase} ${activeTab === 'sports' ? tabActive : tabIdle}`}>
          <span>My Sports ({userSports.length})</span>
        </button>
        <button type="button" onClick={() => setActiveTab('location')} className={`${tabBase} ${activeTab === 'location' ? tabActive : tabIdle}`}>
          <span>Neighborhood &bull; Location</span>
        </button>
        <button type="button" onClick={() => setActiveTab('security')} className={`${tabBase} ${activeTab === 'security' ? tabActive : tabIdle}`}>
          <span>Security &bull; Appearance</span>
        </button>
      </div>

      {/* TAB 1: PROFILE INFO */}
      {activeTab === 'profile' && (
        <div className="grid grid-cols-1 lg:grid-cols-[2fr_1.2fr] gap-6 items-start">
          <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-6 shadow-sm">
            <h3 className="m-0 mb-4 text-gray-900 dark:text-gray-50 text-[19px] font-bold">Edit Profile Information</h3>

            <form onSubmit={handleSave}>
              <div className="flex items-center gap-5 mb-6 pb-5 border-b border-gray-200 dark:border-gray-700 flex-wrap">
                <div
                  className="w-[84px] h-[84px] rounded-full flex items-center justify-center text-[32px] font-extrabold text-white shrink-0"
                  style={{ background: 'linear-gradient(135deg, #10b981, #059669)' }}
                >
                  {initial}
                </div>
                <div className="flex-1 min-w-[200px]">
                  <div className={labelClass}>{form.full_name}</div>
                  <span className="text-xs text-gray-500 dark:text-gray-400 block">@{form.username}</span>
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="mb-5">
                  <label className={labelClass}>Full Name *</label>
                  <input type="text" className={inputClass} value={form.full_name} onChange={(e) => setForm({ ...form, full_name: e.target.value })} required />
                </div>
                <div className="mb-5">
                  <label className={labelClass}>Username (@handle) *</label>
                  <input type="text" className={inputClass} value={form.username} disabled />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="mb-5">
                  <label className={labelClass}>Mobile Number</label>
                  <input type="tel" className={inputClass} value={form.mobile} onChange={(e) => setForm({ ...form, mobile: e.target.value })} />
                </div>
                <div className="mb-5">
                  <label className={labelClass}>Date of Birth</label>
                  <input type="date" className={inputClass} value={form.dob} onChange={(e) => setForm({ ...form, dob: e.target.value })} />
                </div>
              </div>

              <button type="submit" className="px-6 py-2.5 rounded-full bg-nd-green hover:bg-nd-green-hover text-white font-semibold text-sm border-0 cursor-pointer">
                Save Profile Changes
              </button>
            </form>
          </div>

          <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-6 shadow-sm space-y-4">
            <h3 className="m-0 text-gray-900 dark:text-gray-50 text-[17px] font-bold">Account Overview</h3>
            <div className="text-sm text-gray-500 space-y-2">
              <div className="flex justify-between"><span>Status:</span><span className="text-nd-green font-semibold">Active</span></div>
              <div className="flex justify-between"><span>Email:</span><span className="font-medium text-gray-900 dark:text-gray-100">{form.email}</span></div>
              <div className="flex justify-between"><span>Database:</span><span className="font-medium text-gray-900 dark:text-gray-100">teamup_user_db</span></div>
            </div>
          </div>
        </div>
      )}

      {/* TAB 2: MY SPORTS */}
      {activeTab === 'sports' && (
        <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-6 shadow-sm">
          <h3 className="m-0 mb-4 text-gray-900 dark:text-gray-50 text-[19px] font-bold">My Sports ({userSports.length})</h3>
          {userSports.length === 0 ? (
            <p className="text-sm text-gray-500 m-0">No sports registered yet.</p>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
              {userSports.map((s, idx) => (
                <div key={idx} className="p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                  <span className="font-semibold text-sm text-gray-900 dark:text-gray-50">{s.sport_name}</span>
                  <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-nd-green/15 text-nd-green">
                    {s.skill_level || 'Intermediate'}
                  </span>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* TAB 3: LOCATION */}
      {activeTab === 'location' && (
        <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-6 shadow-sm max-w-[680px]">
          <h3 className="m-0 mb-4 text-gray-900 dark:text-gray-50 text-[19px] font-bold">Neighborhood &amp; Location</h3>
          <form onSubmit={handleSave} className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className={labelClass}>Area / Neighborhood</label>
                <input type="text" className={inputClass} value={form.area} onChange={(e) => setForm({ ...form, area: e.target.value })} />
              </div>
              <div>
                <label className={labelClass}>City</label>
                <input type="text" className={inputClass} value={form.city} onChange={(e) => setForm({ ...form, city: e.target.value })} />
              </div>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className={labelClass}>State</label>
                <input type="text" className={inputClass} value={form.state} onChange={(e) => setForm({ ...form, state: e.target.value })} />
              </div>
              <div>
                <label className={labelClass}>Country</label>
                <input type="text" className={inputClass} value={form.country} onChange={(e) => setForm({ ...form, country: e.target.value })} />
              </div>
            </div>
            <button type="submit" className="px-6 py-2.5 rounded-full bg-nd-green hover:bg-nd-green-hover text-white font-semibold text-sm border-0 cursor-pointer">
              Update Location
            </button>
          </form>
        </div>
      )}

      {/* TAB 4: SECURITY & APPEARANCE */}
      {activeTab === 'security' && (
        <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] p-6 shadow-sm max-w-[680px] flex items-center justify-between">
          <div>
            <div className="font-semibold text-base text-gray-900 dark:text-gray-50">Dark Mode Appearance</div>
            <div className="text-xs text-gray-500 mt-1">Toggle between light and dark theme across UserPanel</div>
          </div>
          <button
            type="button"
            onClick={toggleDark}
            className="px-5 py-2.5 rounded-full bg-nd-green hover:bg-nd-green-hover text-white text-sm font-semibold border-0 cursor-pointer"
          >
            {dark ? 'Switch to Light Mode' : 'Switch to Dark Mode'}
          </button>
        </div>
      )}
    </>
  );
}
