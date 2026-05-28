<?php
session_start();

// 1. Connexion propre à la base de données en PDO uniquement
$host = 'localhost';
$dbname = 'qcm1';
$user = 'root';
$pass = 'root'; // Mot de passe MAMP

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

$nbBonnesReponses = 0;
$erreurs = []; 
$totalQuestions = 0;
$tricheInvalide = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. Vérification du drapeau Anti-Triche envoyé par le JavaScript
    if (isset($_POST['triche']) && $_POST['triche'] === "1") {
        $tricheInvalide = true;
        $noteSur20 = 0;
    } 
    // 3. Traitement normal si l'utilisateur n'a pas triché
    // Le calcul s'applique si des réponses sont reçues
    elseif (isset($_POST['reponse'])) {
        $reponsesUtilisateur = $_POST['reponse']; 
        $totalQuestions = count($reponsesUtilisateur);

        foreach ($reponsesUtilisateur as $idQuestion => $indexChoisi) {
            $idQuestionSecure = (int)$idQuestion;
            
            // Requête préparée PDO sécurisée contre les injections SQL
            $stmt = $db->prepare("SELECT * FROM questions WHERE id = ?");
            $stmt->execute([$idQuestionSecure]);
            $q = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($q) {
                $bonneReponseIndex = (int)$q['bonne_reponse'];
                $indexChoisi = (int)$indexChoisi;

                if ($indexChoisi === $bonneReponseIndex) {
                    $nbBonnesReponses++;
                } else {
                    // Récupération dynamique des textes des réponses
                    $cleChoisie = 'reponse' . $indexChoisi;
                    $cleBonne = 'reponse' . $bonneReponseIndex;

                    $erreurs[] = [
                        'question' => $q['question'],
                        'reponse_donnee' => $q[$cleChoisie] ?? 'Pas de réponse',
                        'bonne_reponse' => $q[$cleBonne]
                    ];
                }
            }
        }
        
        // Calcul de la note globale sur 20
        $noteSur20 = ($totalQuestions > 0) ? round(($nbBonnesReponses / $totalQuestions) * 20, 2) : 0;

        // 🔥 MODIFICATION ICI : Sauvegarde propre et sécurisée en PDO (avec $db)
        if (isset($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];
            
            try {
                $stmt_hist = $db->prepare("INSERT INTO historique (user_id, score, date_session) VALUES (?, ?, NOW())");
                $stmt_hist->execute([$user_id, $noteSur20]);
            } catch (Exception $e) {
                // Évite de bloquer la page en cas de problème de table en BDD
            }
        }
    
    }

    // Nettoyer d'éventuelles variables de session temporaires
    unset($_SESSION['qcm_questions']);

} else {
    // Si quelqu'un tente d'accéder au fichier sans valider le formulaire, retour au QCM
    header("Location: qcm.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats du QCM</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; color: #333; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { color: #2c3e50; text-align: center; }
        
        /* Bandeau de score */
        .stats { background: #ecf0f1; padding: 20px; border-radius: 6px; margin-bottom: 25px; }
        .triche-box { background: linear-gradient(135deg, #dc2626, #991b1b); color: white; padding: 25px; border-radius: 8px; text-align: center; margin-bottom: 25px; }
        
        /* Blocs d'erreurs */
        .error-block { background: #fdf2f2; border-left: 5px solid #e74c3c; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .wrong { color: #c0392b; font-weight: bold; }
        .correct { color: #27ae60; font-weight: bold; }
        .btn-retry { display: inline-block; background-color: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 20px; }
        .btn-retry:hover { background-color: #2980b9; }
    </style>
</head>
<body>

<div class="container">
    <h1>Résultats du QCM</h1>
    
    <?php if ($tricheInvalide): ?>
        <div class="triche-box">
            <h2>Examen Invalidé (00 / 20)</h2>
            <p>⚠️ Raison : Vous avez quitté l'onglet d'examen à 3 reprises. La copie a été bloquée automatiquement.</p>
        </div>
        <h3 style="color: #dc2626; text-align: center;">Aucune correction détaillée n'est disponible.</h3>
    <?php else: ?>
        <div class="stats">
            <strong>📊 Bilan de votre session :</strong>
            <ul>
                <li>Votre note : <span style="color:#2980b9; font-weight:bold; font-size:1.3em;"><?php echo $noteSur20; ?> / 20</span></li>
                <li>Réponses valides : <strong><?php echo $nbBonnesReponses; ?> / <?php echo $totalQuestions; ?></strong></li>
            </ul>
        </div>

        <?php if (!empty($erreurs)): ?>
            <h2>Détail des erreurs à corriger :</h2>
            <?php foreach ($erreurs as $e): ?>
                <div class="error-block">
                    <p><strong>Question :</strong> <?php echo htmlspecialchars($e['question']); ?></p>
                    <p>❌ Votre choix : <span class="wrong"><?php echo htmlspecialchars($e['reponse_donnee']); ?></span></p>
                    <p>✔️ Réponse attendue : <span class="correct"><?php echo htmlspecialchars($e['bonne_reponse']); ?></span></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="background-color: #edf7ed; color: #1e4620; padding: 20px; border-radius: 6px; font-weight: bold; text-align: center; font-size: 1.1em;">
                🎉 Parfait ! Un sans-faute ! Félicitations pour votre 20/20 !
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="selection_theme.php" class="btn-retry">🔄 Tenter un nouveau QCM</a>
    </div>
     <div style="text-align: center; margin-top: 10px;">
        <a href="acceuil.php" class="btn-retry">Retour a l'acceuil</a>
    </div>
</div>

</body>
</html>