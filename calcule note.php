<?php
session_start();

$conn = mysqli_connect('localhost', 'root', 'root', 'qcm1');
if (!$conn) {
    die('Erreur de connexion : ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8');

$nbBonnesReponses = 0;
$erreurs = [];
$totalQuestions = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'])) {
    $reponsesUtilisateur = $_POST['reponse'];
    $totalQuestions = count($reponsesUtilisateur);

    foreach ($reponsesUtilisateur as $idQuestion => $indexChoisi) {
        $idQuestionSecure = (int)$idQuestion;

        $sql      = "SELECT * FROM questions WHERE id = $idQuestionSecure";
        $resultat = mysqli_query($conn, $sql);
        $q        = mysqli_fetch_assoc($resultat);

        if ($q) {
            $bonneReponseIndex = (int)$q['bonne_reponse'];
            $indexChoisi       = (int)$indexChoisi;

            if ($indexChoisi === $bonneReponseIndex) {
                $nbBonnesReponses++;
            } else {
                $erreurs[] = [
                    'question'      => $q['question'],
                    'reponse_donnee'=> $q['reponse' . $indexChoisi],
                    'bonne_reponse' => $q['reponse' . $bonneReponseIndex]
                ];
            }
        }
    }

    $noteSur20  = ($totalQuestions > 0) ? round(($nbBonnesReponses / $totalQuestions) * 20, 1) : 0;
    $pourcentage = ($totalQuestions > 0) ? round(($nbBonnesReponses / $totalQuestions) * 100) : 0;

    unset($_SESSION['qcm_questions']);
} else {
    header("Location: qcm.php");
    exit();
}

mysqli_close($conn);

// Couleur selon le score
if ($pourcentage >= 80)      { $couleur = '#2ecc71'; $couleurFond = '#edf7ed'; $emoji = '🎉'; $message = 'Excellent résultat !'; }
elseif ($pourcentage >= 60)  { $couleur = '#f39c12'; $couleurFond = '#fef9ec'; $emoji = '👍'; $message = 'Bon travail !'; }
elseif ($pourcentage >= 40)  { $couleur = '#e67e22'; $couleurFond = '#fef3e8'; $emoji = '💪'; $message = 'Peut mieux faire.'; }
else                          { $couleur = '#e74c3c'; $couleurFond = '#fdf2f2'; $emoji = '📚'; $message = 'À réviser !'; }

