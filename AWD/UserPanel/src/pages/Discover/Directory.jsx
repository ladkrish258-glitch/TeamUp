import React, { useState, useEffect } from 'react';
import { useOutletContext } from 'react-router-dom';

export default function Directory() {
  const { currentUser, searchQuery } = useOutletContext();
  const [neighbors, setNeighbors] = useState([]);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [selectedNeighbor, setSelectedNeighbor] = useState(null);
  const [drawerStats, setDrawerStats] = useState({ matchesCount: '-' });
  const perPage = 12;

  const gradients = [
    'linear-gradient(135deg, #10b981, #059669)',
    'linear-gradient(135deg, #3b82f6, #1d4ed8)',
    'linear-gradient(135deg, #8b5cf6, #6d28d9)',
    'linear-gradient(135deg, #f59e0b, #d97706)',
    'linear-gradient(135deg, #ec4899, #be185d)',
    'linear-gradient(135deg, #06b6d4, #0891b2)'
  ];

  useEffect(() => {
    fetch(`/api/neighbors?user_id=${currentUser.user_id}`, { credentials: 'include' })
      .then((res) => res.json())
      .then((data) => {
        if (data && data.success) setNeighbors(data.neighbors || []);
      })
      .catch(console.error);
  }, [currentUser.user_id]);

  const openProfileDrawer = (neighbor) => {
    setSelectedNeighbor(neighbor);
    setDrawerStats({ matchesCount: '-' });
    fetch(`/api/users/${neighbor.user_id}/profile`, { credentials: 'include' })
      .then((res) => res.json())
      .then((data) => {
        if (data && data.success) {
          setDrawerStats({ matchesCount: data.matchesCount || 0 });
        }
      })
      .catch(() => {});
  };

  const effectiveSearch = (search || searchQuery || '').toLowerCase();
  const filtered = neighbors.filter((n) => {
    if (!effectiveSearch) return true;
    const text = `${n.full_name} ${n.username} ${n.city} ${n.area} ${n.sports_list}`.toLowerCase();
    return text.includes(effectiveSearch);
  });

  const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
  const startIdx = (page - 1) * perPage;
  const paged = filtered.slice(startIdx, startIdx + perPage);

  return (
    <>
      <div className="mb-5 flex justify-between items-end flex-wrap gap-4">
        <div>
          <h2 className="m-0 mb-1.5 text-[26px] font-bold text-gray-900 dark:text-gray-50">
            Neighbors Directory
          </h2>
          <p className="m-0 text-gray-500 dark:text-gray-400 text-[14.5px]">
            Connect with other sports enthusiasts and players in your area.
          </p>
        </div>

        <div className="flex items-center gap-3 flex-wrap">
          <div className="text-[13.5px] text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-1.5 rounded-full">
            <span>{neighbors.length}</span> Neighbors in your network
          </div>
          <div className="text-[13px] font-semibold text-nd-green bg-nd-green/10 border border-nd-green/25 px-3.5 py-1.5 rounded-full">
            Grid View 3&times;4 (12 / page)
          </div>
        </div>
      </div>

      <div className="mb-6 flex justify-between items-center flex-wrap gap-3.5">
        <div className="relative flex-1 max-w-[520px] min-w-[280px]">
          <input
            type="text"
            placeholder="Search neighbors by name, handle, area, or sport..."
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
            className="w-full py-3 pr-4 pl-10 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 text-[14.5px] outline-none shadow-sm focus:border-nd-green transition-colors"
          />
          <svg viewBox="0 0 24 24" className="absolute left-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] fill-gray-500 dark:fill-gray-400">
            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
          </svg>
        </div>

        <div className="text-[13.5px] text-gray-500 dark:text-gray-400 font-medium">
          Showing {filtered.length > 0 ? `${startIdx + 1}–${startIdx + paged.length}` : '0'} of {filtered.length}
        </div>
      </div>

      {paged.length === 0 ? (
        <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center py-12 px-6 rounded-2xl mt-4">
          <div className="text-[38px] mb-2.5">🔍</div>
          <h3 className="m-0 mb-1.5 text-gray-900 dark:text-gray-50 font-bold text-lg">No neighbors found</h3>
          <p className="text-gray-500 dark:text-gray-400 text-sm m-0">Try searching with a different name, location, or sport.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-7">
          {paged.map((neighbor, index) => {
            const initial = (neighbor.full_name || 'U').charAt(0).toUpperCase();
            const grad = gradients[index % gradients.length];
            const sportsArr = neighbor.sports_list ? neighbor.sports_list.split(',').map((s) => s.trim()).filter(Boolean) : [];

            return (
              <div
                key={neighbor.user_id}
                className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl pt-6 px-4 pb-5 text-center flex flex-col transition-all duration-200 hover:-translate-y-1 hover:border-nd-green/45 hover:shadow-xl shadow-sm"
              >
                <div className="relative w-[72px] h-[72px] mx-auto mb-3">
                  <div
                    className="w-[72px] h-[72px] rounded-full flex items-center justify-center text-[26px] text-white font-bold shadow-md"
                    style={{ background: grad }}
                  >
                    {initial}
                  </div>
                </div>

                <div className="font-bold text-base text-gray-900 dark:text-gray-50 mb-0.5">
                  {neighbor.full_name}
                </div>
                <div className="text-[13px] text-gray-500 dark:text-gray-400 mb-2.5">
                  @{neighbor.username}
                </div>

                <div className="inline-flex items-center justify-center gap-1.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full text-xs text-gray-500 dark:text-gray-400 mb-3 mx-auto">
                  <svg className="w-3.5 h-3.5 fill-nd-green shrink-0" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                  </svg>
                  <span>{neighbor.area || 'Local'}{neighbor.city ? ` • ${neighbor.city}` : ''}</span>
                </div>

                <div className="flex flex-wrap justify-center gap-1.5 mb-4 min-h-[26px]">
                  {sportsArr.length > 0 ? (
                    <>
                      {sportsArr.slice(0, 2).map((sp) => (
                        <span key={sp} className="text-[11.5px] font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                          {sp}
                        </span>
                      ))}
                      {sportsArr.length > 2 && (
                        <span className="text-[11.5px] font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                          +{sportsArr.length - 2}
                        </span>
                      )}
                    </>
                  ) : (
                    <span className="text-[11.5px] font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 opacity-75">
                      Active Neighbor
                    </span>
                  )}
                </div>

                <button
                  type="button"
                  onClick={() => openProfileDrawer(neighbor)}
                  className="w-full mt-auto inline-flex items-center justify-center gap-1.5 bg-nd-green hover:bg-nd-green-hover text-white border-0 py-2 px-4 rounded-full font-semibold text-sm cursor-pointer transition-colors"
                >
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                  </svg>
                  <span>View Profile</span>
                </button>
              </div>
            );
          })}
        </div>
      )}

      {totalPages > 1 && (
        <div className="flex items-center justify-center gap-1.5 my-8 flex-wrap">
          <button
            type="button"
            disabled={page === 1}
            onClick={() => setPage(page - 1)}
            className="min-w-[38px] h-[38px] px-3 rounded-[10px] border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-semibold disabled:opacity-40 cursor-pointer"
          >
            &laquo; Prev
          </button>
          <span className="px-3 text-sm text-gray-500">Page {page} of {totalPages}</span>
          <button
            type="button"
            disabled={page === totalPages}
            onClick={() => setPage(page + 1)}
            className="min-w-[38px] h-[38px] px-3 rounded-[10px] border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-semibold disabled:opacity-40 cursor-pointer"
          >
            Next &raquo;
          </button>
        </div>
      )}

      {/* Exact PHP Right Slide-Over Profile Drawer */}
      {selectedNeighbor && (
        <>
          <div
            className="fixed inset-0 bg-black/55 backdrop-blur-[3px] z-[9998]"
            onClick={() => setSelectedNeighbor(null)}
          />
          <div className="fixed top-0 right-0 w-[440px] max-w-[92vw] h-screen bg-white dark:bg-gray-800 z-[9999] shadow-2xl flex flex-col overflow-hidden">
            <div className="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
              <div className="flex items-center gap-2">
                <svg viewBox="0 0 24 24" width="18" height="18" className="fill-nd-green">
                  <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                </svg>
                <span className="font-bold text-[15px] text-gray-900 dark:text-gray-50">Neighbor Profile</span>
              </div>
              <button
                type="button"
                onClick={() => setSelectedNeighbor(null)}
                className="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-50 w-[34px] h-[34px] rounded-full flex items-center justify-center cursor-pointer text-lg hover:bg-gray-200 dark:hover:bg-gray-700"
              >
                &times;
              </button>
            </div>

            <div className="flex-1 overflow-y-auto pb-8">
              <div className="h-[110px] w-full" style={{ background: 'linear-gradient(135deg, #1f8a55 0%, #176840 100%)' }} />

              <div className="px-5 pb-4 relative">
                <div className="relative w-[84px] h-[84px] -mt-[42px] mb-3">
                  <div
                    className="w-[84px] h-[84px] rounded-full border-[3px] border-white dark:border-gray-800 flex items-center justify-center text-[32px] font-bold text-white shadow-lg"
                    style={{ background: 'linear-gradient(135deg, #10b981, #059669)' }}
                  >
                    {(selectedNeighbor.full_name || 'U').charAt(0).toUpperCase()}
                  </div>
                </div>

                <div className="flex justify-between items-start gap-2.5 flex-wrap">
                  <div>
                    <h2 className="m-0 text-[22px] text-gray-900 dark:text-gray-50 font-bold">{selectedNeighbor.full_name}</h2>
                    <div className="text-[13.5px] text-gray-500 dark:text-gray-400 mt-0.5">@{selectedNeighbor.username}</div>
                  </div>
                  <span className="text-[11.5px] font-semibold px-2.5 py-1 rounded-xl bg-nd-green/10 text-nd-green border border-nd-green/25">
                    Active Player
                  </span>
                </div>

                <div className="flex items-center gap-1.5 mt-2.5 text-[13px] text-gray-500 dark:text-gray-400">
                  <svg viewBox="0 0 24 24" width="15" height="15" className="fill-nd-green">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                  </svg>
                  <span>{selectedNeighbor.area || 'Local'}{selectedNeighbor.city ? ` • ${selectedNeighbor.city}` : ''}</span>
                </div>
              </div>

              <div className="h-px bg-gray-200 dark:bg-gray-700 mx-5 mt-1.5 mb-4" />

              <div className="px-5 pb-4">
                <div className="grid grid-cols-2 gap-2.5">
                  <div className="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 rounded-xl text-center">
                    <div className="text-xl font-bold text-gray-900 dark:text-gray-50">{drawerStats.matchesCount}</div>
                    <div className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Matches Hosted</div>
                  </div>
                  <div className="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3 rounded-xl text-center">
                    <div className="text-xl font-bold text-gray-900 dark:text-gray-50">
                      {selectedNeighbor.created_at ? new Date(selectedNeighbor.created_at).getFullYear() : '2026'}
                    </div>
                    <div className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Member Since</div>
                  </div>
                </div>
              </div>

              <div className="px-5">
                <div className="font-bold text-[14.5px] text-gray-900 dark:text-gray-50 mb-3 flex items-center justify-between">
                  <span>Sports &amp; Experience</span>
                  <span className="text-xs text-gray-500 dark:text-gray-400 font-normal">
                    {Array.isArray(selectedNeighbor.sports) ? `${selectedNeighbor.sports.length} sports` : ''}
                  </span>
                </div>

                <div className="flex flex-col gap-2.5">
                  {Array.isArray(selectedNeighbor.sports) && selectedNeighbor.sports.length > 0 ? (
                    selectedNeighbor.sports.map((s, idx) => (
                      <div key={idx} className="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                        <span className="font-semibold text-sm text-gray-900 dark:text-gray-50">{s.sport_name}</span>
                        <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-nd-green/15 text-nd-green">
                          {s.skill_level || 'Intermediate'}
                        </span>
                      </div>
                    ))
                  ) : (
                    <div className="text-sm text-gray-500">All neighborhood sports</div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </>
      )}
    </>
  );
}
