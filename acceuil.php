<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QCM Dev Web</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: #ffffff;
      --surface: #f7f7f5;
      --surface2: #eeeeeb;
      --ink: #111111;
      --ink-light: #555550;
      --muted: #999994;
      --border: #e0e0db;
      --accent-php:  #6c63ff;
      --accent-html: #e44d26;
      --accent-css:  #2965f1;
      --accent-sql:  #00758f;
      --accent-js:   #f0b429;
      --accent-sec:  #c0392b;
      --font-display: 'Playfair Display', Georgia, serif;
      --font-body: 'DM Sans', sans-serif;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: var(--font-body);
      background: var(--bg);
      color: var(--ink);
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* ───── HEADER / NAV ───── */
    header {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 3rem;
      background: rgba(255,255,255,.95);
      backdrop-filter: blur(14px);
      border-bottom: 1px solid var(--border);
    }

    .logo {
      font-family: var(--font-display);
      font-size: 1.3rem;
      font-weight: 900;
      color: var(--ink);
      letter-spacing: -.01em;
      text-decoration: none;
    }
    .logo span { color: var(--accent-php); }

    nav {
      display: flex;
      align-items: center;
      gap: 2rem;
    }

    nav a {
      color: var(--ink-light);
      text-decoration: none;
      font-size: .875rem;
      font-weight: 500;
      letter-spacing: .02em;
      transition: color .2s;
    }
    nav a:hover { color: var(--ink); }

    .nav-btn {
      background: var(--ink);
      color: #fff !important;
      padding: .5rem 1.4rem;
      border-radius: 99px;
      font-weight: 600 !important;
      transition: opacity .2s !important;
    }
    .nav-btn:hover { opacity: .75; }

    .nav-btn-outline {
      border: 1.5px solid var(--ink);
      color: var(--ink) !important;
      padding: .45rem 1.3rem;
      border-radius: 99px;
      font-weight: 600 !important;
      transition: background .2s, color .2s !important;
    }
    .nav-btn-outline:hover {
      background: var(--ink);
      color: #fff !important;
    }

    /* Burger mobile */
    .burger {
      display: none;
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 4px;
    }
    .burger span {
      display: block;
      width: 22px;
      height: 2px;
      background: var(--ink);
      border-radius: 2px;
      transition: transform .3s, opacity .3s;
    }

    @media (max-width: 720px) {
      header { padding: 1rem 1.5rem; }
      .burger { display: flex; }
      nav {
        display: none;
        position: absolute;
        top: 100%;
        left: 0; right: 0;
        background: #fff;
        border-bottom: 1px solid var(--border);
        flex-direction: column;
        padding: 1.5rem;
        gap: 1.2rem;
        align-items: flex-start;
      }
      nav.open { display: flex; }
    }

    /* ───── HERO ───── */
    .hero {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 9rem 2rem 5rem;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      background: var(--ink);
      color: #fff;
      font-size: .72rem;
      font-weight: 600;
      letter-spacing: .1em;
      text-transform: uppercase;
      padding: .4rem 1.1rem;
      border-radius: 99px;
      margin-bottom: 2.2rem;
      animation: fadeUp .6s ease both;
    }

    .hero h1 {
      font-family: var(--font-display);
      font-size: clamp(2.8rem, 7vw, 6rem);
      font-weight: 900;
      line-height: 1.07;
      letter-spacing: -.03em;
      max-width: 820px;
      animation: fadeUp .65s ease .1s both;
    }

    .hero h1 .underline {
      position: relative;
      display: inline-block;
    }
    .hero h1 .underline::after {
      content: '';
      position: absolute;
      left: 0; right: 0;
      bottom: 4px;
      height: 5px;
      background: var(--accent-php);
      border-radius: 2px;
      opacity: .25;
    }

    .hero-sub {
      margin-top: 1.6rem;
      font-size: 1.1rem;
      color: var(--ink-light);
      max-width: 480px;
      line-height: 1.75;
      animation: fadeUp .65s ease .2s both;
    }

    .hero-actions {
      display: flex;
      gap: 1rem;
      margin-top: 2.8rem;
      flex-wrap: wrap;
      justify-content: center;
      animation: fadeUp .65s ease .3s both;
    }

    .btn-primary {
      background: var(--ink);
      color: #fff;
      font-family: var(--font-body);
      font-size: 1rem;
      font-weight: 600;
      padding: .9rem 2.2rem;
      border: none;
      border-radius: 99px;
      cursor: pointer;
      text-decoration: none;
      transition: transform .2s, opacity .2s;
      display: inline-block;
    }
    .btn-primary:hover { transform: translateY(-2px); opacity: .8; }

    .btn-secondary {
      background: transparent;
      color: var(--ink);
      font-family: var(--font-body);
      font-size: 1rem;
      font-weight: 500;
      padding: .9rem 2.2rem;
      border: 1.5px solid var(--ink);
      border-radius: 99px;
      cursor: pointer;
      text-decoration: none;
      transition: background .2s, color .2s;
      display: inline-block;
    }
    .btn-secondary:hover { background: var(--ink); color: #fff; }

    /* ───── STATS BAR ───── */
    .stats-bar {
      margin-top: 4rem;
      display: flex;
      gap: 3rem;
      justify-content: center;
      flex-wrap: wrap;
      animation: fadeUp .65s ease .45s both;
    }

    .stat-item { text-align: center; }
    .stat-num {
      font-family: var(--font-display);
      font-size: 2.2rem;
      font-weight: 900;
      line-height: 1;
    }
    .stat-label {
      font-size: .75rem;
      color: var(--muted);
      letter-spacing: .06em;
      text-transform: uppercase;
      margin-top: .3rem;
    }
    .sep-line {
      width: 1px;
      height: 40px;
      background: var(--border);
      align-self: center;
    }

    /* ───── SECTIONS ───── */
    section { padding: 5rem 2rem; }

    .section-eyebrow {
      text-align: center;
      font-size: .72rem;
      font-weight: 600;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: .9rem;
    }

    .section-title {
      font-family: var(--font-display);
      font-size: clamp(1.9rem, 3.5vw, 2.8rem);
      font-weight: 900;
      text-align: center;
      letter-spacing: -.02em;
      margin-bottom: .5rem;
    }

    .section-sub {
      text-align: center;
      color: var(--ink-light);
      margin-bottom: 3.5rem;
      font-size: .95rem;
      line-height: 1.7;
    }

    /* ───── THÈMES ───── */
    .themes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 1.2rem;
      max-width: 1060px;
      margin: 0 auto;
    }

    .theme-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 18px;
      padding: 1.8rem 1.6rem;
      cursor: pointer;
      transition: transform .25s, border-color .25s, box-shadow .25s;
      text-decoration: none;
      display: block;
      position: relative;
      overflow: hidden;
    }

    .theme-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: var(--c);
      border-radius: 18px 18px 0 0;
    }

    .theme-card:hover {
      transform: translateY(-5px);
      border-color: var(--c);
      box-shadow: 0 12px 40px rgba(0,0,0,.08);
    }

    .theme-card[data-theme="php"]  { --c: var(--accent-php); }
    .theme-card[data-theme="html"] { --c: var(--accent-html); }
    .theme-card[data-theme="css"]  { --c: var(--accent-css); }
    .theme-card[data-theme="sql"]  { --c: var(--accent-sql); }
    .theme-card[data-theme="js"]   { --c: var(--accent-js); }
    .theme-card[data-theme="sec"]  { --c: var(--accent-sec); }

    .theme-icon { font-size: 2rem; margin-bottom: 1rem; display: block; }

    .theme-card h3 {
      font-family: var(--font-display);
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: .4rem;
      color: var(--ink);
    }

    .theme-card p {
      font-size: .85rem;
      color: var(--ink-light);
      line-height: 1.65;
      margin-bottom: 1.3rem;
    }

    .theme-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .qs-count {
      font-size: .8rem;
      font-weight: 600;
      color: var(--c);
      background: color-mix(in srgb, var(--c) 10%, transparent);
      padding: .3rem .8rem;
      border-radius: 99px;
      border: 1px solid color-mix(in srgb, var(--c) 20%, transparent);
    }

    .theme-arrow {
      font-size: 1rem;
      color: var(--muted);
      transition: color .2s, transform .2s;
    }
    .theme-card:hover .theme-arrow { color: var(--ink); transform: translateX(3px); }

    /* ───── STRUCTURE BDD ───── */
    .bdd-section {
      background: var(--surface);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }

    .schema-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 1.2rem;
      max-width: 900px;
      margin: 0 auto;
    }

    .schema-card {
      background: var(--bg);
      border: 1.5px solid var(--border);
      border-radius: 14px;
      padding: 1.4rem;
    }

    .schema-card h4 {
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: .8rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .schema-card ul { list-style: none; display: flex; flex-direction: column; gap: .4rem; }

    .schema-card li {
      font-size: .8rem;
      color: var(--ink-light);
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .field-tag {
      font-size: .65rem;
      font-weight: 700;
      padding: .15rem .45rem;
      border-radius: 4px;
      letter-spacing: .04em;
      text-transform: uppercase;
      flex-shrink: 0;
    }
    .pk { background: #fffbe6; color: #c49000; border: 1px solid #f0d060; }
    .fk { background: #e8f0ff; color: #3060c0; border: 1px solid #b0c8ff; }
    .t  { background: #f0f0f0; color: #666;    border: 1px solid #ddd; }

    /* ───── STEPS ───── */
    .steps-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 2.5rem;
      max-width: 920px;
      margin: 0 auto;
    }

    .step { text-align: center; }

    .step-bubble {
      width: 52px; height: 52px;
      background: var(--ink);
      color: #fff;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-display);
      font-size: 1.2rem;
      font-weight: 900;
      margin-bottom: 1rem;
    }

    .step h3 { font-size: 1rem; font-weight: 600; margin-bottom: .4rem; }
    .step p { font-size: .85rem; color: var(--ink-light); line-height: 1.65; }

    /* ───── ROLES ───── */
    .roles-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      max-width: 760px;
      margin: 0 auto;
    }

    @media (max-width: 560px) { .roles-grid { grid-template-columns: 1fr; } }

    .role-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
    }

    .role-icon { font-size: 2rem; margin-bottom: .8rem; display: block; }
    .role-card h3 { font-family: var(--font-display); font-size: 1.3rem; font-weight: 700; margin-bottom: .6rem; }
    .role-card p  { font-size: .875rem; color: var(--ink-light); line-height: 1.7; margin-bottom: 1.2rem; }
    .role-card ul { list-style: none; display: flex; flex-direction: column; gap: .4rem; }
    .role-card li { font-size: .83rem; color: var(--ink-light); display: flex; align-items: flex-start; gap: .5rem; }
    .role-card li::before { content: '✓'; color: var(--ink); font-weight: 700; flex-shrink: 0; }

    /* ───── CTA ───── */
    .cta-box {
      background: var(--ink);
      color: #fff;
      border-radius: 24px;
      padding: 4.5rem 2rem;
      text-align: center;
      max-width: 680px;
      margin: 0 auto;
    }

    .cta-box h2 {
      font-family: var(--font-display);
      font-size: clamp(1.8rem, 4vw, 2.8rem);
      font-weight: 900;
      margin-bottom: 1rem;
      letter-spacing: -.02em;
    }

    .cta-box p { color: rgba(255,255,255,.7); margin-bottom: 2.2rem; font-size: .95rem; line-height: 1.7; }

    .btn-white {
      background: #fff;
      color: var(--ink);
      font-family: var(--font-body);
      font-size: 1rem;
      font-weight: 700;
      padding: .95rem 2.5rem;
      border: none;
      border-radius: 99px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: transform .2s, opacity .2s;
    }
    .btn-white:hover { transform: translateY(-2px); opacity: .9; }

    /* ───── FOOTER ───── */
    footer {
      border-top: 1px solid var(--border);
      padding: 2rem 3rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
    }

    footer p { font-size: .8rem; color: var(--muted); }

    footer nav { display: flex; gap: 1.5rem; }
    footer nav a {
      font-size: .8rem;
      color: var(--muted);
      text-decoration: none;
      transition: color .2s;
    }
    footer nav a:hover { color: var(--ink); }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 640px) {
      .stats-bar { gap: 1.5rem; }
      .sep-line { display: none; }
      footer { flex-direction: column; text-align: center; }
      footer nav { justify-content: center; }
    }
  </style>
</head>
<body>

<!-- ══ HEADER ══ -->
<header>
  <a href="acceuil.php" class="logo">QCM<span>.</span>Dev</a>

  <button class="burger" id="burger" aria-label="Menu" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>

  <nav id="nav">
    <a href="#themes">Thèmes</a>
    <a href="#comment">Comment ça marche</a>
    <a href="#bdd">Base de données</a>
    <a href="inscription.php" class="nav-btn-outline">Inscription</a>
    <a href="connexion.php" class="nav-btn">Se connecter</a>
  </nav>
</header>

<!-- ══ HERO ══ -->
<div class="hero">
  <span class="hero-badge">💻 100 questions · Développement Web</span>

  <h1>
    Maîtrises-tu vraiment<br/>
    le <span class="underline">dev web</span> ?
  </h1>

  <p class="hero-sub">
    PHP, HTML5, CSS3, SQL, JavaScript, Sécurité —
    100 questions pour mesurer tes connaissances honnêtement.
  </p>

  <div class="hero-actions">
    <a href="inscription.php" class="btn-primary">Commencer gratuitement</a>
    <a href="#comment" class="btn-secondary">Comment ça marche</a>
  </div>

  <div class="stats-bar">
    <div class="stat-item">
      <div class="stat-num">100</div>
      <div class="stat-label">Questions</div>
    </div>
    <div class="sep-line"></div>
    <div class="stat-item">
      <div class="stat-num">6</div>
      <div class="stat-label">Thèmes</div>
    </div>
    <div class="sep-line"></div>
    <div class="stat-item">
      <div class="stat-num">4</div>
      <div class="stat-label">Choix par question</div>
    </div>
    <div class="sep-line"></div>
    <div class="stat-item">
      <div class="stat-num">2</div>
      <div class="stat-label">Rôles (user / admin)</div>
    </div>
  </div>
</div>

<!-- ══ THÈMES ══ -->
<section id="themes">
  <p class="section-eyebrow">Explorer les thèmes</p>
  <h2 class="section-title">6 domaines du développement web</h2>
  <p class="section-sub">Connecte-toi pour démarrer un QCM sur le thème de ton choix.</p>

  <div class="themes-grid">

    <a href="connexion.php" class="theme-card" data-theme="php">
      <span class="theme-icon">🐘</span>
      <h3>PHP Core &amp; Syntaxe</h3>
      <p>Variables, superglobales, sessions, fonctions, boucles et gestion de tableaux.</p>
      <div class="theme-footer">
        <span class="qs-count">20 questions</span>
        <span class="theme-arrow">→</span>
      </div>
    </a>

    <a href="connexion.php" class="theme-card" data-theme="html">
      <span class="theme-icon">🧱</span>
      <h3>HTML5</h3>
      <p>Balises sémantiques, formulaires, attributs, accessibilité et structure de page.</p>
      <div class="theme-footer">
        <span class="qs-count">20 questions</span>
        <span class="theme-arrow">→</span>
      </div>
    </a>

    <a href="connexion.php" class="theme-card" data-theme="css">
      <span class="theme-icon">🎨</span>
      <h3>CSS3</h3>
      <p>Sélecteurs, Flexbox, Grid, responsive design, animations et propriétés visuelles.</p>
      <div class="theme-footer">
        <span class="qs-count">20 questions</span>
        <span class="theme-arrow">→</span>
      </div>
    </a>

    <a href="connexion.php" class="theme-card" data-theme="sql">
      <span class="theme-icon">🗄️</span>
      <h3>SQL &amp; Bases de données</h3>
      <p>Requêtes SELECT, JOIN, GROUP BY, PDO, clés primaires et étrangères.</p>
      <div class="theme-footer">
        <span class="qs-count">20 questions</span>
        <span class="theme-arrow">→</span>
      </div>
    </a>

    <a href="connexion.php" class="theme-card" data-theme="js">
      <span class="theme-icon">⚡</span>
      <h3>JavaScript</h3>
      <p>DOM, événements, fonctions, tableaux, setTimeout et manipulation de l'URL.</p>
      <div class="theme-footer">
        <span class="qs-count">10 questions</span>
        <span class="theme-arrow">→</span>
      </div>
    </a>

    <a href="connexion.php" class="theme-card" data-theme="sec">
      <span class="theme-icon">🔒</span>
      <h3>Sécurité &amp; Architecture</h3>
      <p>Injections SQL, XSS, CSRF, hachage, MVC, HTTPS et codes de statut HTTP.</p>
      <div class="theme-footer">
        <span class="qs-count">10 questions</span>
        <span class="theme-arrow">→</span>
      </div>
    </a>

  </div>
</section>

<!-- ══ STRUCTURE BDD ══ -->
<section id="bdd" class="bdd-section">
  <p class="section-eyebrow">Base de données</p>
  <h2 class="section-title">Structure de la base <code style="font-size:.8em;background:#eee;padding:.2rem .5rem;border-radius:6px;font-family:monospace;">qcm1</code></h2>
  <p class="section-sub">4 tables MySQL reliées entre elles pour gérer utilisateurs, questions, tentatives et réponses.</p>

  <div class="schema-grid">

    <div class="schema-card">
      <h4>👤 utilisateurs</h4>
      <ul>
        <li><span class="field-tag pk">PK</span> id_utilisateur</li>
        <li><span class="field-tag t">VARCHAR</span> nom</li>
        <li><span class="field-tag t">VARCHAR</span> prenom</li>
        <li><span class="field-tag t">VARCHAR</span> email</li>
        <li><span class="field-tag t">VARCHAR</span> mot_de_passe</li>
        <li><span class="field-tag t">ENUM</span> role (user/admin)</li>
        <li><span class="field-tag t">TINYINT</span> est_bloque</li>
      </ul>
    </div>

    <div class="schema-card">
      <h4>❓ questions</h4>
      <ul>
        <li><span class="field-tag pk">PK</span> id_question</li>
        <li><span class="field-tag t">TEXT</span> enonce</li>
      </ul>
    </div>

    <div class="schema-card">
      <h4>💬 reponses</h4>
      <ul>
        <li><span class="field-tag pk">PK</span> id_reponse</li>
        <li><span class="field-tag fk">FK</span> id_question</li>
        <li><span class="field-tag t">VARCHAR</span> texte_reponse</li>
        <li><span class="field-tag t">TINYINT</span> est_correcte</li>
      </ul>
    </div>

    <div class="schema-card">
      <h4>📋 tentatives</h4>
      <ul>
        <li><span class="field-tag pk">PK</span> id</li>
        <li><span class="field-tag fk">FK</span> utilisateur_id</li>
        <li><span class="field-tag t">FLOAT</span> score</li>
        <li><span class="field-tag t">DATETIME</span> date</li>
      </ul>
    </div>

  </div>
</section>

<!-- ══ COMMENT ÇA MARCHE ══ -->
<section id="comment">
  <p class="section-eyebrow">Fonctionnement</p>
  <h2 class="section-title">Comment ça marche</h2>
  <p class="section-sub">Simple, rapide, tracé en base à chaque tentative.</p>

  <div class="steps-row">
    <div class="step">
      <div class="step-bubble">1</div>
      <h3>Crée ton compte</h3>
      <p>Inscris-toi avec ton e-mail. Ton historique de tentatives est sauvegardé automatiquement.</p>
    </div>
    <div class="step">
      <div class="step-bubble">2</div>
      <h3>Connecte-toi</h3>
      <p>Accède à ton espace avec email et mot de passe. Ta session est sécurisée.</p>
    </div>
    <div class="step">
      <div class="step-bubble">3</div>
      <h3>Lance un QCM</h3>
      <p>10 questions aléatoires parmi les 100 disponibles. 4 propositions par question.</p>
    </div>
    <div class="step">
      <div class="step-bubble">4</div>
      <h3>Obtiens ton score</h3>
      <p>Chaque bonne réponse vaut 2 points. Ton score sur 20 est calculé et affiché.</p>
    </div>
  </div>
</section>

<!-- ══ RÔLES ══ -->
<section style="background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
  <p class="section-eyebrow">Accès &amp; rôles</p>
  <h2 class="section-title">Deux profils distincts</h2>
  <p class="section-sub">La table <code style="font-family:monospace;background:#eee;padding:.1rem .4rem;border-radius:4px;">utilisateurs</code> gère deux rôles via une colonne ENUM.</p>

  <div class="roles-grid">
    <div class="role-card">
      <span class="role-icon">🎓</span>
      <h3>Utilisateur</h3>
      <p>Profil standard — accès au quiz et à l'historique personnel.</p>
      <ul>
        <li>Passer les QCM</li>
        <li>Consulter ses tentatives</li>
        <li>Voir ses bonnes et mauvaises réponses</li>
        <li>Suivre l'évolution de son score</li>
      </ul>
    </div>

    <div class="role-card">
      <span class="role-icon">⚙️</span>
      <h3>Administrateur</h3>
      <p>Profil élevé — gestion complète du contenu et des utilisateurs.</p>
      <ul>
        <li>Ajouter / modifier / supprimer des questions</li>
        <li>Gérer les comptes utilisateurs</li>
        <li>Bloquer / débloquer des utilisateurs</li>
        <li>Accéder à l'interface d'administration</li>
      </ul>
    </div>
  </div>
</section>

<!-- ══ CTA ══ -->
<section id="start">
  <div class="cta-box">
    <h2>Prêt à te tester ?</h2>
    <p>
      100 questions · 6 thèmes · Résultats enregistrés en base.<br/>
      Crée ton compte en 30 secondes et démarre ton premier QCM.
    </p>
    <a href="inscription.php" class="btn-white">Créer mon compte →</a>
  </div>
</section>

<!-- ══ FOOTER ══ -->
<footer>
  <a href="acceuil.php" class="logo" style="font-size:1rem;">QCM<span>.</span>Dev</a>
  <p>© 2025 QCM Dev Web · Base MySQL <code style="font-family:monospace;">qcm1</code></p>
  <nav>
    <a href="connexion.php">Connexion</a>
    <a href="inscription.php">Inscription</a>
    <a href="admin.php">Admin</a>
  </nav>
</footer>

<script>
  const burger = document.getElementById('burger');
  const nav    = document.getElementById('nav');

  burger.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    burger.setAttribute('aria-expanded', open);
  });

  document.querySelectorAll('nav a[href^="#"]').forEach(link => {
    link.addEventListener('click', () => {
      nav.classList.remove('open');
      burger.setAttribute('aria-expanded', false);
    });
  });
</script>

</body>
</html>
 