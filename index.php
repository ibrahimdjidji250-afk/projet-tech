<?php
// Configuration de la connexion
$host = 'localhost';
$dbname = 'qcm1';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// 1. Sélectionner 10 questions aléatoires
$queryQuestions = $db->query("SELECT * FROM questions ORDER BY RAND() LIMIT 10");
$questions = $queryQuestions->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Passage du QCM</title>
    <style>
        .question-block { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        /* Anti-triche basique : empêche la sélection de texte */
        body { user-select: none; }
    </style>
</head>
<body>
    <h1>Examen : QCM</h1>
    <form action="validation.php" method="POST">
        <?php foreach ($questions as $index => $q): ?>
            <div class="question-block">
                <p><strong>Question <?php echo $index + 1; ?> :</strong> <?php echo htmlspecialchars($q['enonce']); ?></p>
                
                <?php
                // Récupérer les 4 réponses pour cette question (mélangées aussi pour plus de sécurité)
                $stmt = $db->prepare("SELECT * FROM reponses WHERE id_question = ? ORDER BY RAND()");
                $stmt->execute([$q['id_question']]);
                $reponses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php foreach ($reponses as $r): ?>
                    <label>
                        <input type="radio" name="reponse[<?php echo $q['id_question']; ?>]" value="<?php echo $r['id_reponse']; ?>" required>
                        <?php echo htmlspecialchars($r['texte_reponse']); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit">Valider mes réponses</button>
    </form>

    <script>
        // Anti-triche : Détection de changement d'onglet
        window.onblur = function() {
            console.log("L'utilisateur a quitté la page");
            // Vous pourriez ici envoyer une requête AJAX pour alerter l'admin ou pénaliser le score
        };
    </script>
</body>
</html>