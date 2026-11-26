import React, { useState, useEffect } from 'react';
import { useOutletContext, Link } from 'react-router-dom';

export default function Home() {
  const { currentUser, searchQuery } = useOutletContext();
  const [matches, setMatches] = useState([]);
  const [joinedIds, setJoinedIds] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const perPage = 10;

  // Joined Players Modal
  const [modalMatch, setModalMatch] = useState(null);
  const [modalPlayers, setModalPlayers] = useState([]);
  const [loadingPlayers, setLoadingPlayers] = useState(false);

  useEffect(() => {
    fetch(`/api/feed?user_id=${currentUser.user_id}`, { credentials: 'include' })
      .then((res) => res.json())
      .then((data) => {
        if (data && data.success) {
          setMatches(data.matches || []);
          setJoinedIds(data.joinedIds || []);
        }
      })
      .catch(console.error)
      .finally(() => setLoading(false));
  }, [currentUser.user_id]);

  const handleJoinToggle = async (matchId) => {
    const res = await fetch(`/api/matches/${matchId}/join`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: currentUser.user_id })
    });
    const data = await res.json();
    if (data.success) {
      if (data.joined) {
        setJoinedIds((prev) => [...prev, matchId]);
        setMatches((prev) =>
          prev.map((m) => (m.match_id === matchId ? { ...m, current_players: (m.current_players || 0) + 1 } : m))
        );
      } else {
        setJoinedIds((prev) => prev.filter((id) => id !== matchId));
        setMatches((prev) =>
          prev.map((m) => (m.match_id === matchId ? { ...m, current_players: Math.max(0, (m.current_players || 1) - 1) } : m))
        );
      }
    }
  };

  const openJoinedPlayersModal = async (match) => {
    setModalMatch(match);
    setLoadingPlayers(true);
    try {
      const res = await fetch(`/api/matches/${match.match_id}/participants`, { credentials: 'include' });
      const data = await res.json();
      if (data && data.success) {
        setModalPlayers(data.participants || []);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoadingPlayers(false);
    }
  };

  const getSportIcon = (sportName = '') => {
    const s = sportName.toLowerCase();
    if (s.includes('cricket')) return '🏏';
    if (s.includes('football') || s.includes('soccer')) return '⚽';
    if (s.includes('basket')) return '🏀';
    if (s.includes('badminton')) return '🏸';
    if (s.includes('tennis')) return '🎾';
    if (s.includes('volley')) return '🏐';
    return '🏆';
  };

  const filtered = matches.filter((m) => {
    if (!searchQuery) return true;
    const q = searchQuery.toLowerCase();
    return (
      (m.title || '').toLowerCase().includes(q) ||
      (m.sport_name || '').toLowerCase().includes(q) ||
      (m.ground_name || '').toLowerCase().includes(q) ||
      (m.creator_name || '').toLowerCase().includes(q)
    );
  });

  const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
  const startIdx = (page - 1) * perPage;
  const paged = filtered.slice(startIdx, startIdx + perPage);

  return (
    <>
      <div className="mb-2.5 flex justify-between items-end flex-wrap gap-3">
        <div>
          <h2 className="m-0 mb-1 text-2xl font-bold text-gray-900 dark:text-gray-50">Home Feed</h2>
          <p className="m-0 text-sm text-gray-500 dark:text-gray-400">
            Recent match invites and updates from neighbors nearby.
          </p>
        </div>

        <div className="flex items-center gap-2.5 flex-wrap">
          <div className="text-[13px] text-gray-500 dark:text-gray-400 font-medium">
            Showing {filtered.length > 0 ? `${startIdx + 1}–${startIdx + paged.length}` : '0'} of {filtered.length}
          </div>
        </div>
      </div>

      {loading ? (
        <div className="text-center py-12 text-gray-500">Loading matches from MongoDB...</div>
      ) : paged.length === 0 ? (
        <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-center px-6 py-12 rounded-2xl">
          <div className="text-4xl mb-3">🏟️</div>
          <h3 className="text-gray-900 dark:text-gray-50 m-0 mb-2 text-lg font-bold">No matches found</h3>
          <p className="text-gray-500 dark:text-gray-400 text-sm m-0 mb-5">Be the first neighbor to post a new game!</p>
          <Link to="/post" className="inline-flex items-center px-5 py-2.5 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white no-underline transition-colors">
            Create a Post
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-5 items-stretch">
          {paged.map((item) => {
            const isJoined = joinedIds.includes(item.match_id);
            const isOpen = (item.match_status || 'Open') === 'Open';
            const initial = (item.creator_name || 'U').charAt(0).toUpperCase();
            const currentPlayers = item.current_players ?? 0;
            const maxPlayers = item.max_players || 10;
            const slotsRemaining = Math.max(0, maxPlayers - currentPlayers);

            return (
              <article
                key={item.match_id}
                className="nd-feed-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-[22px] shadow-sm dark:shadow-lg transition-all duration-200 hover:border-nd-green/50 flex flex-col h-full"
              >
                {/* Card Header */}
                <div className="flex justify-between items-start mb-3 gap-3">
                  <div className="flex items-center gap-3">
                    <div
                      className="w-11 h-11 rounded-full flex items-center justify-center text-white font-semibold text-[17px] shrink-0"
                      style={{ background: 'linear-gradient(135deg, #10b981, #059669)' }}
                    >
                      {initial}
                    </div>
                    <div>
                      <div className="font-semibold text-[15px] text-gray-900 dark:text-gray-50 leading-snug">
                        {item.creator_name || 'Community Member'}
                      </div>
                      <div className="text-[12.5px] text-gray-500 dark:text-gray-400 mt-0.5">
                        {new Date(item.created_at || Date.now()).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                      </div>
                    </div>
                  </div>

                  <span
                    className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold tracking-wide shrink-0 border ${
                      isOpen
                        ? 'bg-emerald-500/15 text-emerald-500 border-emerald-500/30'
                        : 'bg-gray-400/15 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700'
                    }`}
                  >
                    <span className="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {item.match_status || 'Open'}
                  </span>
                </div>

                {/* Description */}
                {item.description && (
                  <div className="text-[15px] leading-relaxed mb-3.5 text-gray-900 dark:text-gray-100 break-words">
                    {item.description}
                  </div>
                )}

                {/* Exact PHP Match Details Card Box */}
                <div className="nd-match-box mt-auto bg-black/[0.025] dark:bg-gray-900/70 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                  <div className="flex items-center justify-between flex-wrap gap-2.5 mb-3.5">
                    <div className="flex items-center gap-2 flex-wrap">
                      <span className="inline-flex items-center gap-1.5 bg-nd-green/15 text-emerald-500 border border-nd-green/30 px-3 py-1 rounded-lg text-[13px] font-semibold">
                        {getSportIcon(item.sport_name)} {item.sport_name || 'Sport'}
                      </span>
                      {item.skill_level && (
                        <span className="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 px-2.5 py-1 rounded-lg text-xs font-medium">
                          {item.skill_level}
                        </span>
                      )}
                    </div>

                    <div className="nd-players-pill inline-flex items-center gap-1.5 text-[13px] font-semibold text-gray-500 dark:text-gray-400">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                      </svg>
                      <span>{currentPlayers} / {maxPlayers} Players</span>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div className="flex items-start gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2.5 rounded-lg">
                      <div className="text-base leading-none mt-0.5">📅</div>
                      <div>
                        <div className="text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Date</div>
                        <div className="text-[13.5px] font-semibold text-gray-900 dark:text-gray-50 mt-0.5">
                          {new Date(item.match_date || Date.now()).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}
                        </div>
                      </div>
                    </div>

                    <div className="flex items-start gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2.5 rounded-lg">
                      <div className="text-base leading-none mt-0.5">⏰</div>
                      <div>
                        <div className="text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Time</div>
                        <div className="text-[13.5px] font-semibold text-gray-900 dark:text-gray-50 mt-0.5">
                          {item.start_time || '6:00 PM'} &ndash; {item.end_time || '8:00 PM'}
                        </div>
                      </div>
                    </div>

                    <div className="sm:col-span-2 flex items-start gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2.5 rounded-lg">
                      <div className="text-base leading-none mt-0.5">📍</div>
                      <div>
                        <div className="text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Ground / Venue</div>
                        <div className="text-[13.5px] font-semibold text-gray-900 dark:text-gray-50 mt-0.5">
                          {item.ground_name || 'Local Ground'}{item.city ? ` • ${item.city}` : ''}
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Actions Row */}
                  <div className="mt-3.5 flex items-center justify-between flex-wrap gap-2.5">
                    <div className="flex items-center gap-2.5 flex-wrap">
                      {isJoined ? (
                        <button
                          type="button"
                          onClick={() => handleJoinToggle(item.match_id)}
                          className="nd-btn-joined inline-flex items-center px-4 py-2 rounded-full font-semibold text-sm border border-emerald-500 text-emerald-500 hover:border-red-500 hover:text-red-500 hover:bg-red-500/10 bg-transparent cursor-pointer transition-colors group"
                          title="Click to leave match"
                        >
                          <span className="group-hover:hidden">Joined ✓</span>
                          <span className="hidden group-hover:inline">Leave Match &times;</span>
                        </button>
                      ) : isOpen ? (
                        <button
                          type="button"
                          onClick={() => handleJoinToggle(item.match_id)}
                          className="nd-btn-join inline-flex items-center px-4 py-2 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors"
                        >
                          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" className="mr-1">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                          </svg>
                          <span>Interested to Play</span>
                        </button>
                      ) : (
                        <button
                          type="button"
                          disabled
                          className="inline-flex items-center px-4 py-2 rounded-full font-semibold text-sm border border-gray-300 dark:border-gray-600 text-gray-500 bg-transparent opacity-60 cursor-not-allowed"
                        >
                          {item.match_status}
                        </button>
                      )}

                      <button
                        type="button"
                        onClick={() => openJoinedPlayersModal(item)}
                        className="nd-btn-players inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 px-4 py-2 rounded-full font-semibold text-[13.5px] cursor-pointer transition-colors"
                      >
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                          <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                        </svg>
                        <span>Joined Players</span>
                      </button>
                    </div>

                    <span className="text-[13px] text-gray-500 dark:text-gray-400">
                      {slotsRemaining} slots remaining
                    </span>
                  </div>
                </div>
              </article>
            );
          })}
        </div>
      )}

      {totalPages > 1 && (
        <div className="flex items-center justify-center gap-1.5 my-6 flex-wrap">
          <button
            type="button"
            disabled={page === 1}
            onClick={() => setPage(page - 1)}
            className="min-w-[38px] h-[38px] px-3 rounded-[10px] border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-semibold disabled:opacity-40 cursor-pointer"
          >
            &laquo; Prev
          </button>
          <span className="px-3 text-sm text-gray-500">
            Page {page} of {totalPages}
          </span>
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

      {/* Joined Players Modal */}
      {modalMatch && (
        <div
          className="fixed inset-0 bg-black/65 backdrop-blur-sm z-[2000] flex items-center justify-center p-4"
          onClick={() => setModalMatch(null)}
        >
          <div
            className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl w-full max-w-[460px] max-h-[82vh] flex flex-col shadow-2xl overflow-hidden"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 className="m-0 text-base font-bold text-gray-900 dark:text-gray-50 flex items-center gap-2">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                  <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                </svg>
                <span>{modalMatch.title} — Players</span>
              </h3>
              <button
                type="button"
                onClick={() => setModalMatch(null)}
                className="bg-transparent border-0 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 text-2xl leading-none cursor-pointer px-2 py-1 rounded-lg"
              >
                &times;
              </button>
            </div>
            <div className="p-4 px-5 overflow-y-auto flex-1 space-y-2.5">
              {loadingPlayers ? (
                <p className="text-center text-sm text-gray-500 py-6">Loading players...</p>
              ) : modalPlayers.length === 0 ? (
                <p className="text-center text-sm text-gray-500 py-6">No players joined yet.</p>
              ) : (
                modalPlayers.map((p) => (
                  <div
                    key={p.participant_id}
                    className="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700"
                  >
                    <div className="flex items-center gap-3">
                      <div className="w-9 h-9 rounded-full bg-nd-green text-white font-bold text-sm flex items-center justify-center">
                        {(p.full_name || 'P').charAt(0).toUpperCase()}
                      </div>
                      <div>
                        <div className="font-semibold text-sm text-gray-900 dark:text-gray-50">{p.full_name}</div>
                        <div className="text-xs text-gray-500">
                          Joined {new Date(p.joined_at || Date.now()).toLocaleDateString()}
                        </div>
                      </div>
                    </div>
                    <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-500">
                      {p.status || 'Confirmed'}
                    </span>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      )}
    </>
  );
}
