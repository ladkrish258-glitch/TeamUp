<?php
$page = 'matchnote';
$pageTitle = 'MatchNote';

// Fetch live matches from DB if available
$matches = [];
try {
    require_once dirname(__DIR__) . '/Components/Connection/db.php';
    $stmt = $pdo->query("
        SELECT m.match_id, m.title, m.description, m.match_date, m.start_time, m.skill_level, m.match_status,
               s.sport_name, g.ground_name, g.city
        FROM matches m
        LEFT JOIN sports s ON m.sports_id = s.sport_id
        LEFT JOIN grounds g ON m.ground_id = g.ground_id
        WHERE m.visibility = 'Public' OR m.visibility IS NULL
        ORDER BY m.match_date DESC, m.created_at DESC
        LIMIT 6
    ");
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $matches = [];
}

// Sports ball icon & color helper for pinned notes
function getPinBall($sport) {
    $s = strtolower((string)$sport);
    if (strpos($s, 'cricket') !== false) {
        return ['icon' => 'fa-baseball-bat-ball', 'color' => '#c1512d'];
    } elseif (strpos($s, 'football') !== false || strpos($s, 'soccer') !== false) {
        return ['icon' => 'fa-futbol', 'color' => '#ff6b5c'];
    } elseif (strpos($s, 'basket') !== false) {
        return ['icon' => 'fa-basketball', 'color' => '#e3a730'];
    } elseif (strpos($s, 'volley') !== false) {
        return ['icon' => 'fa-volleyball', 'color' => '#b4923b'];
    } elseif (strpos($s, 'badminton') !== false || strpos($s, 'tennis') !== false) {
        return ['icon' => 'fa-table-tennis-paddle-ball', 'color' => '#43a047'];
    }
    return ['icon' => 'fa-futbol', 'color' => '#c1512d'];
}

$rotations = ['-2.4deg', '2.2deg', '-1.8deg', '1.6deg', '-2.6deg', '2.1deg', '-1.4deg', '2.5deg', '-2deg', '1.8deg', '-1.5deg', '2.3deg'];

require_once dirname(__DIR__) . '/Components/Header/header.php';
$rev = 'reveal opacity-0 translate-y-4 transition-all duration-700 [&.in]:opacity-100 [&.in]:translate-y-0';
$noteBase = 'cork-note relative rounded-xl p-5 pt-6 shadow-md transition-transform duration-200 hover:scale-[1.03] hover:z-10 flex flex-col justify-between';
$tagBase = 'inline-block self-start font-mono text-[10.5px] uppercase tracking-wider px-2.5 py-0.5 rounded font-bold mb-2';
?>

<section class="py-14 pb-8">
  <div class="max-w-[1160px] mx-auto px-6">
    <div class="flex justify-between items-end flex-wrap gap-5">
      <div>
        <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">Community Sports Bulletin</span>
        <h1 class="font-serif font-semibold text-4xl sm:text-5xl text-pine-dark mt-2 mb-3">MatchNote.</h1>
        <p class="text-ink-soft text-lg max-w-[600px] m-0">Your neighborhood corkboard for game announcements, ground etiquette, rule reminders, and friendly match fixtures.</p>
      </div>
      <div class="flex gap-3 flex-wrap">
        <a href="../Login/register.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-full text-sm font-semibold bg-pine hover:bg-pine-dark text-paper no-underline transition-colors">
          <i class="fa-solid fa-plus text-[13px]"></i> Post a Note
        </a>
      </div>
    </div>
  </div>
</section>

<!-- MAIN CORKBOARD SECTION -->
<section class="pt-6 pb-20">
  <div class="max-w-[1160px] mx-auto px-6">

    <!-- Filter Buttons Bar -->
    <div class="flex gap-2.5 flex-wrap mb-6 items-center">
      <span class="font-mono text-xs uppercase text-ink-soft mr-1">Filter:</span>
      <button type="button" class="filter-btn active px-4 py-1.5 rounded-full text-xs font-semibold border border-pine bg-pine text-paper cursor-pointer transition-colors" data-filter="all">All Notes</button>
      <button type="button" class="filter-btn px-4 py-1.5 rounded-full text-xs font-semibold border border-line bg-white text-ink-soft hover:bg-paper-2 cursor-pointer transition-colors" data-filter="match">Match Fixtures</button>
      <button type="button" class="filter-btn px-4 py-1.5 rounded-full text-xs font-semibold border border-line bg-white text-ink-soft hover:bg-paper-2 cursor-pointer transition-colors" data-filter="rules">Fair Play &amp; Rules</button>
      <button type="button" class="filter-btn px-4 py-1.5 rounded-full text-xs font-semibold border border-line bg-white text-ink-soft hover:bg-paper-2 cursor-pointer transition-colors" data-filter="notice">Notices &amp; Lost/Found</button>
    </div>

    <!-- Corkboard Wall -->
    <div class="<?= $rev ?> bg-[#C69C6D] border-[8px] border-[#9A7148] rounded-3xl p-6 sm:p-9 shadow-2xl" style="background-image: radial-gradient(rgba(0,0,0,0.08) 1px, transparent 0); background-size: 18px 18px;">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">

        <!-- NOTE 1: Fair Play Rule 1 -->
        <div class="<?= $noteBase ?> bg-[#EBF5EE]" data-category="rules" style="transform: rotate(<?= $rotations[0] ?>);">
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#ff6b5c] drop-shadow"><i class="fa-solid fa-futbol"></i></div>
          <div>
            <span class="<?= $tagBase ?> bg-pine/15 text-pine">Rule #01</span>
            <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">Punctuality &amp; Prep</div>
            <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
              Arrive 15 minutes before game time. Warm up properly, confirm team bibs, and check that footwear matches the turf surface.
            </p>
          </div>
          <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
            <span>Fair Play Standard</span>
            <span>// Punctuality</span>
          </div>
        </div>

        <!-- NOTE 2: Fair Play Rule 2 -->
        <div class="<?= $noteBase ?> bg-[#FFF8DF]" data-category="rules" style="transform: rotate(<?= $rotations[1] ?>);">
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#e3a730] drop-shadow"><i class="fa-solid fa-basketball"></i></div>
          <div>
            <span class="<?= $tagBase ?> bg-gold/25 text-pine-dark">Rule #02</span>
            <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">Sportsman Spirit</div>
            <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
              Friendly competition first. Call your own fouls honestly, respect the organizer or referee's decisions, and play safe.
            </p>
          </div>
          <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
            <span>Fair Play Standard</span>
            <span>// Integrity</span>
          </div>
        </div>

        <!-- NOTE 3: Fair Play Rule 3 -->
        <div class="<?= $noteBase ?> bg-[#EDF4FC]" data-category="rules" style="transform: rotate(<?= $rotations[2] ?>);">
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#b4923b] drop-shadow"><i class="fa-solid fa-volleyball"></i></div>
          <div>
            <span class="<?= $tagBase ?> bg-blue-500/15 text-blue-900">Rule #03</span>
            <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">Ground &amp; Turf Care</div>
            <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
              Leave the turf cleaner than you found it. Pack all water bottles, personal gear, and report any damaged facilities to the ground manager.
            </p>
          </div>
          <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
            <span>Fair Play Standard</span>
            <span>// Ground Etiquette</span>
          </div>
        </div>

        <!-- NOTE 4: Notice / Lost & Found -->
        <div class="<?= $noteBase ?> bg-white" data-category="notice" style="transform: rotate(<?= $rotations[3] ?>);">
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#ff6b5c] drop-shadow"><i class="fa-solid fa-futbol"></i></div>
          <div>
            <span class="<?= $tagBase ?> bg-brick/15 text-brick">Lost &amp; Found</span>
            <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">Wilson Basketball</div>
            <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
              Left an orange Wilson Evolution size-7 ball near Court 2 bench on Tuesday evening. If found, please drop with the pavilion caretaker!
            </p>
          </div>
          <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
            <span>Court 2 Pavilion</span>
            <span>Reward: High Five</span>
          </div>
        </div>

        <!-- NOTE 5: Notice / Heads Up -->
        <div class="<?= $noteBase ?> bg-white" data-category="notice" style="transform: rotate(<?= $rotations[4] ?>);">
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#c1512d] drop-shadow"><i class="fa-solid fa-football"></i></div>
          <div>
            <span class="<?= $tagBase ?> bg-brick/15 text-brick">Heads up</span>
            <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">Turf Lighting Upgrade</div>
            <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
              Central Park field lights are being upgraded this Wednesday 8:00 AM – 2:00 PM. Evening fixtures will go ahead with brighter LEDs.
            </p>
          </div>
          <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
            <span>Central Park Turf</span>
            <span>Wednesday</span>
          </div>
        </div>

        <!-- NOTE 6: Notice / Free Practice -->
        <div class="<?= $noteBase ?> bg-[#EBF5EE]" data-category="notice" style="transform: rotate(<?= $rotations[5] ?>);">
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#b4923b] drop-shadow"><i class="fa-solid fa-volleyball"></i></div>
          <div>
            <span class="<?= $tagBase ?> bg-pine/15 text-pine">Free Pickup</span>
            <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">Open Volley Drills</div>
            <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
              Sand court net is set up for informal 3v3 passing drills every Saturday 7:30 AM. Beginners and intermediate players welcome!
            </p>
          </div>
          <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
            <span>South Beach Sand Court</span>
            <span>Saturday 7:30 AM</span>
          </div>
        </div>

        <!-- MATCH NOTES (From Database or Default Fixtures) -->
        <?php if (!empty($matches)): ?>
          <?php foreach ($matches as $i => $m): 
            $pin = getPinBall($m['sport_name'] ?? 'sports');
            $rot = $rotations[($i + 6) % count($rotations)];
          ?>
            <div class="<?= $noteBase ?> bg-[#FFF8DF]" data-category="match" style="transform: rotate(<?= $rot ?>);">
              <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl drop-shadow" style="color: <?= $pin['color'] ?>;"><i class="fa-solid <?= $pin['icon'] ?>"></i></div>
              <div>
                <span class="<?= $tagBase ?> bg-gold/25 text-pine-dark"><?= htmlspecialchars($m['sport_name'] ?? 'Match') ?></span>
                <div class="font-serif font-bold text-lg text-pine-dark mb-1.5"><?= htmlspecialchars($m['title'] ?? 'Community Match') ?></div>
                <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
                  <?= !empty($m['description']) ? htmlspecialchars(mb_strimwidth($m['description'], 0, 140, '...')) : 'Match note posted by organizer. All skill levels welcome to RSVP.' ?>
                </p>
              </div>
              <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
                <span>📍 <?= htmlspecialchars($m['ground_name'] ?? 'Local Ground') ?></span>
                <span>📅 <?= !empty($m['match_date']) ? date('M d', strtotime($m['match_date'])) : 'Upcoming' ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Sample Match 1 -->
          <div class="<?= $noteBase ?> bg-[#FFF8DF]" data-category="match" style="transform: rotate(<?= $rotations[6] ?>);">
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#c1512d] drop-shadow"><i class="fa-solid fa-baseball-bat-ball"></i></div>
            <div>
              <span class="<?= $tagBase ?> bg-gold/25 text-pine-dark">This weekend</span>
              <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">T20 Tennis Ball Cricket</div>
              <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
                Looking for 3 more players for a 10-over friendly match. Match ball provided, whites optional. Please RSVP early so teams can be drafted.
              </p>
            </div>
            <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
              <span>📍 Central Green Park Turf</span>
              <span>Sat 7:00 AM</span>
            </div>
          </div>

          <!-- Sample Match 2 -->
          <div class="<?= $noteBase ?> bg-white" data-category="match" style="transform: rotate(<?= $rotations[7] ?>);">
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#ff6b5c] drop-shadow"><i class="fa-solid fa-futbol"></i></div>
            <div>
              <span class="<?= $tagBase ?> bg-brick/15 text-brick">Match Fixture</span>
              <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">7-a-side Friendly Turf</div>
              <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
                Turf booked for 2 hours under lights. Bibs and match balls arranged. Goalkeepers play free! Bring turf studs or flat indoor trainers.
              </p>
            </div>
            <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
              <span>📍 Arena Sports Hub, Court 2</span>
              <span>Sun 6:30 PM</span>
            </div>
          </div>

          <!-- Sample Match 3 -->
          <div class="<?= $noteBase ?> bg-[#EBF5EE]" data-category="match" style="transform: rotate(<?= $rotations[8] ?>);">
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#43a047] drop-shadow"><i class="fa-solid fa-table-tennis-paddle-ball"></i></div>
            <div>
              <span class="<?= $tagBase ?> bg-pine/15 text-pine">Badminton</span>
              <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">Doubles Practice &amp; Rally</div>
              <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
                Wooden court booked. Yonex Mavis 350 shuttles provided. Rotating partners after every 21-point set. Clean non-marking shoes required.
              </p>
            </div>
            <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
              <span>📍 Shuttle Zone Academy</span>
              <span>Thu 8:00 PM</span>
            </div>
          </div>

          <!-- Sample Match 4 -->
          <div class="<?= $noteBase ?> bg-[#EDF4FC]" data-category="match" style="transform: rotate(<?= $rotations[9] ?>);">
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#e3a730] drop-shadow"><i class="fa-solid fa-basketball"></i></div>
            <div>
              <span class="<?= $tagBase ?> bg-blue-500/15 text-blue-900">Basketball</span>
              <div class="font-serif font-bold text-lg text-pine-dark mb-1.5">Morning 3v3 Half-Court</div>
              <p class="text-sm text-ink-soft leading-relaxed m-0 mb-4">
                Friendly half-court games to 15 points. Winner stays on. Bring light &amp; dark jersey. Fast breaks, good vibes only!
              </p>
            </div>
            <div class="flex justify-between items-center text-[11px] font-mono text-ink-soft border-t border-black/10 pt-2.5">
              <span>📍 Eastside Hoops Park</span>
              <span>Sat 8:30 AM</span>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>

    <!-- Bottom Action Card -->
    <div class="mt-12 bg-white border-[1.5px] border-line rounded-card text-center px-6 py-10 shadow-sm">
      <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">Organize &amp; Play</span>
      <h3 class="font-serif text-2xl my-2.5 text-pine-dark">Have a match note, pickup game, or ground update to pin?</h3>
      <p class="text-ink-soft max-w-[520px] mx-auto mb-5 text-[15px] leading-relaxed">
        Join TeamUp to post your own match notes, invite neighbors, book nearby grounds, and keep track of your sports scores.
      </p>
      <div class="flex justify-center gap-3.5 flex-wrap">
        <a href="../Login/register.php" class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-pine hover:bg-pine-dark text-paper no-underline transition-colors">Join Free &amp; Pin a Note</a>
        <a href="../Login/login.php" class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold border border-line bg-white hover:bg-paper-2 text-pine-dark no-underline transition-colors">Log In</a>
      </div>
    </div>

  </div>
</section>

<!-- Filter Script -->
<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => {
      b.classList.remove('active', 'bg-pine', 'text-paper', 'border-pine');
      b.classList.add('bg-white', 'text-ink-soft', 'border-line');
    });
    btn.classList.add('active', 'bg-pine', 'text-paper', 'border-pine');
    btn.classList.remove('bg-white', 'text-ink-soft', 'border-line');
    const filter = btn.getAttribute('data-filter');
    document.querySelectorAll('.cork-note').forEach(note => {
      if (filter === 'all' || note.getAttribute('data-category') === filter) {
        note.style.display = 'flex';
      } else {
        note.style.display = 'none';
      }
    });
  });
});
</script>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
