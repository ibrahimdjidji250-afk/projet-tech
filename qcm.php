<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: connexion.php'); exit(); }

$conn = mysqli_connect('localhost', 'root', 'root', 'qcm1');
if (!$conn) { die('Erreur de connexion : ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8');

// Récupération du thème sélectionné
$themeChoisi = isset($_GET['theme']) ? $_GET['theme'] : 'Tous';

// Si l'utilisateur change de thème ou démarre un nouveau QCM, on réinitialise sa session de questions
if (isset($_GET['theme']) || !isset($_SESSION['qcm_questions'])) {
    
    // Construction de la requête selon le choix
    if ($themeChoisi === 'Tous') {
        $sql_compte = "SELECT COUNT(*) AS total FROM questions";
        $sql_questions = "SELECT * FROM questions ORDER BY RAND() LIMIT 10";
    } else {
        $themeSecure = mysqli_real_escape_string($conn, $themeChoisi);
        $sql_compte = "SELECT COUNT(*) AS total FROM questions WHERE theme = '$themeSecure'";
        $sql_questions = "SELECT * FROM questions WHERE theme = '$themeSecure' ORDER BY RAND() LIMIT 10";
    }

    $resultat_compte = mysqli_query($conn, $sql_compte);
    $ligne_compte = mysqli_fetch_assoc($resultat_compte);
    $totalDispo = $ligne_compte['total'];

    if ($totalDispo == 0) {
        die("<h2 style='font-family:sans-serif; text-align:center; margin-top:50px;'>Aucune question disponible pour le thème ".htmlspecialchars($themeChoisi).". <a href='selection_theme.php'>Retour</a></h2>");
    }

    // Récupération finale des questions
    $resultat_questions = mysqli_query($conn, $sql_questions);
    $tableau_questions = [];
    while ($ligne = mysqli_fetch_assoc($resultat_questions)) {
        $tableau_questions[] = $ligne;
    }
    $_SESSION['qcm_questions'] = $tableau_questions;
    $_SESSION['qcm_theme_encours'] = $themeChoisi;
}

$questions = $_SESSION['qcm_questions'];
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passage du QCM - QCM Dev Web</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg: #ffffff; --surface: #f7f7f5; --ink: #111111; --ink-light: #555550; --border: #e0e0db; --accent: #6c63ff; --danger: #e44d26; }
        body { font-family: 'DM Sans', sans-serif; background-color: var(--surface); color: var(--ink); padding-top: 80px; }
        .navbar { position: fixed; top: 0; left: 0; right: 0; height: 70px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); padding: 0 40px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .navbar .logo { font-family: 'Playfair Display', serif; color: var(--ink); font-size: 1.4rem; font-weight: 900; text-decoration: none; }
        .navbar .logo span { color: var(--accent); }
        .navbar .nav-links a.btn-quit { color: var(--danger); text-decoration: none; font-weight: 600; padding: 8px 16px; border: 1px solid var(--border); border-radius: 6px; }
        .navbar .nav-links a.btn-quit:hover { background-color: var(--danger); color: white; border-color: var(--danger); }
        .container { max-width: 760px; margin: 40px auto; padding: 0 20px; }
        header.qcm-header { margin-bottom: 40px; text-align: center; }
        header h1 { font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 10px; }
        header p { color: var(--ink-light); font-size: 1.1rem; }
        .badge { background: var(--accent); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; vertical-align: middle; }
        .question-block { background: var(--bg); border: 1px solid var(--border); border-radius: 12px; padding: 30px; margin-bottom: 30px; }
        .question-title { font-size: 1.15rem; font-weight: 600; margin-bottom: 20px; }
        .reponse-option { display: flex; align-items: center; gap: 14px; margin: 12px 0; padding: 16px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; cursor: pointer; transition: all 0.2s; }
        .reponse-option:hover { background: var(--surface); border-color: var(--ink); }
        .reponse-option input[type="radio"] { accent-color: var(--accent); width: 18px; height: 18px; }
        .reponse-text { font-size: 1rem; color: var(--ink-light); font-weight: 500; }
        .btn-submit { display: block; width: 100%; background: var(--ink); color: var(--bg); border: none; padding: 18px; border-radius: 8px; cursor: pointer; font-size: 1.1rem; font-weight: 600; margin-top: 20px; }
        .btn-submit:hover { background: var(--accent); }
        .warning-box { display: none; background: var(--danger); color: white; padding: 12px; margin-bottom: 25px; border-radius: 8px; font-weight: 600; text-align: center; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="selection_theme.php" class="logo">QCM<span>.</span>Dev</a>
    <div class="nav-links">
        <a href="selection_theme.php" class="btn-quit" onclick="return confirm('Quitter ce QCM ?');">Quitter le QCM</a>
    </div>
</nav>

<div class="container">
    <header class="qcm-header">
        <h1>Évaluation</h1>
        <p>Thème en cours : <span class="badge"><?php echo htmlspecialchars($_SESSION['qcm_theme_encours']); ?></span></p>
    </header>
    
    <div id="warning" class="warning-box">⚠️ Changement d'onglet ou triche détectée !</div>
    
    <form action="calcule_note.php" method="POST">
        
        <?php foreach ($questions as $index => $q): ?>
            <div class="question-block">
                <div class="question-title"><?php echo ($index + 1); ?>. <?php echo htmlspecialchars($q['question']); ?></div>
                
                <?php for($i = 1; $i <= 4; $i++): ?>
                    <label class="reponse-option">
                        <input type="radio" name="reponse[<?php echo $q['id']; ?>]" value="<?php echo $i; ?>" required>
                        <span class="reponse-text"><?php echo htmlspecialchars($q['reponse' . $i]); ?></span>
                    </label>
                <?php endfor; ?>
            </div>
        <?php endforeach; ?>

        <input type="hidden" id="triche_detectee" name="triche" value="0">

        <button type="submit" class="btn-submit">Valider et soumettre mon questionnaire</button>
    </form>
</div>

<script>
    let avertissements = 0;
    const maxAvertissements = 3;

    // 1. Bloquer le clic droit
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        alert("⚠️ Le clic droit est désactivé pendant l'examen !");
    });

    // 2. Bloquer le copier-coller
    document.addEventListener('copy', function(e) {
        e.preventDefault();
        alert("⚠️ Le copier-coller est interdit.");
    });

    // 3. Gérer le changement d'onglet ou d'application (blur)
    window.addEventListener('blur', function() {
        avertissements++;
        const warningElement = document.getElementById('warning');
        
        if (avertissements >= maxAvertissements) {
            // Activer le drapeau de triche
            document.getElementById('triche_detectee').value = "1";
            alert("❌ EXAMEN ANNULÉ : Sortie de page détectée 3 fois. Votre copie est envoyée avec la note de 00/20.");
            // Soumission forcée du formulaire vers calcul_note.php
            document.querySelector('form').submit();
        } else {
            // Affichage du bandeau rouge CSS et de l'alerte
            warningElement.style.display = 'block';
            warningElement.innerText = "⚠️ Attention (" + avertissements + "/" + maxAvertissements + ") : Ne changez pas d'onglet !";
            alert("⚠️ Attention : Vous avez quitté la page de test ! (" + avertissements + "/" + maxAvertissements + ")");
        }
    });
</script>
</body>
</html>
