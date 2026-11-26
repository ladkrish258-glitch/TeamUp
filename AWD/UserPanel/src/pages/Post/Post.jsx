import React, { useState, useEffect } from 'react';
import { useOutletContext, useNavigate } from 'react-router-dom';

export default function Post() {
  const { currentUser } = useOutletContext();
  const navigate = useNavigate();

  const [step, setStep] = useState(1);
  const [sports, setSports] = useState([]);
  const [grounds, setGrounds] = useState([]);
  const [previewImg, setPreviewImg] = useState('');
  const [previewName, setPreviewName] = useState('');

  const [form, setForm] = useState({
    content: '',
    sport_id: '',
    min_players: 2,
    max_players: 10,
    sport_description: '',
    skill_level: 'Intermediate',
    experience_years: 2,
    preferred_position: '',
    match_date: new Date().toISOString().split('T')[0],
    skill_level_match: 'Intermediate',
    start_time: '18:00',
    end_time: '20:00',
    visibility: 'Public',
    entry_fee: '0.00',
    ground_city: '',
    ground_id: ''
  });
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetch('/api/meta', { credentials: 'include' })
      .then((res) => res.json())
      .then((data) => {
        if (data && data.success) {
          setSports(data.sports || []);
          setGrounds(data.grounds || []);
        }
      })
      .catch(console.error);
  }, []);

  const cities = Array.from(new Set(grounds.map((g) => g.city).filter(Boolean))).sort();
  const filteredGrounds = grounds.filter((g) => !form.ground_city || g.city === form.ground_city);

  const selectedSport = sports.find((s) => String(s.sport_id) === String(form.sport_id));
  const selectedGround = grounds.find((g) => String(g.ground_id) === String(form.ground_id));

  const handlePhotoSelect = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    setPreviewName(file.name);
    const reader = new FileReader();
    reader.onload = (ev) => setPreviewImg(ev.target.result);
    reader.readAsDataURL(file);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    try {
      const title = selectedSport ? `${selectedSport.sport_name} Match` : 'Neighborhood Match';
      const res = await fetch('/api/posts', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...form,
          title,
          user_id: currentUser.user_id
        })
      });
      const data = await res.json();
      if (data.success) {
        navigate('/');
      }
    } finally {
      setSaving(false);
    }
  };

  const pInput =
    'w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50 text-sm outline-none focus:border-nd-green transition-colors disabled:opacity-50 disabled:cursor-not-allowed';
  const pLabel = 'block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1.5';
  const pBtnPrimary =
    'inline-flex items-center justify-center px-6 py-2.5 rounded-full font-semibold text-sm bg-nd-green hover:bg-nd-green-hover text-white border-0 cursor-pointer transition-colors';
  const pBtnBack =
    'inline-flex items-center justify-center px-5 py-2.5 rounded-full font-semibold text-sm bg-transparent border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-colors';

  return (
    <form
      onSubmit={handleSubmit}
      className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm flex flex-col gap-3 mb-6"
    >
      {/* STEP 1: Post Details */}
      {step === 1 && (
        <div>
          <h4 className="font-bold text-xl mb-4 text-center text-gray-900 dark:text-gray-50">Post Details</h4>

          <div className="mb-4">
            <label htmlFor="post_content" className={pLabel}>Content:</label>
            <textarea
              id="post_content"
              className={`${pInput} resize-y min-h-[88px]`}
              placeholder={`What's on your mind, ${currentUser.full_name}? Ask for a match, share an update...`}
              rows="3"
              value={form.content}
              onChange={(e) => setForm({ ...form, content: e.target.value })}
            />
          </div>

          <div className="mb-4">
            <label className={pLabel}>Upload Photo</label>
            <p className="text-xs text-gray-500 dark:text-gray-400 m-0 mb-2">
              Drag &amp; drop or click to browse &middot; Max 10 MB
            </p>

            <label className="block border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-7 text-center cursor-pointer bg-gray-100 dark:bg-gray-900 hover:border-nd-green hover:bg-nd-green/5 transition-colors">
              <input type="file" className="hidden" accept="image/*" onChange={handlePhotoSelect} />
              {!previewImg ? (
                <div className="flex flex-col items-center gap-1.5">
                  <svg viewBox="0 0 24 24" className="w-10 h-10 fill-nd-green">
                    <path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z" />
                  </svg>
                  <div className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Drop a photo here or click to browse
                  </div>
                  <div className="text-xs text-gray-500 dark:text-gray-400">PNG, JPG up to 10 MB</div>
                </div>
              ) : (
                <div className="flex flex-col items-center gap-2.5">
                  <img src={previewImg} className="w-full max-h-[320px] rounded-xl object-contain" alt="Preview" />
                  <div className="flex items-center justify-between w-full gap-3 pt-1">
                    <span className="text-xs text-gray-500 dark:text-gray-400 truncate">{previewName}</span>
                    <button
                      type="button"
                      onClick={(e) => {
                        e.preventDefault();
                        setPreviewImg('');
                        setPreviewName('');
                      }}
                      className="px-3 py-1 rounded-full text-xs font-semibold text-red-500 border border-red-500/30 hover:bg-red-500 hover:text-white bg-transparent cursor-pointer"
                    >
                      Remove
                    </button>
                  </div>
                </div>
              )}
            </label>
          </div>

          <div className="flex justify-end items-center border-t border-gray-200 dark:border-gray-700 pt-4">
            <button type="button" className={pBtnPrimary} onClick={() => setStep(2)}>
              Next
            </button>
          </div>
        </div>
      )}

      {/* STEP 2: Sport Details */}
      {step === 2 && (
        <div>
          <h4 className="font-bold text-xl mb-4 text-center text-gray-900 dark:text-gray-50">Sport Details</h4>

          <div className="mb-4">
            <label className={pLabel}>Sport</label>
            <select
              className={pInput}
              required
              value={form.sport_id}
              onChange={(e) => setForm({ ...form, sport_id: e.target.value })}
            >
              <option value="" hidden>Select a sport</option>
              {sports.map((s) => (
                <option key={s.sport_id} value={s.sport_id}>
                  {s.sport_name}
                </option>
              ))}
            </select>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
              <label className={pLabel}>Minimum Players</label>
              <input
                type="number"
                className={pInput}
                min="1"
                value={form.min_players}
                onChange={(e) => setForm({ ...form, min_players: e.target.value })}
              />
            </div>
            <div>
              <label className={pLabel}>Maximum Players</label>
              <input
                type="number"
                className={pInput}
                min="1"
                value={form.max_players}
                onChange={(e) => setForm({ ...form, max_players: e.target.value })}
              />
            </div>
          </div>

          <div className="mb-4">
            <label className={pLabel}>Sport Related Description</label>
            <textarea
              className={pInput}
              placeholder="e.g. ground rules, skill level, equipment needed..."
              rows="3"
              value={form.sport_description}
              onChange={(e) => setForm({ ...form, sport_description: e.target.value })}
            />
          </div>

          <div className="mb-4">
            <label className={pLabel}>Skill Level</label>
            <select
              className={pInput}
              value={form.skill_level}
              onChange={(e) => setForm({ ...form, skill_level: e.target.value })}
            >
              <option value="Beginner">Beginner</option>
              <option value="Intermediate">Intermediate</option>
              <option value="Advanced">Advanced</option>
            </select>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div>
              <label className={pLabel}>Experience (Years)</label>
              <input
                type="number"
                className={pInput}
                min="0"
                max="50"
                value={form.experience_years}
                onChange={(e) => setForm({ ...form, experience_years: e.target.value })}
              />
            </div>
            <div>
              <label className={pLabel}>Preferred Position</label>
              <input
                type="text"
                className={pInput}
                placeholder="e.g. Striker, Batsman, Goalkeeper"
                value={form.preferred_position}
                onChange={(e) => setForm({ ...form, preferred_position: e.target.value })}
              />
            </div>
          </div>

          <div className="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
            <button type="button" className={pBtnBack} onClick={() => setStep(1)}>Back</button>
            <button type="button" className={pBtnPrimary} onClick={() => setStep(3)}>Next</button>
          </div>
        </div>
      )}

      {/* STEP 3: Match Details */}
      {step === 3 && (
        <div>
          <h4 className="font-bold text-xl mb-5 text-center text-gray-900 dark:text-gray-50">Match Details</h4>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
              <label className={pLabel}>Match Date</label>
              <input
                type="date"
                className={pInput}
                value={form.match_date}
                onChange={(e) => setForm({ ...form, match_date: e.target.value })}
              />
            </div>
            <div>
              <label className={pLabel}>Skill Level</label>
              <select
                className={pInput}
                value={form.skill_level_match}
                onChange={(e) => setForm({ ...form, skill_level_match: e.target.value })}
              >
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advanced">Advanced</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
              <label className={pLabel}>Start Time</label>
              <input
                type="time"
                className={pInput}
                value={form.start_time}
                onChange={(e) => setForm({ ...form, start_time: e.target.value })}
              />
            </div>
            <div>
              <label className={pLabel}>End Time</label>
              <input
                type="time"
                className={pInput}
                value={form.end_time}
                onChange={(e) => setForm({ ...form, end_time: e.target.value })}
              />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div>
              <label className={pLabel}>Visibility</label>
              <select
                className={pInput}
                value={form.visibility}
                onChange={(e) => setForm({ ...form, visibility: e.target.value })}
              >
                <option value="Public">Public</option>
                <option value="Private">Private</option>
              </select>
            </div>
            <div>
              <label className={pLabel}>Entry Fee</label>
              <input
                type="number"
                className={pInput}
                min="0"
                step="0.01"
                value={form.entry_fee}
                onChange={(e) => setForm({ ...form, entry_fee: e.target.value })}
              />
            </div>
          </div>

          <div className="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" className={pBtnBack} onClick={() => setStep(2)}>Back</button>
            <button type="button" className={pBtnPrimary} onClick={() => setStep(4)}>Next</button>
          </div>
        </div>
      )}

      {/* STEP 4: Ground Details */}
      {step === 4 && (
        <div>
          <h4 className="font-bold text-xl mb-5 text-center text-gray-900 dark:text-gray-50">Ground Details</h4>

          <div className="mb-4">
            <label className={pLabel}>City</label>
            <select
              className={pInput}
              value={form.ground_city}
              onChange={(e) => setForm({ ...form, ground_city: e.target.value, ground_id: '' })}
            >
              <option value="" hidden>Select a city</option>
              {cities.map((c) => (
                <option key={c} value={c}>{c}</option>
              ))}
            </select>
          </div>

          <div className="mb-5">
            <label className={pLabel}>Ground</label>
            <select
              className={pInput}
              disabled={!form.ground_city}
              value={form.ground_id}
              onChange={(e) => setForm({ ...form, ground_id: e.target.value })}
            >
              <option value="">{form.ground_city ? 'Select a ground' : 'Select a city first'}</option>
              {filteredGrounds.map((g) => (
                <option key={g.ground_id} value={g.ground_id}>{g.ground_name}</option>
              ))}
            </select>
          </div>

          <div className="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" className={pBtnBack} onClick={() => setStep(3)}>Back</button>
            <button type="button" className={pBtnPrimary} onClick={() => setStep(5)}>Next</button>
          </div>
        </div>
      )}

      {/* STEP 5: Preview */}
      {step === 5 && (
        <div>
          <h4 className="font-bold text-xl mb-5 text-center text-gray-900 dark:text-gray-50">Preview</h4>

          <div className="bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 space-y-2.5 text-sm">
            <div><strong>Content:</strong> {form.content || '—'}</div>
            <div><strong>Sport:</strong> {selectedSport?.sport_name || '—'} ({form.min_players}–{form.max_players} players)</div>
            <div><strong>Date &amp; Time:</strong> {form.match_date} &bull; {form.start_time} – {form.end_time}</div>
            <div><strong>Ground:</strong> {selectedGround?.ground_name || '—'} ({form.ground_city || '—'})</div>
            <div><strong>Visibility:</strong> {form.visibility} &bull; Entry Fee: ₹{form.entry_fee}</div>
          </div>

          <div className="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700 mt-5">
            <button type="button" className={pBtnBack} onClick={() => setStep(4)}>Back</button>
            <button type="submit" disabled={saving} className={pBtnPrimary}>
              {saving ? 'Publishing...' : 'Publish Match Post'}
            </button>
          </div>
        </div>
      )}
    </form>
  );
}
