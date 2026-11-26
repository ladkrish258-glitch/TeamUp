<?php
$page = 'about';
$pageTitle = 'About Us';

require_once dirname(__DIR__) . '/Components/Header/header.php';
$rev = 'reveal opacity-0 translate-y-4 transition-all duration-700 [&.in]:opacity-100 [&.in]:translate-y-0';
?>

<!-- Hero Section -->
<section class="py-14 pb-8">
  <div class="max-w-[1160px] mx-auto px-6">
    <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">Our Story &amp; Mission</span>
    <h1 class="font-serif font-semibold text-4xl sm:text-5xl text-pine-dark mt-2.5 mb-4">Built for the love of the game.</h1>
    <p class="text-ink-soft text-lg max-w-[740px] leading-relaxed m-0">TeamUp is a grassroots neighborhood sports community platform designed to get players off the bench and onto the field. Whether you need an extra bowler for Sunday cricket, a goalkeeper for 5-a-side football, or a friendly badminton partner, TeamUp connects you with players right next door.</p>
  </div>
</section>

<!-- Stats Bar -->
<div class="bg-pine-dark text-paper py-3.5 border-y border-white/10 mb-5" aria-hidden="true">
  <div class="max-w-[1160px] mx-auto px-6 flex justify-around flex-wrap gap-5 font-mono text-xs tracking-wide text-paper/90">
    <span>⚡ 50+ Verified Turfs &amp; Grounds</span>
    <span>🏆 10+ Sports Leagues &amp; Pickups</span>
    <span>🤝 2,500+ Neighborhood Players</span>
    <span>⚽ 1,200+ Matches Organized</span>
  </div>
</div>

