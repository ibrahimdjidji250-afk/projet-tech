<?php
$db = new PDO("mysql:host=localhost;dbname=qcm1;charset=utf8", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// 1. Traitement de l'ajout d'une question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_question'])) {
    $enonce = $_POST['enonce'];
    $reponses = $_POST['reponses']; // Tableau de 4 réponses
    $bonne_reponse = $_POST['bonne_reponse']; // Index de la bonne réponse (0, 1, 2 ou 3)

    // Insérer la question
    $stmt = $db->prepare("INSERT INTO questions (enonce) VALUES (?)");
    $stmt->execute([$enonce]);
    $id_question = $db->lastInsertId();

    // Insérer les 4 réponses
    foreach ($reponses as $index => $texte) {
        $est_correcte = ($index == $bonne_reponse) ? 1 : 0;
        $stmtRep = $db->prepare("INSERT INTO reponses (id_question, texte_reponse, est_correcte) VALUES (?, ?, ?)");
        $stmtRep->execute([$id_question, $texte, $est_correcte]);
    }
    header('Location: admin.php');
    exit();
}

// 2. Traitement de la suppression
if (isset($_GET['supprimer'])) {
    $id_sub = (int)$_GET['supprimer'];
    $stmt = $db->prepare("DELETE FROM questions WHERE id_question = ?");
    $stmt->execute([$id_sub]);
    header('Location: admin.php');
    exit();
}

// Récupérer toutes les questions pour les afficher
$questions = $db->query("SELECT * FROM questions ORDER BY id_question DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration du QCM</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .box { background: white; padding: 20px; max-width: 700px; margin: 0 auto 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-top: 0; }
        input[type="text"], textarea { width: 100%; padding: 8px; margin: 5px 0 15px; box-sizing: border-box; }
        .radio-group { margin-bottom: 10px; }
        button { background: #3498db; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        button:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; padding: 5px 10px; color: white; text-decoration: none; border-radius: 3px; font-size: 0.9em; }
        .btn-danger:hover { background: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

<div class="box">
    <h2>Ajouter une nouvelle question</h2>
    <form action="admin.php" method="POST">
        <label>Énoncé de la question :</label>
        <textarea name="enonce" required placeholder="Ex: Quelle est la capitale de la France ?"></textarea>

        <label>Options de réponse (Cochez la bonne réponse) :</label>
        <?php for($i=0; $i<4; $i++): ?>
            <div class="radio-group">
                <input type="radio" name="bonne_reponse" value="<?php echo $i; ?>" <?php echo $i===0?'checked':''; ?>>
                <input type="text" name="reponses[]" required placeholder="Réponse <?php echo $i+1; ?>">
            </div>
        <?php endfor; ?>

        <button type="submit" name="ajouter_question">Enregistrer la question</button>
    </form>
</div>

<div class="box">
    <h2>Questions existantes (<?php echo count($questions); ?>)</h2>
    <p><a href="qcm.php" target="_blank" style="color: #2ecc71; font-weight: bold;">➡️ Ouvrir le QCM étudiant</a></p>
    <table>
        <tr>
            <th>ID</th>
            <th>Question</th>
            <th>Action</th>
        </tr>
        <?php foreach ($questions as $q): ?>
        <tr>
            <td><?php echo $db->query("SELECT COUNT(*) FROM questions WHERE id_question <= ".$q['id_question'])->fetchColumn(); ?></td>
            <td><?php echo htmlspecialchars($q['enonce']); ?></td>
            <td><a class="btn-danger" href="admin.php?supprimer=<?php echo $q['id_question']; ?>" onclick="return confirm('Supprimer cette question ?');">Retirer</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>