// Calcul arc SVG (cercle de rayon 54, circonférence ≈ 339.3)
$circonference = 2 * M_PI * 54;
$offset = $circonference - ($pourcentage / 100) * $circonference;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        *,
*::before,
*::after{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

:root{
    --bg:#ffffff;
    --surface:#f7f7f5;
    --surface2:#eeeeeb;
    --ink:#111111;
    --ink-light:#555550;
    --muted:#999994;
    --border:#e0e0db;

    --success:#2ecc71;
    --warning:#f39c12;
    --danger:#e74c3c;

    --font-display:'Playfair Display',serif;
    --font-body:'DM Sans',sans-serif;
}

body{
    font-family:var(--font-body);
    background:var(--bg);
    color:var(--ink);
    min-height:100vh;
    padding:60px 20px;
}

/* CONTAINER */

.container{
    max-width:1000px;
    margin:auto;
}

/* RESULT HERO */

.result-header{
    background:var(--surface);
    border:1.5px solid var(--border);
    border-radius:32px;
    padding:4rem 2rem;
    text-align:center;
    margin-bottom:2rem;
}

.result-msg{
    font-family:var(--font-display);
    font-size:clamp(2.5rem,5vw,4.5rem);
    font-weight:900;
    line-height:1.05;
    letter-spacing:-.03em;
    margin-top:2rem;
}

/* GAUGE */

.gauge-wrap{
    display:flex;
    flex-direction:column;
    align-items:center;
}

.gauge-container{
    position:relative;
    width:180px;
    height:180px;
}

.gauge-svg{
    transform:rotate(-90deg);
}

.gauge-bg{
    fill:none;
    stroke:#ddddda;
    stroke-width:10;
}

.gauge-arc{
    fill:none;
    stroke:<?php echo $couleur; ?>;
    stroke-width:10;
    stroke-linecap:round;
    stroke-dasharray:<?php echo round($circonference,2); ?>;
    stroke-dashoffset:<?php echo round($circonference,2); ?>;
    transition:stroke-dashoffset 1.4s cubic-bezier(.4,0,.2,1);
}

.gauge-center{
    position:absolute;
    inset:0;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
}

.gauge-pct{
    font-family:var(--font-display);
    font-size:3rem;
    font-weight:900;
    color:<?php echo $couleur; ?>;
}

.gauge-label{
    font-size:.75rem;
    text-transform:uppercase;
    letter-spacing:.12em;
    color:var(--muted);
}

/* SCORE CARDS */

.score-cards{
    margin-top:2.5rem;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:1rem;
    width:100%;
    max-width:600px;
}

.score-card{
    background:#fff;
    border:1.5px solid var(--border);
    border-radius:24px;
    padding:2rem;
    transition:.25s;
}

.score-card:hover{
    transform:translateY(-5px);
}

.score-card .val{
    font-family:var(--font-display);
    font-size:2.4rem;
    font-weight:900;
    color:<?php echo $couleur; ?>;
}

.score-card .lbl{
    margin-top:.6rem;
    color:var(--muted);
    font-size:.8rem;
    letter-spacing:.08em;
    text-transform:uppercase;
}

/* CONTENT */

.content{
    margin-top:2rem;
}

.error-title{
    font-family:var(--font-display);
    font-size:2rem;
    font-weight:800;
    margin-bottom:2rem;
}

/* ERROR BLOCK */

.error-block{
    background:var(--surface);
    border:1.5px solid var(--border);
    border-radius:24px;
    padding:1.6rem;
    margin-bottom:1rem;
    transition:.2s;
}

.error-block:hover{
    transform:translateY(-3px);
}

.error-block p{
    line-height:1.7;
    margin-top:.5rem;
}

.wrong{
    color:var(--danger);
    font-weight:700;
}

.correct{
    color:var(--success);
    font-weight:700;
}

/* PERFECT BOX */

.perfect-box{
    background:var(--surface);
    border:1.5px solid var(--border);
    border-radius:30px;
    padding:4rem 2rem;
    text-align:center;

    font-family:var(--font-display);
    font-size:2rem;
    font-weight:700;
}

/* BUTTON */

.btn-wrap{
    display:flex;
    justify-content:center;
    margin-top:3rem;
}

.btn-retry{
    background:var(--ink);
    color:#fff;
    text-decoration:none;
    padding:1rem 2.4rem;
    border-radius:999px;
    font-weight:600;
    transition:.25s;
}

.btn-retry:hover{
    transform:translateY(-2px);
    opacity:.85;
}

/* RESPONSIVE */

@media(max-width:768px){

    body{
        padding:30px 16px;
    }

    .result-header{
        padding:3rem 1.5rem;
        border-radius:24px;
    }

    .gauge-container{
        width:150px;
        height:150px;
    }

    .gauge-pct{
        font-size:2.4rem;
    }

    .score-card{
        padding:1.5rem;
    }

    .result-msg{
        font-size:2.6rem;
    }
}
    </style>
    
    
</head>
<body>

<div class="container">

    <!-- Bandeau résultat -->
    <div class="result-header">
        <div class="gauge-wrap">
            <div class="gauge-container">
                <svg class="gauge-svg" width="130" height="130" viewBox="0 0 130 130" role="img" aria-label="Jauge de score">
                    <circle class="gauge-bg"  cx="65" cy="65" r="54"/>
                    <circle class="gauge-arc" cx="65" cy="65" r="54" id="gauge-arc"/>
                </svg>
                <div class="gauge-center">
                    <div class="gauge-pct" id="gauge-pct">0%</div>
                    <div class="gauge-label">score</div>
                </div>
            </div>

            <div class="score-cards">
                <div class="score-card">
                    <div class="val"><?php echo $noteSur20; ?>/20</div>
                    <div class="lbl">Note</div>
                </div>
                <div class="score-card">
                    <div class="val"><?php echo $nbBonnesReponses; ?>/<?php echo $totalQuestions; ?></div>
                    <div class="lbl">Bonnes réponses</div>
                </div>
            </div>
        </div>

        <div class="result-msg"><?php echo $emoji; ?> <?php echo $message; ?></div>
    </div>

    <!-- Erreurs ou félicitations -->
    <div class="content">
        <?php if (!empty($erreurs)): ?>
            <div class="error-title">
                <span>❌</span> Détail des erreurs
            </div>
            <?php foreach ($erreurs as $e): ?>
                <div class="error-block">
                    <p><strong>Question :</strong> <?php echo htmlspecialchars($e['question']); ?></p>
                    <p>Votre réponse : <span class="wrong"><?php echo htmlspecialchars($e['reponse_donnee']); ?></span></p>
                    <p>Bonne réponse : <span class="correct"><?php echo htmlspecialchars($e['bonne_reponse']); ?></span></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="perfect-box">🎉 Incroyable ! Toutes les réponses sont correctes !</div>
        <?php endif; ?>

        <div class="btn-wrap">
            <a href="selection_theme.php" class="btn-retry">🔄 Nouvelle tentative</a>
        </div>
    </div>
</div>

<script>
    const finalOffset  = <?php echo round($offset, 2); ?>;
    const finalPct     = <?php echo $pourcentage; ?>;
    const circonference = <?php echo round($circonference, 2); ?>;
    const arc           = document.getElementById('gauge-arc');
    const pctEl         = document.getElementById('gauge-pct');

    window.addEventListener('load', () => {
        setTimeout(() => {
            arc.style.strokeDashoffset = finalOffset;
        }, 200);

        // Compteur animé du pourcentage
        let current = 0;
        const duration = 1400;
        const steps    = 60;
        const interval = duration / steps;
        const increment = finalPct / steps;

        const timer = setInterval(() => {
            current += increment;
            if (current >= finalPct) {
                current = finalPct;
                clearInterval(timer);
            }
            pctEl.textContent = Math.round(current) + '%';
        }, interval);
    });
</script>

</body>
</html>