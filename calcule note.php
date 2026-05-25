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
        // Sécuriser l'ID entré pour la requête
        $idQuestionSecure = (int)$idQuestion;
        
        $sql = "SELECT * FROM questions WHERE id = $idQuestionSecure";
        $resultat = mysqli_query($conn, $sql);
        $q = mysqli_fetch_assoc($resultat);

        if ($q) {
            $bonneReponseIndex = (int)$q['bonne_reponse'];
            $indexChoisi = (int)$indexChoisi;

            if ($indexChoisi === $bonneReponseIndex) {
                $nbBonnesReponses++;
            } else {
                $erreurs[] = [
                    'question' => $q['question'],
                    'reponse_donnee' => $q['reponse' . $indexChoisi],
                    'bonne_reponse' => $q['reponse' . $bonneReponseIndex]
                ];
            }
        }
    }
    
    // Calcul note globale sur 20
    $noteSur20 = ($totalQuestions > 0) ? round(($nbBonnesReponses / $totalQuestions) * 20, 2) : 0;
    
    // Nettoyer la session du lot précédent
    unset($_SESSION['qcm_questions']);
} else {
    header("Location: qcm.php");
    exit();
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; color: #333; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { color: #2c3e50; text-align: center; }
        .stats { background: #ecf0f1; padding: 20px; border-radius: 6px; margin-bottom: 25px; }
        .error-block { background: #fdf2f2; border-left: 5px solid #e74c3c; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .wrong { color: #c0392b; font-weight: bold; }
        .correct { color: #27ae60; font-weight: bold; }
        .btn-retry { display: inline-block; background-color: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Résultats du QCM</h1>
    
    <div class="stats">
        <strong>📊 Bilan :</strong>
        <ul>
            <li>Votre note : <span style="color:#2980b9; font-weight:bold; font-size:1.2em;"><?php echo $noteSur20; ?> / 20</span></li>
            <li>Réponses valides : <strong><?php echo $nbBonnesReponses; ?> / <?php echo $totalQuestions; ?></strong></li>
        </ul>
    </div>

    <?php if (!empty($erreurs)): ?>
        <h2>Détail des erreurs :</h2>
        <?php foreach ($erreurs as $e): ?>
            <div class="error-block">
                <p><strong>Question :</strong> <?php echo htmlspecialchars($e['question']); ?></p>
                <p>❌ Votre choix : <span class="wrong"><?php echo htmlspecialchars($e['reponse_donnee']); ?></span></p>
                <p>✔️ Réponse attendue : <span class="correct"><?php echo htmlspecialchars($e['bonne_reponse']); ?></span></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="background-color: #edf7ed; color: #1e4620; padding: 15px; border-radius: 6px; font-weight: bold; text-align: center;">
            🎉 Parfait ! Félicitations pour ce 20/20 !
        </div>
    <?php endif; ?>
    
    <div style="text-align: center;">
        <a href="qcm.php" class="btn-retry">🔄 Lancer une autre tentative</a>
    </div>
</div>

</body>
</html>