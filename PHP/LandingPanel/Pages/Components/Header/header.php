<?php
// $page is set by each top-level file before including this header,
// used only to highlight the current nav link.
if (!isset($page)) {
  $page = '';
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — TeamUp' : 'TeamUp — Connect and play sports with neighbors'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,500;9..144,600;9..144,700;9..144,900&family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            paper: '#FBF7F0',
            'paper-2': '#F3ECE0',
            ink: '#1E2421',
            'ink-soft': '#56605A',
            pine: '#1E4032',
            'pine-dark': '#122922',
            brick: '#C1512D',
            gold: '#E3A730',
            'gold-dark': '#B4923B',
            line: '#E2DDD8',
          },
          fontFamily: {
            sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
            serif: ['Fraunces', 'Georgia', 'serif'],
            mono: ['"Space Mono"', 'monospace'],
          },
          borderRadius: {
            'card': '18px',
          }
        }
      }
    };
  </script>
</head>

<body class="bg-paper text-ink font-sans antialiased leading-relaxed overflow-x-hidden m-0 p-0">

  <header class="sticky top-0 z-50 bg-paper/90 backdrop-blur-md border-b border-line">
    <nav class="max-w-[1160px] mx-auto px-6 h-[74px] flex items-center justify-between gap-6">
      <a href="../Home/home.php" class="inline-flex items-center gap-2.5 font-serif font-bold text-[22px] tracking-tight text-pine-dark no-underline shrink-0">
        <svg class="w-[34px] h-[34px]" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
          <circle cx="32" cy="14" r="7" fill="#F4B400" />
          <circle cx="18" cy="26" r="5" fill="#1E88E5" />
          <path d="M18 31 L12 46 L18 42 L24 46 L18 31Z" fill="#1E88E5" />
          <circle cx="46" cy="26" r="5" fill="#43A047" />
          <path d="M46 31 L40 46 L46 42 L52 46 L46 31Z" fill="#43A047" />
          <path d="M23 28 Q32 36 41 28" stroke="#F4B400" stroke-width="3" fill="none" />
        </svg>
        <span>TeamUp</span>
      </a>
      <ul class="hidden md:flex list-none m-0 p-0 gap-8 text-[15px] text-ink-soft font-medium">
        <li><a href="../MatchNote/matchnote.php" class="no-underline hover:text-pine-dark transition-colors <?php echo $page === 'matchnote' ? 'text-pine-dark font-semibold' : ''; ?>">MatchNote</a></li>
        <li><a href="../ContactUs/contact.php" class="no-underline hover:text-pine-dark transition-colors <?php echo $page === 'contact' ? 'text-pine-dark font-semibold' : ''; ?>">Contact Us</a></li>
        <li><a href="../AboutUs/about.php" class="no-underline hover:text-pine-dark transition-colors <?php echo $page === 'about' ? 'text-pine-dark font-semibold' : ''; ?>">About Us</a></li>
      </ul>
      <div class="flex items-center gap-3">
        <a href="../Login/login.php" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold border border-line bg-white hover:bg-paper-2 text-pine-dark no-underline transition-all <?php echo $page === 'login' ? 'ring-2 ring-pine/30' : ''; ?>">Log in</a>
        <a href="../Home/home.php#join" class="hidden sm:inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold bg-pine hover:bg-pine-dark text-paper no-underline transition-all">Find my block</a>
      </div>
    </nav>
  </header>

  <!-- Interactive Background Canvas -->
  <canvas id="dot-canvas" class="fixed inset-0 w-screen h-screen z-[1] pointer-events-none"></canvas>

  <div class="relative z-[2]">
