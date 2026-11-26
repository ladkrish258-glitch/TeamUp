</div> <!-- end content-wrapper -->

<script>
    const canvas = document.getElementById('dot-canvas');
    const ctx = canvas.getContext('2d');
    let width, height;
    let dots = [];
    
    // --- Configuration ---
    const spacing = 35;         // Distance between dots
    const mouseRadius = 220;    // How close the mouse needs to be to interact
    const dotBaseSize = 1.5;    // Normal dot size
    const dotMaxSize = 5;       // Hovered dot size
    const repulsion = 45;       // How far dots are pushed away
    const tension = 0.08;       // Spring tension returning dots to original position
    const friction = 0.75;      // Spring friction (dampening)
    
    const mouse = { x: -1000, y: -1000 };
    class Dot {
        constructor(x, y) {
            this.baseX = x;
            this.baseY = y;
            this.x = x;
            this.y = y;
            this.vx = 0;
            this.vy = 0;
            this.size = dotBaseSize;
            const faIcons = ['\uf1e3', '\uf434', '\uf45f', '\uf44e'];
            this.icon = faIcons[Math.floor(Math.random() * faIcons.length)];
        }
        update() {
            const dx = mouse.x - this.baseX;
            const dy = mouse.y - this.baseY;
            const distance = Math.sqrt(dx * dx + dy * dy);
            let targetX = this.baseX;
            let targetY = this.baseY;
            let targetSize = dotBaseSize;
            
            if (distance < mouseRadius) {
                const force = (mouseRadius - distance) / mouseRadius;
                const angle = Math.atan2(dy, dx);
                
                targetX -= Math.cos(angle) * force * repulsion;
                targetY -= Math.sin(angle) * force * repulsion;
                
                targetSize = dotBaseSize + force * (dotMaxSize - dotBaseSize);
            }
            this.vx += (targetX - this.x) * tension;
            this.vy += (targetY - this.y) * tension;
            
            this.vx *= friction;
            this.vy *= friction;
            
            this.x += this.vx;
            this.y += this.vy;
            
            this.size += (targetSize - this.size) * 0.15;
        }
        draw() {
            ctx.font = '900 ' + Math.max(0, this.size * 3.5) + 'px "Font Awesome 6 Free"';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = 'rgba(30, 64, 50, 0.2)'; // Restored to default pine color with low opacity
            ctx.fillText(this.icon, this.x, this.y);
        }
    }
    function init() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        dots = [];
        
        const padding = 100;
        
        for (let x = -padding; x <= width + padding; x += spacing) {
            for (let y = -padding; y <= height + padding; y += spacing) {
                dots.push(new Dot(x, y));
            }
        }
    }
    function animate() {
        ctx.clearRect(0, 0, width, height);
        
        dots.forEach(dot => {
            dot.update();
            dot.draw();
        });
        
        requestAnimationFrame(animate);
    }
    
    window.addEventListener('resize', init);
    
    window.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });
    
    window.addEventListener('mouseleave', () => {
        mouse.x = -1000;
        mouse.y = -1000;
    });
    
    init();
    animate();
</script>

