<?php
session_start();
$db = new PDO("mysql:host=localhost;dbname=qcm1;charset=utf8", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$message_success = "";

// ==========================================
// TRAITEMENTS DE LA GESTION DES UTILISATEURS
// ==========================================

// Supprimer un utilisateur (9.1)
if (isset($_GET['action']) && $_GET['action'] === 'suppr_user') {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    $message_success = "Utilisateur supprimé avec succès.";
}

// Bloquer / Débloquer un utilisateur (9.1)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_block') {
    $id = (int)$_GET['id'];
    $status = (int)$_GET['status']; // 1 pour bloquer, 0 pour débloquer
    $stmt = $db->prepare("UPDATE utilisateurs SET est_bloque = ? WHERE id_utilisateur = ?");
    $stmt->execute([$status, $id]);
    $message_success = ($status === 1) ? "Utilisateur bloqué." : "Utilisateur débloqué.";
}

// ==========================================
// TRAITEMENTS DE LA GESTION DES QUESTIONS
// ==========================================

// Ajouter une question (9.2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_question'])) {
    $enonce = trim($_POST['enonce']);
    $reponses = $_POST['reponses']; 
    $bonne_reponse = (int)$_POST['bonne_reponse']; 

    $stmt = $db->prepare("INSERT INTO questions (enonce) VALUES (?)");
    $stmt->execute([$enonce]);
    $id_question = $db->lastInsertId();

    foreach ($reponses as $index => $texte) {
        $est_correcte = ($index === $bonne_reponse) ? 1 : 0;
        $stmtRep = $db->prepare("INSERT INTO reponses (id_question, texte_reponse, est_correcte) VALUES (?, ?, ?)");
        $stmtRep->execute([$id_question, trim($texte), $est_correcte]);
    }
    $message_success = "Question ajoutée avec succès.";
}

// Modifier une question (9.2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_question'])) {
    $id_question = (int)$_POST['id_question'];
    $enonce = trim($_POST['enonce']);
    $reponses = $_POST['reponses']; // Tableau [id_reponse => texte]
    $bonne_reponse_id = (int)$_POST['bonne_reponse_id']; // ID de la réponse correcte cochee

    // Mettre à jour l'énoncé
    $stmt = $db->prepare("UPDATE questions SET enonce = ? WHERE id_question = ?");
    $stmt->execute([$enonce, $id_question]);

    // Mettre à jour les réponses
    foreach ($reponses as $id_reponse => $texte) {
        $est_correcte = ($id_reponse === $bonne_reponse_id) ? 1 : 0;
        $stmtRep = $db->prepare("UPDATE reponses SET texte_reponse = ?, est_correcte = ? WHERE id_reponse = ?");
        $stmtRep->execute([trim($texte), $est_correcte, $id_reponse]);
    }
    $message_success = "Question mise à jour avec succès.";
}

// Supprimer une question (9.2)
if (isset($_GET['action']) && $_GET['action'] === 'suppr_question') {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("DELETE FROM questions WHERE id_question = ?");
    $stmt->execute([$id]);
    $message_success = "Question supprimée avec succès.";
}

