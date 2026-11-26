import React, { useState, useEffect } from 'react';
import { useOutletContext, Link } from 'react-router-dom';

export default function Profile() {
  const { currentUser } = useOutletContext();
  const [profileData, setProfileData] = useState(currentUser);
  const [hostedMatches, setHostedMatches] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`/api/users/${currentUser.user_id}/profile`, { credentials: 'include' })
      .then((res) => res.json())
      .then((data) => {
        if (data && data.success) {
          setProfileData(data.user || currentUser);
          setHostedMatches(data.hostedMatches || []);
        }
      })
      .catch(console.error)
      .finally(() => setLoading(false));
  }, [currentUser.user_id]);

  const initial = (profileData.full_name || 'U').charAt(0).toUpperCase();
  const userSports = Array.isArray(profileData.sports) ? profileData.sports : [];

  let topSkill = 'Active';
  const skills = userSports.map((s) => s.skill_level);
  if (skills.includes('Professional')) topSkill = 'Professional';
  else if (skills.includes('Advanced')) topSkill = 'Advanced';
  else if (skills.includes('Intermediate')) topSkill = 'Intermediate';
  else if (skills.includes('Beginner')) topSkill = 'Beginner';

  return (
    <>
      {/* Exact PHP Profile Hero Card */}
      <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-[18px] overflow-hidden mb-6 shadow-md">
        <div
          className="h-[180px] relative flex items-start justify-end px-5 py-4"
          style={{ background: 'linear-gradient(135deg, #064e3b 0%, #047857 45%, #1e3a5f 100%)' }}
        >
          <div className="bg-black/35 backdrop-blur-md text-slate-200 text-xs font-semibold px-3.5 py-1.5 rounded-full border border-white/15 inline-flex items-center gap-1.5">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="#10b981">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
            </svg>
            <span>Verified Athlete &bull; TeamUp</span>
          </div>
        </div>

        <div className="px-7 pb-6 relative">
          <div className="relative w-[110px] h-[110px] -mt-[55px] mb-4">
            <div
              className="w-[110px] h-[110px] rounded-full border-4 border-white dark:border-gray-800 shadow-lg flex items-center justify-center text-[42px] font-extrabold text-white"
              style={{ background: 'linear-gradient(135deg, #10b981, #059669)' }}
            >
              {initial}
            </div>
            <Link
              to="/settings"
              title="Change profile settings"
              className="absolute bottom-0.5 right-0.5 w-8 h-8 rounded-full bg-nd-green text-white flex items-center justify-center border-[3px] border-white dark:border-gray-800 shadow hover:scale-110 transition-transform no-underline"
            >
              <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
              </svg>
            </Link>
          </div>

          <div className="flex justify-between items-start flex-wrap gap-4">
            <div>
              <div className="flex items-center gap-2 flex-wrap">
                <h1 className="m-0 text-[26px] font-extrabold text-gray-900 dark:text-gray-50 tracking-tight">
                  {profileData.full_name}
                </h1>
                <span className="inline-flex items-center justify-center w-5 h-5 rounded-full bg-nd-green text-white" title="Verified Profile">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                </span>
              </div>

              <div className="flex items-center gap-3 mt-1.5 text-sm text-gray-500 dark:text-gray-400 flex-wrap">
                <span className="font-semibold text-gray-900 dark:text-gray-100">@{profileData.username}</span>
                <span>&bull;</span>
                <span className="inline-flex items-center gap-1">
                  <svg viewBox="0 0 24 24" width="14" height="14" className="fill-nd-green">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                  </svg>
                  {profileData.area || 'Local'}{profileData.city ? ` (${profileData.city})` : ''}
                </span>
                <span>&bull;</span>
                <span>
                  Member since{' '}
                  {profileData.created_at
                    ? new Date(profileData.created_at).toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
                    : 'Recent'}
                </span>
              </div>
            </div>

            <div className="flex gap-2.5 flex-wrap">
              <Link
                to="/settings"
                className="inline-flex items-center gap-1.5 text-[13.5px] px-[18px] py-2 rounded-full font-semibold bg-nd-green hover:bg-nd-green-hover text-white no-underline transition-colors"
              >
                <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor">
                  <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                </svg>
                <span>Edit Profile</span>
              </Link>
              <Link
                to="/post"
                className="inline-flex items-center gap-1.5 text-[13.5px] px-[18px] py-2 rounded-full font-semibold bg-transparent text-nd-green border border-nd-green hover:bg-nd-green hover:text-white no-underline transition-colors"
              >
                <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor">
                  <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                </svg>
                <span>Host a Match</span>
              </Link>
            </div>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6 pt-5 border-t border-gray-200 dark:border-gray-700">
            <div className="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-3 text-center">
              <div className="text-xl font-extrabold text-gray-900 dark:text-gray-50">{hostedMatches.length}</div>
              <div className="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Matches Hosted</div>
            </div>
            <div className="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-3 text-center">
              <div className="text-xl font-extrabold text-gray-900 dark:text-gray-50">{userSports.length}</div>
              <div className="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Active Sports</div>
            </div>
            <div className="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-3 text-center">
              <div className="text-xl font-extrabold text-nd-green">{topSkill}</div>
              <div className="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Top Skill Level</div>
            </div>
            <div className="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-3 text-center">
              <div className="text-base font-extrabold text-gray-900 dark:text-gray-50">{profileData.area || profileData.city || 'Surat'}</div>
              <div className="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Home Neighborhood</div>
            </div>
          </div>
        </div>
      </div>

      {/* Sports & Hosted Matches Sections */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm">
          <h3 className="text-base font-bold m-0 mb-4 text-gray-900 dark:text-gray-50">My Sports &amp; Skills</h3>
          {userSports.length === 0 ? (
            <p className="text-sm text-gray-500 m-0">No sports listed yet.</p>
          ) : (
            <div className="space-y-2.5">
              {userSports.map((s, idx) => (
                <div key={idx} className="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                  <span className="font-semibold text-sm text-gray-900 dark:text-gray-50">{s.sport_name}</span>
                  <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-nd-green/15 text-nd-green">
                    {s.skill_level || 'Intermediate'}
                  </span>
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm">
          <h3 className="text-base font-bold m-0 mb-4 text-gray-900 dark:text-gray-50">Hosted Matches ({hostedMatches.length})</h3>
          {loading ? (
            <p className="text-sm text-gray-500 m-0">Loading...</p>
          ) : hostedMatches.length === 0 ? (
            <p className="text-sm text-gray-500 m-0">You haven't hosted any matches yet.</p>
          ) : (
            <div className="space-y-2.5">
              {hostedMatches.slice(0, 5).map((m) => (
                <div key={m.match_id} className="p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                  <div className="font-semibold text-sm text-gray-900 dark:text-gray-50">{m.title}</div>
                  <div className="text-xs text-gray-500 mt-0.5">
                    {m.sport_name} &bull; {m.ground_name} &bull; {m.match_status}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </>
  );
}
