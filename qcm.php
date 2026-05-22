<?php
session_start();
$db = new PDO("mysql:host=localhost;dbname=qcm1;charset=utf8", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Compter combien de questions existent en BDD
$totalDispo = $db->query("SELECT COUNT(*) FROM questions")->fetchColumn();

if ($totalDispo == 0) {
    die("<h1>Aucune question disponible. Allez sur <a href='admin.php'>l'espace admin</a> pour en ajouter.</h1>");
}

// Déterminer la limite (10 ou moins si la BDD contient moins de 10 questions)
$limite = ($totalDispo < 10) ? $totalDispo : 10;

if (!isset($_SESSION['qcm_questions'])) {
    // Sélectionner des questions aléatoires selon ce qui est disponible
    $query = $db->query("SELECT * FROM questions ORDER BY RAND() LIMIT $limite");
    $_SESSION['qcm_questions'] = $query->fetchAll(PDO::FETCH_ASSOC);
}

$questions = $_SESSION['qcm_questions'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Passage du QCM Dynamique</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; color: #333; padding: 20px; user-select: none; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .question-block { margin-bottom: 25px; padding: 15px; background: #fafafa; border-left: 5px solid #3498db; border-radius: 4px; }
        .question-title { font-weight: bold; margin-bottom: 10px; }
        .reponse-option { margin: 8px 0; display: block; cursor: pointer; }
        button { background-color: #2ecc71; color: white; border: none; padding: 12px 25px; font-size: 1.1em; border-radius: 4px; cursor: pointer; }
        .warning-box { display: none; background-color: #e74c3c; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Passage du QCM</h1>
    <a href="connexion.php?action=logout" style="color: red; font-weight: bold;">Se déconnecter</a>
    <div id="warning" class="warning-box">Attention ! Changement d'onglet détecté.</div>
    
    <form action="validation.php" method="POST">
        <?php foreach ($questions as $index => $q): ?>
            <div class="question-block">
                <div class="question-title">Question <?php echo $index + 1; ?> : <?php echo htmlspecialchars($q['enonce']); ?></div>
                
                <?php
                $stmt = $db->prepare("SELECT * FROM reponses WHERE id_question = ? ORDER BY RAND()");
                $stmt->execute([$q['id_question']]);
                $reponses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php foreach ($reponses as $r): ?>
                    <label class="reponse-option">
                        <input type="radio" name="reponse[<?php echo $q['id_question']; ?>]" value="<?php echo $r['id_reponse']; ?>" required>
                        <?php echo htmlspecialchars($r['texte_reponse']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit">Valider mes réponses</button>
    </form>
</div>

<script>
    document.addEventListener('contextmenu', e => e.preventDefault());
    window.addEventListener('blur', function() {
        document.getElementById('warning').style.display = 'block';
    });
</script>
</body>
</html>