// Récupération des données pour l'affichage
$utilisateurs = $db->query("SELECT * FROM utilisateurs ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$questions = $db->query("SELECT * FROM questions ORDER BY id_question DESC")->fetchAll(PDO::FETCH_ASSOC);

// Mode édition d'une question
$question_en_cours_edition = null;
$reponses_en_cours_edition = [];
if (isset($_GET['action']) && $_GET['action'] === 'edit_question') {
    $id_edit = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM questions WHERE id_question = ?");
    $stmt->execute([$id_edit]);
    $question_en_cours_edition = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($question_en_cours_edition) {
        $stmtRep = $db->prepare("SELECT * FROM reponses WHERE id_question = ?");
        $stmtRep->execute([$id_edit]);
        $reponses_en_cours_edition = $stmtRep->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>9. Interface Administrateur</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .box { background: white; padding: 25px; margin-bottom: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #2980b9; margin-top: 0; border-bottom: 2px solid #ecf0f1; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f8f9fa; }
        input[type="text"], textarea { width: 100%; padding: 10px; margin: 5px 0 15px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #3498db; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        button:hover { background: #2980b9; }
        .btn-action { padding: 5px 10px; color: white; text-decoration: none; border-radius: 4px; font-size: 0.85em; font-weight: bold; margin-right: 5px; }
        .btn-danger { background: #e74c3c; } .btn-danger:hover { background: #c0392b; }
        .btn-warning { background: #f39c12; } .btn-warning:hover { background: #d35400; }
        .btn-success { background: #2ecc71; } .btn-success:hover { background: #27ae60; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .radio-group { display: flex; align-items: center; margin-bottom: 8px; }
        .radio-group input[type="radio"] { margin-right: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h1>9. Interface Administrateur</h1>
    <p style="text-align: right;"><a href="connexion.php?action=logout" style="color:red; font-weight:bold;">Se déconnecter</a></p>

    <?php if (!empty($message_success)): ?>
        <div class="alert-success"><?php echo $message_success; ?></div>
    <?php endif; ?>

    <div class="box">
        <h2>9.1 Gestion des utilisateurs</h2>
        <table>
            <thead>
                <tr>
                    <th>Nom / Prénom</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <?php echo ($user['est_bloque'] == 1) ? "<span style='color:red;font-weight:bold;'>Bloqué</span>" : "<span style='color:green;'>Actif</span>"; ?>
                    </td>
                    <td>
                        <?php if ($user['est_bloque'] == 1): ?>
                            <a class="btn-action btn-success" href="admin.php?action=toggle_block&id=<?php echo $user['id_utilisateur']; ?>&status=0">Débloquer</a>
                        <?php else: ?>
                            <a class="btn-action btn-warning" href="admin.php?action=toggle_block&id=<?php echo $user['id_utilisateur']; ?>&status=1" onclick="return confirm('Bloquer cet utilisateur ? Il ne pourra plus se connecter.');">Bloquer</a>
                        <?php endif; ?>

                        <a class="btn-action btn-danger" href="admin.php?action=suppr_user&id=<?php echo $user['id_utilisateur']; ?> onclick="return confirm('Supprimer définitivement cet utilisateur ?');">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="box">
        <h2>9.2 Gestion des questions</h2>

        <?php if ($question_en_cours_edition): ?>
            <h3 style="color: #f39c12;">Modifier la question ID #<?php echo $question_en_cours_edition['id_question']; ?></h3>
            <form action="admin.php" method="POST">
                <input type="hidden" name="id_question" value="<?php echo $question_en_cours_edition['id_question']; ?>">
                
                <label>Texte de la question :</label>
                <textarea name="enonce" required><?php echo htmlspecialchars($question_en_cours_edition['enonce']); ?></textarea>

                <label>Modifiez les 4 réponses (Sélectionnez la bonne) :</label>
                <?php foreach ($reponses_en_cours_edition as $r): ?>
                    <div class="radio-group">
                        <input type="radio" name="bonne_reponse_id" value="<?php echo $r['id_reponse']; ?>" <?php echo ($r['est_correcte'] == 1) ? 'checked' : ''; ?>>
                        <input type="text" name="reponses[<?php echo $r['id_reponse']; ?>]" value="<?php echo htmlspecialchars($r['texte_reponse']); ?>" required>
                    </div>
                <?php endforeach; ?>

                <button type="submit" name="modifier_question" style="background:#f39c12;">Enregistrer les modifications</button>
                <a href="admin.php" style="margin-left:10px; color:#555;">Annuler</a>
            </form>
        <?php else: ?>
            <h3>Ajouter une nouvelle question</h3>
            <form action="admin.php" method="POST">
                <label>Texte de la question :</label>
                <textarea name="enonce" placeholder="Ex: Quel protocole chiffre le web ?" required></textarea>

                <label>Les 4 réponses possibles (Cochez la case ronde pour la réponse correcte) :</label>
                <?php for($i=0; $i<4; $i++): ?>
                    <div class="radio-group">
                        <input type="radio" name="bonne_reponse" value="<?php echo $i; ?>" <?php echo ($i === 0) ? 'checked' : ''; ?>>
                        <input type="text" name="reponses[]" placeholder="Option de réponse <?php echo $i+1; ?>" required>
                    </div>
                <?php endfor; ?>

                <button type="submit" name="ajouter_question">Ajouter la question</button>
            </form>
        <?php endif; ?>

        <h3 style="margin-top:40px; border-top: 1px solid #ddd; padding-top:20px;">Questions enregistrées</h3>
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $q): ?>
                <tr>
                    <td><?php echo htmlspecialchars($q['enonce']); ?></td>
                    <td>
                        <a class="btn-action btn-warning" href="admin.php?action=edit_question&id=<?php echo $q['id_question']; ?>">Modifier</a>
                        <a class="btn-action btn-danger" href="admin.php?action=suppr_question&id=<?php echo $q['id_question']; ?>" onclick="return confirm('Voulez-vous supprimer cette question ainsi que ses réponses ?');">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>