<!-- Main Pillars Section -->
<section class="pt-10 pb-16">
  <div class="max-w-[1160px] mx-auto px-6">

    <div class="text-center max-w-[680px] mx-auto mb-12">
      <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">What Drives Us</span>
      <h2 class="font-serif font-semibold text-3xl sm:text-4xl mt-3 mb-3.5 text-pine-dark">A smarter, friendlier way to play local sports.</h2>
      <p class="text-ink-soft text-base leading-relaxed m-0">We believe sports are best enjoyed when everyone has easy access to fields, dependable teammates, and a welcoming community atmosphere.</p>
    </div>

    <!-- 3 Core Feature Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-7 mb-16">
      
      <div class="<?= $rev ?> bg-white border border-line rounded-card p-7 shadow-sm">
        <div class="w-12 h-12 rounded-xl bg-paper-2 flex items-center justify-center mb-4 text-pine">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <h3 class="font-serif text-[22px] mb-2.5 mt-0 text-pine-dark">Inclusive Matchmaking</h3>
        <p class="text-ink-soft leading-relaxed text-[15px] m-0">
          From first-timers trying a sport to seasoned weekend players, every match on TeamUp is tagged by skill level (Beginner, Intermediate, Advanced) so games stay competitive, fair, and fun.
        </p>
      </div>

      <div class="<?= $rev ?> bg-white border border-line rounded-card p-7 shadow-sm">
        <div class="w-12 h-12 rounded-xl bg-paper-2 flex items-center justify-center mb-4 text-gold-dark">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
        </div>
        <h3 class="font-serif text-[22px] mb-2.5 mt-0 text-pine-dark">Grounds &amp; Venues Directory</h3>
        <p class="text-ink-soft leading-relaxed text-[15px] m-0">
          Explore verified cricket turfs, football grounds, indoor wooden badminton courts, and basketball hoops near you with directions, lighting status, amenities, and player reviews.
        </p>
      </div>

      <div class="<?= $rev ?> bg-white border border-line rounded-card p-7 shadow-sm">
        <div class="w-12 h-12 rounded-xl bg-paper-2 flex items-center justify-center mb-4 text-brick">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
          </svg>
        </div>
        <h3 class="font-serif text-[22px] mb-2.5 mt-0 text-pine-dark">Fair Play &amp; Community Trust</h3>
        <p class="text-ink-soft leading-relaxed text-[15px] m-0">
          Honest community ratings, attendance punctuality tracking, and sportsman badges ensure games remain safe, respectful, and reliable for everyone involved.
        </p>
      </div>

    </div>

    <!-- How It Works Step-by-Step -->
    <div class="mb-[70px]">
      <div class="text-center mb-9">
        <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">How It Works</span>
        <h2 class="font-serif text-3xl mt-2.5 text-pine-dark">From match idea to kick-off in 4 simple steps.</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <div class="<?= $rev ?> bg-white border border-line rounded-card px-5 py-6 shadow-sm">
          <div class="font-mono text-[28px] font-bold text-gold mb-3">01</div>
          <h4 class="font-serif text-lg m-0 mb-2 text-pine-dark">Post or Find a Match</h4>
          <p class="text-sm text-ink-soft leading-relaxed m-0">
            Choose a sport, select a ground, pick the date &amp; start time, and set open player slots.
          </p>
        </div>

        <div class="<?= $rev ?> bg-white border border-line rounded-card px-5 py-6 shadow-sm">
          <div class="font-mono text-[28px] font-bold text-gold mb-3">02</div>
          <h4 class="font-serif text-lg m-0 mb-2 text-pine-dark">RSVP &amp; Lock Rosters</h4>
          <p class="text-sm text-ink-soft leading-relaxed m-0">
            Nearby players get notified, review match guidelines on MatchNote, and confirm their participation.
          </p>
        </div>

        <div class="<?= $rev ?> bg-white border border-line rounded-card px-5 py-6 shadow-sm">
          <div class="font-mono text-[28px] font-bold text-gold mb-3">03</div>
          <h4 class="font-serif text-lg m-0 mb-2 text-pine-dark">Show Up &amp; Play</h4>
          <p class="text-sm text-ink-soft leading-relaxed m-0">
            Head to the ground with your gear. Everything is pre-arranged so you can focus entirely on enjoying the sport.
          </p>
        </div>

        <div class="<?= $rev ?> bg-white border border-line rounded-card px-5 py-6 shadow-sm">
          <div class="font-mono text-[28px] font-bold text-gold mb-3">04</div>
          <h4 class="font-serif text-lg m-0 mb-2 text-pine-dark">Log Scores &amp; Badges</h4>
          <p class="text-sm text-ink-soft leading-relaxed m-0">
            Record final match results, applaud MVP performances, and build your neighbor sports reputation.
          </p>
        </div>

      </div>
    </div>

    <!-- Supported Sports Grid -->
    <div class="<?= $rev ?> bg-white border-[1.5px] border-line rounded-card px-7 py-9 mb-[70px] shadow-sm">
      <div class="flex justify-between items-center flex-wrap gap-4 mb-6">
        <div>
          <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">Play What You Love</span>
          <h3 class="font-serif text-2xl mt-1.5 mb-0 text-pine-dark">Sports available on TeamUp</h3>
        </div>
        <a href="../MatchNote/matchnote.php" class="inline-flex items-center px-4 py-2 rounded-full text-[13.5px] font-semibold border border-line bg-white hover:bg-paper-2 text-pine-dark no-underline transition-colors">View MatchNote Board &rarr;</a>
      </div>

      <div class="flex flex-wrap gap-3">
        <span class="inline-flex items-center gap-2 bg-paper-2 px-4 py-2 rounded-full text-sm font-medium text-pine-dark">
          <i class="fa-solid fa-baseball-bat-ball text-[#c1512d]"></i> Cricket (Box &amp; Ground)
        </span>
        <span class="inline-flex items-center gap-2 bg-paper-2 px-4 py-2 rounded-full text-sm font-medium text-pine-dark">
          <i class="fa-solid fa-futbol text-[#ff6b5c]"></i> Football (5s &amp; 7s Turf)
        </span>
        <span class="inline-flex items-center gap-2 bg-paper-2 px-4 py-2 rounded-full text-sm font-medium text-pine-dark">
          <i class="fa-solid fa-table-tennis-paddle-ball text-[#43a047]"></i> Badminton (Singles &amp; Doubles)
        </span>
        <span class="inline-flex items-center gap-2 bg-paper-2 px-4 py-2 rounded-full text-sm font-medium text-pine-dark">
          <i class="fa-solid fa-basketball text-[#e3a730]"></i> Basketball (3v3 &amp; Full Court)
        </span>
        <span class="inline-flex items-center gap-2 bg-paper-2 px-4 py-2 rounded-full text-sm font-medium text-pine-dark">
          <i class="fa-solid fa-volleyball text-[#b4923b]"></i> Volleyball &amp; Throwball
        </span>
        <span class="inline-flex items-center gap-2 bg-paper-2 px-4 py-2 rounded-full text-sm font-medium text-pine-dark">
          <i class="fa-solid fa-table-tennis-paddle-ball text-[#2b5b84]"></i> Table Tennis &amp; Pickleball
        </span>
      </div>
    </div>

    <!-- Vision and Story -->
    <div class="<?= $rev ?> bg-paper-2 rounded-card px-9 py-12 mb-[70px]">
      <div class="max-w-[820px] mx-auto text-center">
        <span class="font-mono text-xs tracking-[0.12em] uppercase text-pine font-bold">Our Story</span>
        <h2 class="font-serif text-3xl sm:text-4xl my-4 text-pine-dark">No more chaotic messaging groups just to arrange a weekend game.</h2>
        <p class="text-[16.5px] leading-relaxed text-ink-soft mb-5">
          Like many sports enthusiasts, we spent years trapped in disorganized group chats trying to get 10 people together for a Sunday match. Inevitably, last-minute dropouts, double-booked grounds, and mismatched skill levels meant games got cancelled at the eleventh hour.
        </p>
        <p class="text-[16.5px] leading-relaxed text-ink-soft mb-7">
          We built TeamUp to give local sports the dedicated platform it deserves: clear match attendance, verified turf listings, community guidelines, and active neighbor participation so games always go ahead smoothly.
        </p>
        <div class="flex gap-4 justify-center flex-wrap">
          <a href="../Login/register.php" class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold bg-pine hover:bg-pine-dark text-paper no-underline transition-colors">Join the Community</a>
          <a href="../ContactUs/contact.php" class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold border border-line bg-white hover:bg-paper text-pine-dark no-underline transition-colors">Contact TeamUp</a>
        </div>
      </div>
    </div>

    <!-- Frequently Asked Questions (FAQ) -->
    <div class="max-w-[840px] mx-auto mb-10">
      <div class="text-center mb-9">
        <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">Common Questions</span>
        <h2 class="font-serif text-[28px] mt-2 text-pine-dark">Frequently Asked Questions</h2>
      </div>

      <div class="flex flex-col gap-4">
        
        <div class="<?= $rev ?> bg-white border border-line rounded-card px-6 py-5 shadow-sm">
          <h4 class="font-serif text-lg text-pine-dark m-0 mb-2">Is TeamUp free to join?</h4>
          <p class="text-ink-soft m-0 text-[14.5px] leading-relaxed">
            Yes! Creating an account, browsing grounds, finding neighbors, and joining community games is 100% free. If a match organizers books a paid private turf, the ground fee is split transparently between players.
          </p>
        </div>

        <div class="<?= $rev ?> bg-white border border-line rounded-card px-6 py-5 shadow-sm">
          <h4 class="font-serif text-lg text-pine-dark m-0 mb-2">How do skill levels work?</h4>
          <p class="text-ink-soft m-0 text-[14.5px] leading-relaxed">
            Every match is marked as Beginner, Intermediate, Advanced, or All Levels. You can see the intended pace before joining, ensuring competitive balance and no pressure.
          </p>
        </div>

        <div class="<?= $rev ?> bg-white border border-line rounded-card px-6 py-5 shadow-sm">
          <h4 class="font-serif text-lg text-pine-dark m-0 mb-2">Can I register our local turf or ground?</h4>
          <p class="text-ink-soft m-0 text-[14.5px] leading-relaxed">
            Absolutely. Ground managers and community park coordinators can list their venues with amenities, hours, and photos so nearby players can discover and book games there.
          </p>
        </div>

        <div class="<?= $rev ?> bg-white border border-line rounded-card px-6 py-5 shadow-sm">
          <h4 class="font-serif text-lg text-pine-dark m-0 mb-2">What happens if someone doesn't show up?</h4>
          <p class="text-ink-soft m-0 text-[14.5px] leading-relaxed">
            Our attendance and fair play rating system tracks reliability. Repeat no-shows lose priority booking on popular matches, keeping games dependable for committed players.
          </p>
        </div>

      </div>
    </div>

  </div>
</section>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
