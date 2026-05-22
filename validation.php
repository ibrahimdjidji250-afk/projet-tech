<?php
// Configuration de la connexion MAMP
$host = 'localhost';
$dbname = 'qcm1';
$user = 'root';
$pass = 'root'; // Mot de passe MAMP requis

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'])) {
    $reponsesUtilisateur = $_POST['reponse']; 
    $score = 0;
    $totalQuestions = 10; 
    $questionsCorrigees = [];

    foreach ($reponsesUtilisateur as $idQuestion => $numeroReponseChoisie) {
        // Sélection de la question correspondante
        $stmt = $db->prepare("SELECT question, reponse1, reponse2, reponse3, reponse4, bonne_reponse FROM questions WHERE id = ?");
        $stmt->execute([$idQuestion]);
        $questionData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($questionData) {
            $estCorrect = ((int)$numeroReponseChoisie === (int)$questionData['bonne_reponse']);
            
            if ($estCorrect) {
                $score++;
            }

            $cleChoisie = 'reponse' . $numeroReponseChoisie;
            $cleBonne = 'reponse' . $questionData['bonne_reponse'];

            $questionsCorrigees[] = [
                'enonce' => $questionData['question'],
                'correct' => $estCorrect,
                'texte_choisi' => $questionData[$cleChoisie] ?? 'Pas de réponse',
                'texte_bon' => $questionData[$cleBonne]
            ];
        }
    }

    $noteSur20 = ($score / $totalQuestions) * 20;

} else {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats du QCM</title>
    <style>
        /* Styles généraux */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 40px 20px; 
            background-color: #f4f7f9; 
            color: #333; 
        }

        /* Conteneur principal corrigé (800px au lieu de 80px) */
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
        }

        /* Boîtier de score en haut */
        .result-box { 
            background: linear-gradient(135deg, #007BFF, #0056b3); 
            color: white; 
            padding: 30px; 
            border-radius: 10px; 
            margin-bottom: 35px; 
            text-align: center; 
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
        }

        .result-box h1 { margin: 0 0 10px 0; font-size: 26px; }
        .result-box h2 { margin: 0 0 5px 0; font-size: 32px; font-weight: 700; }
        .result-box p { margin: 0; font-size: 16px; opacity: 0.9; }

        h3 { 
            font-size: 20px; 
            color: #1e293b; 
            border-bottom: 2px solid #e2e8f0; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }

        /* Cartes de correction des questions */
        .question-card { 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            font-size: 15px;
        }

        .question-card p { margin: 8px 0; }

        /* Style si la réponse est VRAIE */
        .vrai { 
            background-color: #f0fdf4; 
            border-left: 5px solid #16a34a; 
            color: #14532d; 
        }
        .vrai strong.status { color: #16a34a; }

        /* Style si la réponse est FAUSSE */
        .faux { 
            background-color: #fef2f2; 
            border-left: 5px solid #dc2626; 
            color: #7f1d1d; 
        }
        .faux strong.status { color: #dc2626; }

        /* Bouton de retour */
        .actions {
            text-align: center;
            margin-top: 35px;
        }

        .btn-retour { 
            display: inline-block; 
            background-color: #64748b; 
            color: white; 
            text-decoration: none; 
            padding: 12px 25px; 
            font-size: 16px; 
            border-radius: 6px; 
            font-weight: bold; 
            transition: background 0.2s; 
        }

        .btn-retour:hover { 
            background-color: #475569; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="result-box">
            <h1>Résultat de votre examen</h1>
            <h2>Note : <?php echo $noteSur20; ?> / 20</h2>
            <p>Vous avez validé <strong><?php echo $score; ?></strong> bonne(s) réponse(s) sur un total de <strong><?php echo $totalQuestions; ?></strong> questions.</p>
        </div>

        <h3>Correction détaillée :</h3>
        
        <?php foreach ($questionsCorrigees as $index => $qc): ?>
            <div class="question-card <?php echo $qc['correct'] ? 'vrai' : 'faux'; ?>">
                <p><strong>Question <?php echo $index + 1; ?> :</strong> <?php echo htmlspecialchars($qc['enonce']); ?></p>
                <p><strong>Votre réponse :</strong> <span><?php echo htmlspecialchars($qc['texte_choisi']); ?></span></p>
                
                <?php if (!$qc['correct']): ?>
                    <p>❌ <strong class="status">Incorrect.</strong> La bonne réponse attendue était : <strong><?php echo htmlspecialchars($qc['texte_bon']); ?></strong></p>
                <?php else: ?>
                    <p>✅ <strong class="status">Excellent !</strong> Votre réponse est tout à fait correcte.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="actions">
            <a href="index.php" class="btn-retour">🔄 Recommencer un nouvel examen</a>
        </div>
    </div>
</body>
</html>