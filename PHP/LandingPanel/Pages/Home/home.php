<?php
$page = 'home';
$pageTitle = 'Home';
require_once dirname(__DIR__) . '/Components/Header/header.php';
$rev = 'reveal opacity-0 translate-y-4 transition-all duration-700 [&.in]:opacity-100 [&.in]:translate-y-0';
?>

<section class="py-16 md:py-20">
  <div class="max-w-[1160px] mx-auto px-6 grid grid-cols-1 lg:grid-cols-[1.05fr_0.95fr] gap-12 items-center">
    <div>
      <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">Free &middot; No dues &middot; Just neighbors</span>
      <h1 class="font-serif font-semibold text-4xl sm:text-5xl lg:text-[56px] leading-[1.05] tracking-tight text-pine-dark mt-3.5 mb-5">
        Everything's better with <em class="italic font-medium text-brick">neighbors</em> nearby.
      </h1>
      <p class="text-[18px] text-ink-soft max-w-[490px] leading-relaxed mb-7">
        Porchlight is the private space for your actual street — lost cats, borrowed ladders, block parties, and the stuff nobody thinks to text you about.
      </p>
      <form class="flex flex-col sm:flex-row gap-2.5 max-w-[460px] bg-white p-1.5 sm:rounded-full rounded-2xl border border-line shadow-sm" id="join">
        <input type="text" placeholder="Enter your street address" aria-label="Your street address"
               class="flex-1 bg-transparent border-0 outline-none px-4 py-2.5 text-[15px] text-ink">
        <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 rounded-full text-sm font-semibold bg-gold hover:bg-gold-dark text-pine-dark border-0 cursor-pointer transition-colors shrink-0">Find my block</button>
      </form>
      <p class="font-mono text-xs text-ink-soft mt-3">// verified by address, one neighborhood at a time</p>
    </div>

    <!-- Corkboard Preview -->
    <div class="<?= $rev ?> relative bg-[#C69C6D] border-[6px] border-[#9A7148] rounded-[22px] p-6 shadow-xl grid grid-cols-1 sm:grid-cols-2 gap-4" style="background-image: radial-gradient(rgba(0,0,0,0.08) 1px, transparent 0); background-size: 16px 16px;" aria-hidden="true">
      <div class="relative bg-white rounded-xl p-4 pt-5 shadow-md -rotate-2 text-sm text-ink">
        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#ff6b5c] drop-shadow"><i class="fa-solid fa-futbol"></i></div>
        <span class="inline-block font-mono text-[10px] uppercase tracking-wider px-2 py-0.5 rounded bg-brick/15 text-brick font-bold mb-1.5">Lost &amp; found</span>
        <div class="leading-snug text-ink-soft">Grey tabby, green collar, last seen near the community garden. Answers to "Biscuit."</div>
      </div>
      <div class="relative bg-[#FFF8DF] rounded-xl p-4 pt-5 shadow-md rotate-2 text-sm text-ink">
        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#e3a730] drop-shadow"><i class="fa-solid fa-basketball"></i></div>
        <span class="inline-block font-mono text-[10px] uppercase tracking-wider px-2 py-0.5 rounded bg-gold/25 text-pine-dark font-bold mb-1.5">This weekend</span>
        <div class="leading-snug text-ink-soft">Elm Street block party — Sat 4pm. Bring a dish, we've got the folding tables.</div>
      </div>
      <div class="relative bg-[#EBF5EE] rounded-xl p-4 pt-5 shadow-md rotate-1 text-sm text-ink">
        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#b4923b] drop-shadow"><i class="fa-solid fa-volleyball"></i></div>
        <span class="inline-block font-mono text-[10px] uppercase tracking-wider px-2 py-0.5 rounded bg-pine/15 text-pine font-bold mb-1.5">Free</span>
        <div class="leading-snug text-ink-soft">Moving boxes, good condition, 20+. Front porch, help yourself.</div>
      </div>
      <div class="relative bg-white rounded-xl p-4 pt-5 shadow-md -rotate-1 text-sm text-ink">
        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-2xl text-[#c1512d] drop-shadow"><i class="fa-solid fa-football"></i></div>
        <span class="inline-block font-mono text-[10px] uppercase tracking-wider px-2 py-0.5 rounded bg-brick/15 text-brick font-bold mb-1.5">Heads up</span>
        <div class="leading-snug text-ink-soft">Water main work on 4th Ave, Tues–Wed. Expect some noise in the morning.</div>
      </div>
    </div>
  </div>
</section>

<div class="bg-pine-dark text-paper py-3.5 border-y border-white/10" aria-hidden="true">
  <div class="max-w-[1160px] mx-auto px-6 flex items-center justify-around flex-wrap gap-5 font-mono text-xs tracking-wide text-paper/85">
    <span>🏡 2,400 blocks and counting</span>
    <span>🔦 Porch pickups every Sunday</span>
    <span>🐾 11,000 lost pets reunited</span>
    <span>🛠️ Tools borrowed, not bought</span>
  </div>
</div>

<section class="py-16 md:py-20" id="features">
  <div class="max-w-[1160px] mx-auto px-6">
    <div class="<?= $rev ?> max-w-[600px] mb-11">
      <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">What's on the board</span>
      <h2 class="font-serif font-semibold text-3xl sm:text-4xl text-pine-dark mt-2.5 mb-3 tracking-tight">The stuff a group text can't hold.</h2>
      <p class="text-ink-soft text-base m-0">One feed, sorted by how close it is — not how loud it is.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="<?= $rev ?> bg-white border border-line rounded-card p-7 shadow-sm">
        <div class="w-11 h-11 rounded-xl bg-pine/10 flex items-center justify-center mb-4">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M3 11L12 4L21 11" stroke="#1E4032" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 10V20H19V10" stroke="#1E4032" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h3 class="font-serif text-xl text-pine-dark mt-0 mb-2">The block feed</h3>
        <p class="text-sm text-ink-soft leading-relaxed m-0">Garage sales, missing packages, and "did anyone else hear that?" — sorted by distance from your porch, not popularity.</p>
      </div>
      <div class="<?= $rev ?> bg-white border border-line rounded-card p-7 shadow-sm">
        <div class="w-11 h-11 rounded-xl bg-brick/10 flex items-center justify-center mb-4">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 7H20L18 20H6L4 7Z" stroke="#C1512D" stroke-width="2" stroke-linejoin="round"/><path d="M8 7V5C8 3.34315 9.34315 2 11 2H13C14.6569 2 16 3.34315 16 5V7" stroke="#C1512D" stroke-width="2"/></svg>
        </div>
        <h3 class="font-serif text-xl text-pine-dark mt-0 mb-2">Give &amp; borrow</h3>
        <p class="text-sm text-ink-soft leading-relaxed m-0">A ladder, a stand mixer, a folding table — half the stuff you need already lives three doors down.</p>
      </div>
      <div class="<?= $rev ?> bg-white border border-line rounded-card p-7 shadow-sm">
        <div class="w-11 h-11 rounded-xl bg-gold/15 flex items-center justify-center mb-4">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 6V11C4 16 7.5 20.5 12 22C16.5 20.5 20 16 20 11V6L12 2Z" stroke="#B4923B" stroke-width="2" stroke-linejoin="round"/></svg>
        </div>
        <h3 class="font-serif text-xl text-pine-dark mt-0 mb-2">Heads-up alerts</h3>
        <p class="text-sm text-ink-soft leading-relaxed m-0">Road work, power flickers, a porch light out on the corner — the things worth knowing before you leave the house.</p>
      </div>
    </div>
  </div>
</section>

<section class="py-16 md:py-20 bg-paper-2 border-y border-line" id="how">
  <div class="max-w-[1160px] mx-auto px-6">
    <div class="<?= $rev ?> max-w-[600px] mb-11">
      <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">How it works</span>
      <h2 class="font-serif font-semibold text-3xl sm:text-4xl text-pine-dark mt-2.5 mb-0 tracking-tight">Three stops to your block.</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="<?= $rev ?> bg-white border border-line rounded-card p-7 shadow-sm">
        <div class="w-10 h-10 rounded-full bg-pine text-paper font-mono font-bold flex items-center justify-center mb-4">1</div>
        <h3 class="font-serif text-xl text-pine-dark mt-0 mb-2">Enter your address</h3>
        <p class="text-sm text-ink-soft leading-relaxed m-0">We check it against your actual street — Porchlight is built around real blocks, not zip codes.</p>
      </div>
      <div class="<?= $rev ?> bg-white border border-line rounded-card p-7 shadow-sm">
        <div class="w-10 h-10 rounded-full bg-pine text-paper font-mono font-bold flex items-center justify-center mb-4">2</div>
        <h3 class="font-serif text-xl text-pine-dark mt-0 mb-2">Get verified</h3>
        <p class="text-sm text-ink-soft leading-relaxed m-0">A postcard or a quick ID check confirms you live there. Every neighbor is a real neighbor.</p>
      </div>
      <div class="<?= $rev ?> bg-white border border-line rounded-card p-7 shadow-sm">
        <div class="w-10 h-10 rounded-full bg-pine text-paper font-mono font-bold flex items-center justify-center mb-4">3</div>
        <h3 class="font-serif text-xl text-pine-dark mt-0 mb-2">Meet the block</h3>
        <p class="text-sm text-ink-soft leading-relaxed m-0">Your feed fills in with the people, posts, and porches closest to you first.</p>
      </div>
    </div>
  </div>
</section>

<?php
// Game Changing Stories / Reviews — dynamically loaded from ratings table where visibility is 'Visible'
require_once dirname(__DIR__) . '/Components/Connection/db.php';

$stories = [];
try {
  $stmt = $pdo->prepare("
    SELECT r.*, u.full_name, u.username, u.city, u.profile_photo, m.title as match_title, s.sport_name
    FROM ratings r
    LEFT JOIN users u ON r.user_id = u.user_id
    LEFT JOIN matches m ON r.match_id = m.match_id
    LEFT JOIN sports s ON m.sports_id = s.sport_id
    WHERE LOWER(TRIM(r.visibility)) IN ('visible', 'public', '1', 'true')
    ORDER BY r.rating_id DESC
    LIMIT 10
  ");
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fallback avatars pool
  $fallbackAvatars = [
    '/Team-Up/images/User/Profile/user-1.jpg',
    '/Team-Up/images/User/Profile/user-2.jpg',
    '/Team-Up/images/User/Profile/user-3.jpg',
    '/Team-Up/images/User/Profile/user-4.jpg',
    '/Team-Up/images/User/Profile/user-5.jpg'
  ];

  foreach ($rows as $idx => $r) {
    $name = !empty($r['full_name']) ? $r['full_name'] : (!empty($r['username']) ? $r['username'] : 'Player #' . $r['user_id']);
    $loc = !empty($r['city']) ? $r['city'] : 'India';
    $sport = !empty($r['sport_name']) ? $r['sport_name'] : (!empty($r['match_title']) ? $r['match_title'] : 'Verified Player');
    $rating = max(1, min(5, (int)round(floatval($r['rating'] ?? 5))));
    $quote = !empty($r['review']) ? $r['review'] : 'Sports truly connect people in ways nothing else does!';

    $image = $fallbackAvatars[$idx % count($fallbackAvatars)];
    if (!empty($r['profile_photo'])) {
      $photo = trim($r['profile_photo']);
      if (preg_match('#^https?://#i', $photo)) {
        $image = $photo;
      } elseif (file_exists(dirname(__DIR__, 4) . '/images/User/Profile/' . basename($photo))) {
        $image = '/Team-Up/images/User/Profile/' . basename($photo);
      }
    }

    $stories[] = [
      'name'     => $name,
      'location' => $loc,
      'sport'    => $sport,
      'image'    => $image,
      'rating'   => $rating,
      'quote'    => $quote
    ];
  }
} catch (Exception $e) {
  $stories = [];
}
?>
<section class="py-16" id="stories">
  <div class="max-w-[1160px] mx-auto px-6">
    
    <div class="<?= $rev ?> flex items-center gap-4 mb-9">
      <div class="h-px flex-1 bg-line"></div>
      <h2 class="font-serif font-semibold text-2xl sm:text-3xl text-pine-dark m-0 text-center">Game Changing Stories</h2>
      <div class="h-px flex-1 bg-line"></div>
    </div>

    <?php if (!empty($stories)): ?>
      <div class="<?= $rev ?> grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <?php foreach ($stories as $story): ?>
          <div class="story-card group relative h-[320px] rounded-2xl overflow-hidden cursor-pointer shadow-md border border-line bg-pine-dark">
            <img src="<?= htmlspecialchars($story['image']) ?>" alt="<?= htmlspecialchars($story['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
            
            <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/85 via-black/45 to-transparent text-white transition-opacity duration-300 group-hover:opacity-0 group-[.active]:opacity-0">
              <span class="block font-bold text-base leading-tight"><?= htmlspecialchars($story['name']) ?></span>
              <span class="block text-xs text-white/80 mt-0.5"><?= htmlspecialchars($story['sport']) ?> &bull; <?= htmlspecialchars($story['location']) ?></span>
            </div>

            <div class="absolute inset-0 bg-[#1D4ED8] text-white p-5 flex flex-col justify-between opacity-0 pointer-events-none transition-opacity duration-300 group-hover:opacity-100 group-hover:pointer-events-auto group-[.active]:opacity-100 group-[.active]:pointer-events-auto">
              <div>
                <div class="text-gold text-sm tracking-widest mb-2.5"><?= str_repeat('★', (int)$story['rating']) . str_repeat('☆', 5 - (int)$story['rating']) ?></div>
                <p class="text-[13px] leading-relaxed m-0 text-white/95">&ldquo;<?= htmlspecialchars($story['quote']) ?>&rdquo;</p>
              </div>
              <div class="text-xs font-semibold text-white/90 border-t border-white/20 pt-2.5">
                <?= htmlspecialchars($story['name']) ?> &bull; <?= htmlspecialchars($story['location']) ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-12 px-4 text-ink-soft bg-paper-2 rounded-2xl border border-line">
        <i class="fa-regular fa-star text-3xl mb-3 text-gold"></i>
        <p class="text-base font-medium m-0">No ratings are currently set to visible. Mark ratings as &ldquo;Visible&rdquo; in the Admin Panel to display them here.</p>
      </div>
    <?php endif; ?>

  </div>
</section>

<script>
// Click/Tap toggle for touch devices
document.querySelectorAll('.story-card').forEach(card => {
  card.addEventListener('click', () => {
    const isAlreadyActive = card.classList.contains('active');
    document.querySelectorAll('.story-card').forEach(c => c.classList.remove('active'));
    if (!isAlreadyActive) {
      card.classList.add('active');
    }
  });
});
</script>

<section class="py-16 bg-pine text-paper text-center">
  <div class="max-w-[1160px] mx-auto px-6">
    <h2 class="font-serif font-semibold text-3xl sm:text-4xl m-0 mb-3 text-paper">Your neighbors are already here.</h2>
    <p class="text-paper/80 text-base mb-7">It takes about two minutes to find your block.</p>
    <form class="flex flex-col sm:flex-row gap-2.5 max-w-[460px] mx-auto bg-white p-1.5 sm:rounded-full rounded-2xl shadow-lg">
      <input type="text" placeholder="Enter your street address" aria-label="Your street address"
             class="flex-1 bg-transparent border-0 outline-none px-4 py-2.5 text-[15px] text-ink">
      <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 rounded-full text-sm font-semibold bg-pine-dark hover:bg-brick text-paper border-0 cursor-pointer transition-colors shrink-0">Find my block</button>
    </form>
  </div>
</section>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
