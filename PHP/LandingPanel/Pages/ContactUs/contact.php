<?php
$page = 'contact';
$pageTitle = 'Contact Us';

$formSubmitted = false;
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '')    { $errors[] = 'Please tell us your name.'; }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Please enter a valid email address.'; }
    if ($message === '') { $errors[] = 'Please write a short message.'; }

    if (empty($errors)) {
        $formSubmitted = true;
    }
}

require_once dirname(__DIR__) . '/Components/Header/header.php';
$rev = 'reveal opacity-0 translate-y-4 transition-all duration-700 [&.in]:opacity-100 [&.in]:translate-y-0';
$fieldInput = 'w-full px-3.5 py-2.5 rounded-xl border border-line bg-paper text-ink text-[15px] outline-none focus:border-pine focus:bg-white transition-colors';
$fieldLabel = 'block text-sm font-semibold text-pine-dark mb-1.5';
?>

<section class="py-14 pb-8">
  <div class="max-w-[1160px] mx-auto px-6">
    <span class="font-mono text-xs tracking-[0.12em] uppercase text-brick font-bold">We're around</span>
    <h1 class="font-serif font-semibold text-4xl sm:text-5xl text-pine-dark mt-2.5 mb-3">Get in touch.</h1>
    <p class="text-ink-soft text-lg max-w-[600px] m-0">Questions, feedback, or a block that isn't on Porchlight yet — send it over and a real person will get back to you.</p>
  </div>
</section>

<section class="pt-6 pb-20">
  <div class="max-w-[1160px] mx-auto px-6 grid grid-cols-1 lg:grid-cols-[1.2fr_0.8fr] gap-8 items-start">

    <div class="bg-white border border-line rounded-card p-7 shadow-sm">
      <?php if ($formSubmitted): ?>
        <div class="py-3 px-1">
          <h3 class="font-serif text-2xl text-pine-dark mb-2.5 mt-0">Message sent ✅</h3>
          <p class="text-ink-soft m-0">Thanks, <?php echo htmlspecialchars($name); ?> — we'll reply to <?php echo htmlspecialchars($email); ?> within a day or two.</p>
        </div>
      <?php else: ?>
        <?php if (!empty($errors)): ?>
          <div class="bg-[#FBEAE3] border border-brick text-brick px-4 py-3 rounded-xl mb-5 text-sm">
            <?php foreach ($errors as $err): ?>
              <div>&bull; <?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form class="flex flex-col gap-5" method="post" action="../ContactUs/contact.php">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="name" class="<?= $fieldLabel ?>">Your name</label>
              <input type="text" id="name" name="name" placeholder="Jamie Rivera" class="<?= $fieldInput ?>" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            <div>
              <label for="email" class="<?= $fieldLabel ?>">Email address</label>
              <input type="email" id="email" name="email" placeholder="you@example.com" class="<?= $fieldInput ?>" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
          </div>
          <div>
            <label for="message" class="<?= $fieldLabel ?>">Message</label>
            <textarea id="message" name="message" rows="5" placeholder="Tell us what's on your mind..." class="<?= $fieldInput ?> resize-y"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
          </div>
          <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-full text-[15px] font-semibold bg-pine hover:bg-pine-dark text-paper border-0 cursor-pointer transition-colors">Send message</button>
          <p class="text-xs text-ink-soft text-center m-0">We typically reply within one business day.</p>
        </form>
      <?php endif; ?>
    </div>

    <div class="<?= $rev ?> flex flex-col gap-5">
      <div class="bg-white border border-line rounded-card p-5 flex items-start gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-paper-2 flex items-center justify-center shrink-0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="#1E4032" stroke-width="1.6"/><path d="M22 6L12 13L2 6" stroke="#1E4032" stroke-width="1.6"/></svg>
        </div>
        <div>
          <h4 class="font-serif text-base text-pine-dark m-0 mb-1">Email</h4>
          <p class="text-sm text-ink-soft m-0">hello@porchlight.example<br>We read every message.</p>
        </div>
      </div>
      <div class="bg-white border border-line rounded-card p-5 flex items-start gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-paper-2 flex items-center justify-center shrink-0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.4 2.1L8 9.9a16 16 0 0 0 6 6l1.4-1.4a2 2 0 0 1 2.1-.4c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.8 2.2z" stroke="#1E4032" stroke-width="1.6"/></svg>
        </div>
        <div>
          <h4 class="font-serif text-base text-pine-dark m-0 mb-1">Phone</h4>
          <p class="text-sm text-ink-soft m-0">(555) 018-2043<br>Mon–Fri, 9am–6pm</p>
        </div>
      </div>
      <div class="bg-white border border-line rounded-card p-5 flex items-start gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-paper-2 flex items-center justify-center shrink-0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z" stroke="#1E4032" stroke-width="1.6"/><circle cx="12" cy="10" r="3" stroke="#1E4032" stroke-width="1.6"/></svg>
        </div>
        <div>
          <h4 class="font-serif text-base text-pine-dark m-0 mb-1">Office</h4>
          <p class="text-sm text-ink-soft m-0">118 Maple Street<br>Portland, OR 97201</p>
        </div>
      </div>
      <div class="bg-white border border-line rounded-card p-5 flex items-start gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-paper-2 flex items-center justify-center shrink-0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#1E4032" stroke-width="1.6"/><path d="M12 6V12L16 14" stroke="#1E4032" stroke-width="1.6" stroke-linecap="round"/></svg>
        </div>
        <div>
          <h4 class="font-serif text-base text-pine-dark m-0 mb-1">Response time</h4>
          <p class="text-sm text-ink-soft m-0">Usually within 24 hours, every day of the week.</p>
        </div>
      </div>
    </div>

  </div>
</section>

<?php require_once dirname(__DIR__) . '/Components/Footer/footer.php'; ?>