<footer class="bg-pine-dark text-paper/80 pt-16 pb-10">
  <div class="max-w-[1160px] mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr] gap-10 pb-10 border-b border-white/10">
      <div>
        <a href="../Home/home.php" class="inline-flex items-center gap-2.5 font-serif font-bold text-[22px] tracking-tight text-paper no-underline mb-3">
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
        <p class="max-w-[300px] text-sm text-paper/70 leading-relaxed m-0">The private space for your actual street. Free, verified by address, one neighborhood at a time.</p>
        <div class="flex gap-2.5 mt-4">
          <a class="w-[38px] h-[38px] rounded-full border border-white/20 flex items-center justify-center text-paper/80 hover:bg-white/10 hover:text-paper transition-colors" href="https://instagram.com/porchlightapp" target="_blank" rel="noopener" aria-label="Porchlight on Instagram">
            <svg width="19" height="19" viewBox="0 0 24 24" class="fill-current">
              <path d="M12 2c-2.72 0-3.06.01-4.12.06-1.06.05-1.79.22-2.43.47-.66.25-1.22.6-1.77 1.15S2.78 4.79 2.53 5.45c-.25.64-.42 1.37-.47 2.43C2.01 8.94 2 9.28 2 12s.01 3.06.06 4.12c.05 1.06.22 1.79.47 2.43.25.66.6 1.22 1.15 1.77s1.11.9 1.77 1.15c.64.25 1.37.42 2.43.47C8.94 21.99 9.28 22 12 22s3.06-.01 4.12-.06c1.06-.05 1.79-.22 2.43-.47.66-.25 1.22-.6 1.77-1.15s.9-1.11 1.15-1.77c.25-.64.42-1.37.47-2.43.05-1.06.06-1.4.06-4.12s-.01-3.06-.06-4.12c-.05-1.06-.22-1.79-.47-2.43a4.93 4.93 0 0 0-1.15-1.77 4.93 4.93 0 0 0-1.77-1.15c-.64-.25-1.37-.42-2.43-.47C15.06 2.01 14.72 2 12 2zm0 1.8c2.67 0 2.99.01 4.04.06.98.04 1.5.21 1.86.35.47.18.8.4 1.15.75s.57.68.75 1.15c.14.36.31.88.35 1.86.05 1.05.06 1.37.06 4.04s-.01 2.99-.06 4.04c-.04.98-.21 1.5-.35 1.86-.18.47-.4.8-.75 1.15s-.68.57-1.15.75c-.36.14-.88.31-1.86.35-1.05.05-1.37.06-4.04.06s-2.99-.01-4.04-.06c-.98-.04-1.5-.21-1.86-.35a3.1 3.1 0 0 1-1.15-.75 3.1 3.1 0 0 1-.75-1.15c-.14-.36-.31-.88-.35-1.86-.05-1.05-.06-1.37-.06-4.04s.01-2.99.06-4.04c.04-.98.21-1.5.35-1.86.18-.47.4-.8.75-1.15s.68-.57 1.15-.75c.36-.14.88-.31 1.86-.35 1.05-.05 1.37-.06 4.04-.06z" />
              <path d="M12 7.35a4.65 4.65 0 1 0 0 9.3 4.65 4.65 0 0 0 0-9.3zm0 7.67a3.02 3.02 0 1 1 0-6.04 3.02 3.02 0 0 1 0 6.04z" />
              <circle cx="16.8" cy="7.2" r="1.08" />
            </svg>
          </a>
          <a class="w-[38px] h-[38px] rounded-full border border-white/20 flex items-center justify-center text-paper/80 hover:bg-white/10 hover:text-paper transition-colors" href="https://facebook.com/porchlightapp" target="_blank" rel="noopener" aria-label="Porchlight on Facebook">
            <svg width="19" height="19" viewBox="0 0 24 24" class="fill-current">
              <path d="M13.5 21v-8.15h2.74l.41-3.18h-3.15V7.67c0-.92.25-1.55 1.58-1.55h1.68V3.27C16.47 3.19 15.4 3.1 14.16 3.1c-2.6 0-4.38 1.59-4.38 4.5v2.07H7.03v3.18h2.75V21h3.72z" />
            </svg>
          </a>
        </div>
      </div>

      <div>
        <h4 class="font-mono text-[11px] tracking-[0.12em] uppercase text-paper/60 mb-3.5 mt-0">Explore</h4>
        <ul class="list-none m-0 p-0 space-y-2.5 text-sm">
          <li><a href="../MatchNote/matchnote.php" class="text-paper/85 hover:text-paper no-underline">MatchNote</a></li>
          <li><a href="../ContactUs/contact.php" class="text-paper/85 hover:text-paper no-underline">Contact Us</a></li>
          <li><a href="../AboutUs/about.php" class="text-paper/85 hover:text-paper no-underline">About Us</a></li>
        </ul>
      </div>

      <div>
        <h4 class="font-mono text-[11px] tracking-[0.12em] uppercase text-paper/60 mb-3.5 mt-0">Account</h4>
        <ul class="list-none m-0 p-0 space-y-2.5 text-sm">
          <li><a href="../Login/login.php" class="text-paper/85 hover:text-paper no-underline">Log in</a></li>
          <li><a href="../Login/register.php" class="text-paper/85 hover:text-paper no-underline">Register</a></li>
          <li><a href="../ContactUs/contact.php" class="text-paper/85 hover:text-paper no-underline">Contact us</a></li>
        </ul>
      </div>
    </div>

    <div class="pt-6 flex flex-wrap gap-3 justify-between text-[13px] text-paper/55">
      <div>&copy; <?php echo date("Y"); ?> Porchlight. Built block by block.</div>
      <div>Privacy &middot; Guidelines &middot; Help</div>
    </div>
  </div>
</footer>

<script>
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('in');
      }
    });
  }, {
    threshold: 0.15
  });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  document.querySelectorAll('.address-form').forEach(f => {
    f.addEventListener('submit', e => {
      e.preventDefault();
      const input = f.querySelector('input');
      const btn = f.querySelector('button');
      const original = btn.textContent;
      btn.textContent = 'Looking…';
      setTimeout(() => {
        btn.textContent = 'Found it →';
      }, 700);
      setTimeout(() => {
        btn.textContent = original;
        input.value = '';
      }, 2400);
    });
  });

  // Login / Register tab toggle (only present on login.php)
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tab;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(target).classList.add('active');
    });
  });

  // Scrollspy logic for navigation links
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-links a.nav-anchor');
  
  if (sections.length > 0 && navLinks.length > 0) {
    window.addEventListener('scroll', () => {
      let current = '';
      sections.forEach(section => {
        const sectionTop = section.offsetTop;
        if (window.scrollY >= (sectionTop - 300)) {
          current = section.getAttribute('id');
        }
      });
      
      if (!current && sections.length > 0) {
        current = sections[0].getAttribute('id');
      }

      navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').endsWith('#' + current)) {
          link.classList.add('active');
        }
      });
    });
  }
</script>
</body>

</html>
