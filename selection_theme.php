<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header('Location: connexion.php'); 
    exit(); 
}

$conn = mysqli_connect('localhost', 'root', 'root', 'qcm1');
if (!$conn) {
    die('Erreur de connexion : ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
 

// Récupère la liste distincte des thèmes existants
$resultat_themes = mysqli_query($conn, "SELECT DISTINCT theme FROM questions ORDER BY theme ASC");

// 🔥 AJOUT SÉCURISÉ : Historique mis dans un bloc TRY/CATCH pour empêcher tout plantage (Erreur 500)
$historique = [];
$user_id = (int)$_SESSION['user_id'];

try {
    // On tente de récupérer l'historique de l'utilisateur
    $result_hist = mysqli_query($conn, "SELECT * FROM historique WHERE user_id = $user_id ORDER BY date_session DESC");
    if ($result_hist) {
        while ($row = mysqli_fetch_assoc($result_hist)) {
            $historique[] = $row;
        }
    }
} catch (Exception $e) {
    // Si la table ou les colonnes n'existent pas encore, on n'affiche pas d'erreur 500, on laisse le tableau vide
    $historique = [];
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choisir vos thèmes - QCM Dev Web</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background-color: #f7f7f5; margin: 0; padding-top: 100px; padding-bottom: 60px; }
        .navbar { position: fixed; top: 0; left: 0; right: 0; height: 70px; background: white; border-bottom: 1px solid #e0e0db; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; z-index: 1000; }
        .navbar .logo { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 900; text-decoration: none; color: #111; }
        .navbar .logo span { color: #6c63ff; }
        .navbar a.logout { color: #e44d26; text-decoration: none; font-weight: 600; }
        
        .container { max-width: 550px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; border: 1px solid #e0e0db; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; margin-bottom: 10px; text-align: center; }
        p { text-align: center; color: #555; margin-bottom: 30px; }
        
        /* Style de la zone de choix multiple */
        .themes-container { display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px; }
        .theme-label { display: flex; align-items: center; gap: 14px; padding: 14px; border: 1px solid #e0e0db; border-radius: 8px; cursor: pointer; font-weight: 500; transition: background 0.2s, border-color 0.2s; }
        .theme-label:hover { background: #f7f7f5; border-color: #111; }
        .theme-label input[type="checkbox"] { accent-color: #6c63ff; width: 18px; height: 18px; cursor: pointer; }
        
        .btn-start { display: block; width: 100%; background: #111; color: white; border: none; padding: 16px; border-radius: 6px; cursor: pointer; font-size: 1.05rem; font-weight: 600; text-align: center; text-decoration: none; box-sizing: border-box; }
        .btn-start:hover { background: #6c63ff; }

        /* Style pour l'historique */
        .history-box { margin-top: 40px; border-top: 1px solid #e0e0db; padding-top: 30px; }
        .history-box h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 15px; color: #111; text-align: center; }
        .history-table { width: 100%; border-collapse: collapse; text-align: left; }
        .history-table th, .history-table td { padding: 12px; border-bottom: 1px solid #e0e0db; font-size: 0.95rem; }
        .history-table th { background: #f7f7f5; color: #555; font-weight: 600; }
        .score-badge { font-weight: bold; }
        .score-good { color: #2ecc71; }
        .score-bad { color: #e74c3c; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="acceuil.php" class="logo">QCM<span>.</span>Dev</a>
    <a href="connexion.php?action=logout" class="logout">Se déconnecter</a>
</nav>

<div class="container">
    <h1>Mode Évaluation</h1>
    <p>Bonjour <?php echo htmlspecialchars($_SESSION['user_prenom']); ?>, sélectionnez **un ou plusieurs** thèmes pour votre test.</p>
    
    <form action="qcm.php" method="GET" id="theme-form">
        <label style="font-weight: 600; display: block; margin-bottom: 15px;">Thèmes à inclure :</label>
        
        <div class="themes-container">
            <label class="theme-label" style="background: #eeeeeb; font-weight: 600;">
                <input type="checkbox" id="select-all">
                <span>Tous les thèmes</span>
            </label>
            <hr style="border: 0; border-top: 1px solid #e0e0db; margin: 5px 0;">

            <?php if ($resultat_themes): ?>
                <?php while($ligne = mysqli_fetch_assoc($resultat_themes)): ?>
                    <label class="theme-label">
                        <input type="checkbox" name="themes[]" value="<?php echo htmlspecialchars($ligne['theme']); ?>" class="theme-checkbox">
                        <span><?php echo htmlspecialchars($ligne['theme']); ?></span>
                    </label>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
        
        <button type="submit" class="btn-start">Commencer l'évaluation →</button>
    </form>

    <div class="history-box">
        <h2>📊 Mon Historique de Scores</h2>
        <?php if (!empty($historique)): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date de passage</th>
                        <th style="text-align: right;">Note obtenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historique as $h): ?>
                    <tr>
                        <td>
                            <?php 
                                // Sécurité au cas où le nom de la colonne date est différent en BDD
                                $date_brute = isset($h['date_session']) ? $h['date_session'] : (isset($h['date']) ? $h['date'] : 'Preuve SQL');
                                echo ($date_brute !== 'Preuve SQL') ? date('d/m/Y à H:i', strtotime($date_brute)) : 'Date inconnue'; 
                            ?>
                        </td>
                        <td style="text-align: right;">
                            <span class="score-badge <?php echo ($h['score'] >= 10) ? 'score-good' : 'score-bad'; ?>">
                                <?php echo number_format($h['score'], 2); ?> / 20
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #777; font-style: italic; margin-top: 10px;">
                Vous n'avez pas encore passé d'évaluation ou votre table historique doit être créée. Vos scores apparaîtront ici.
            </p>
        <?php endif; ?>
    </div>
</div>

<script>
    const selectAllBox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.theme-checkbox');
    const form = document.getElementById('theme-form');

    if (selectAllBox) {
        selectAllBox.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAllBox.checked);
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const checkCount = document.querySelectorAll('.theme-checkbox:checked').length;
            if (checkCount === 0) {
                e.preventDefault();
                alert("Veuillez sélectionner au moins un thème avant de continuer.");
            }
        });
    }
</script>
</body>
</html>
<?php mysqli_close($conn); ?>
