<?php
session_start();

// Connexion à votre base de données qcm1
$host = 'localhost';
$dbname = 'qcm1';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'])) {
    $reponsesUtilisateur = $_POST['reponse']; // [id_question => id_reponse_choisie]
    $nbBonnesReponses = 0;
    $erreurs = []; // Tableau pour stocker les questions ratées

    foreach ($reponsesUtilisateur as $idQuestion => $idReponseChoisie) {
        // 1. Récupérer l'énoncé de la question
        $stmtQ = $db->prepare("SELECT enonce FROM questions WHERE id_question = ?");
        $stmtQ->execute([$idQuestion]);
        $questionInfo = $stmtQ->fetch(PDO::FETCH_ASSOC);

        // 2. Récupérer la réponse choisie par l'utilisateur
        $stmtUser = $db->prepare("SELECT texte_reponse, est_correcte FROM reponses WHERE id_reponse = ?");
        $stmtUser->execute([idReponseChoisie]);
        $reponseUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

        // 3. Récupérer la bonne réponse attendue pour cette question
        $stmtCorrect = $db->prepare("SELECT texte_reponse FROM reponses WHERE id_question = ? AND est_correcte = 1");
        $stmtCorrect->execute([$idQuestion]);
        $reponseCorrecte = $stmtCorrect->fetch(PDO::FETCH_ASSOC);

        // 4. Vérification du score et stockage des erreurs si faux
        if ($reponseUser && $reponseUser['est_correcte'] == 1) {
            $nbBonnesReponses++;
        } else {
            // L'utilisateur s'est trompé, on enregistre les détails pour l'affichage du bas
            $erreurs[] = [
                'question' => $questionInfo['enonce'],
                'reponse_donnee' => $reponseUser ? $reponseUser['texte_reponse'] : 'Aucune réponse',
                'bonne_reponse' => $reponseCorrecte ? $reponseCorrecte['texte_reponse'] : 'Non définie'
            ];
        }
    }

    // Calcul de la note sur 20 (Vu qu'il y a 10 questions, chaque bonne réponse vaut 2 points)
    $noteSur20 = $nbBonnesReponses * 2;

    // Vider la session anti-triche pour le prochain test
    unset($_SESSION['qcm_questions']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>6. Résultats du QCM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            padding: 30px;
        }
        .container {
            max-width: 750px;
            margin: 0 auto;
            background: #white;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        h2 { color: #c0392b; margin-top: 30px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .stats {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 6px;
            font-size: 1.1em;
            margin-bottom: 20px;
        }
        .stats ul { list-style-type: square; margin: 5px 0 0 20px; padding: 0; }
        .error-block {
            background-color: #fdf2f2;
            border-left: 5px solid #e74c3c;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .error-block p { margin: 5px 0; }
        .wrong { color: #c0392b; font-weight: bold; }
        .correct { color: #27ae60; font-weight: bold; }
        .btn-retry {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn-retry:hover { background-color: #2980b9; }
    </style>
</head>
<body>

<div class="container">
    <h1>6. Résultats</h1>
    
    <div class="stats">
        <strong>Après validation du QCM :</strong>
        <ul>
            <li>Votre note : <strong><?php echo $noteSur20; ?> / 20</strong></li>
            <li>Nombre de bonnes réponses : <strong><?php echo $nbBonnesReponses; ?></strong></li>
        </ul>
    </div>

    <?php if (!empty($erreurs)): ?>
        <h2>Questions où vous vous êtes trompé :</h2>
        
        <?php foreach ($erreurs as $e): ?>
            <div class="error-block">
                <p><strong>Question :</strong> <?php echo htmlspecialchars($e['question']); ?></p>
                <p>Votre réponse : <span class="wrong"><?php echo htmlspecialchars($e['reponse_donnee']); ?></span></p>
                <p>Bonne réponse : <span class="correct"><?php echo htmlspecialchars($e['bonne_reponse']); ?></span></p>
            </div>
        <?php endforeach; ?>
        
    <?php else: ?>
        <div style="background-color: #edf7ed; color: #1e4620; padding: 15px; border-radius: 6px; font-weight: bold;">
            🎉 Incroyable ! Aucune erreur, vous avez trouvé toutes les bonnes réponses !
        </div>
    <?php endif; ?>

    <a href="qcm.php" class="btn-retry">Retourner au QCM</a>
</div>

</body>
</html>
<?php
} else {
    header('Location: qcm.php');
    exit();
}
?>