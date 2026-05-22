<?php
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'qcm1'; // <-- Remplacez par le nom de votre base de données
$user = 'root';        // <-- Votre identifiant
$pass = '';            // <-- Votre mot de passe

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Anti-triche F5 : Si les questions ne sont pas encore en session, on les génère
if (!isset($_SESSION['qcm_questions'])) {
    // Sélectionner 10 questions aléatoires
    $query = $db->query("SELECT * FROM questions ORDER BY RAND() LIMIT 10");
    $_SESSION['qcm_questions'] = $query->fetchAll(PDO::FETCH_ASSOC);
}

$questions = $_SESSION['qcm_questions'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passage de l'examen QCM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            /* Anti-triche : empêche la sélection de texte */
            user-select: none;
            -webkit-user-select: none;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .question-block {
            margin-bottom: 25px;
            padding: 15px;
            background: #fafafa;
            border-left: 5px solid #3498db;
            border-radius: 4px;
        }
        .question-title { font-weight: bold; font-size: 1.1em; margin-bottom: 10px; }
        .reponse-option { margin: 8px 0; display: block; cursor: pointer; }
        .reponse-option input { margin-right: 10px; }
        button {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 1.1em;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover { background-color: #27ae60; }
        .warning-box {
            display: none;
            background-color: #e74c3c;
            color: white;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>4. Passage du QCM : Informatique</h1>
    <div id="warning" class="warning-box">Attention ! Changement d'onglet détecté. Restez sur le test.</div>
    
    <form action="validation.php" method="POST">
        <?php foreach ($questions as $index => $q): ?>
            <div class="question-block">
                <div class="question-title">
                    Question <?php echo $index + 1; ?> : <?php echo htmlspecialchars($q['enonce']); ?>
                </div>
                
                <?php
                // Récupérer les 4 réponses de la question (mélangées aléatoirement pour éviter la triche de position)
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
    // Anti-triche : Bloquer le clic droit (évite d'inspecter l'élément ou copier)
    document.addEventListener('contextmenu', event => event.preventDefault());

    // Anti-triche : Bloquer les raccourcis copier / coller / recherche
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && (e.key === 'c' || e.key === 'v' || e.key === 'u' || e.key === 'f')) {
            e.preventDefault();
            alert("Raccourci désactivé pour cet examen.");
        }
    });

    // Anti-triche : Alerte visuelle si l'étudiant change d'onglet ou de fenêtre
    let tricheCount = 0;
    window.addEventListener('blur', function() {
        tricheCount++;
        document.getElementById('warning').style.display = 'block';
        document.getElementById('warning').innerText = "Avertissement (" + tricheCount + ") : Vous avez quitté la page du QCM !";
    });
</script>

</body>
</html>