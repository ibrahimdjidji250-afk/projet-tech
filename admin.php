<?php
session_start();

// 1. SÉCURITÉ & LISTE BLANCHE DES ADMINS (Toi et tes camarades)
// Ajoute ici ton email et les emails de tes camarades de groupe
$emails_admins = [
    'Alioudiarrapro@gmail.com',
    'Ibrahimdjidji250@gmail.com',
    'Fommarc5@gmail.com',
    'djamaldinefathidouga@gmail.com'
];

// Vérification : L'utilisateur est-il connecté ?
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    header('Location: connexion.php');
    exit();
}

// Vérification du rôle : si son email est dans la liste, on le force ADMIN en session
if (in_array($_SESSION['user_email'], $emails_admins)) {
    $_SESSION['user_role'] = 'admin';
} else {
    $_SESSION['user_role'] = 'user'; // Optionnel : force le rôle user si l'email n'y est pas
}

// Bloquer l'accès si l'utilisateur final n'est pas admin
if ($_SESSION['user_role'] !== 'admin') {
    die("<h1 style='font-family:sans-serif; text-align:center; margin-top:50px; color:#e44d26;'>Accès refusé. Vous devez être administrateur pour voir cette page. <a href='acceuil.php'>Retour</a></h1>");
}


// 2. CONNEXION À LA BASE DE DONNÉES
$conn = mysqli_connect('localhost', 'root', 'root', 'qcm1');
if (!$conn) { 
    die('Erreur de connexion : ' . mysqli_connect_error()); 
}
mysqli_set_charset($conn, 'utf8mb4');

$message_success = "";

// 3. TRAITEMENTS ACTIONS

// Supprimer un utilisateur
if (isset($_GET['action']) && $_GET['action'] === 'suppr_user') {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM utilisateurs WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $message_success = "Utilisateur supprimé avec succès.";
    }
}

// Ajouter une question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_question'])) {
    $question = mysqli_real_escape_string($conn, trim($_POST['question']));
    $theme = mysqli_real_escape_string($conn, trim($_POST['theme']));
    $r1 = mysqli_real_escape_string($conn, trim($_POST['reponse1']));
    $r2 = mysqli_real_escape_string($conn, trim($_POST['reponse2']));
    $r3 = mysqli_real_escape_string($conn, trim($_POST['reponse3']));
    $r4 = mysqli_real_escape_string($conn, trim($_POST['reponse4']));
    $bonne_reponse = (int)$_POST['bonne_reponse']; 

    $sql = "INSERT INTO questions (question, theme, reponse1, reponse2, reponse3, reponse4, bonne_reponse) 
            VALUES ('$question', '$theme', '$r1', '$r2', '$r3', '$r4', $bonne_reponse)";
            
    if (mysqli_query($conn, $sql)) {
        $message_success = "Question ajoutée avec succès dans le thème \"" . htmlspecialchars($_POST['theme']) . "\".";
    }
}

// Modifier le thème en ligne
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_theme_question'])) {
    $id_question = (int)$_POST['id_question'];
    $nouveau_theme = mysqli_real_escape_string($conn, trim($_POST['nouveau_theme']));

    $sql = "UPDATE questions SET theme = '$nouveau_theme' WHERE id = $id_question";
    if (mysqli_query($conn, $sql)) {
        $message_success = "Le thème de la question a été modifié avec succès.";
    }
}

// Supprimer une question
if (isset($_GET['action']) && $_GET['action'] === 'suppr_question') {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM questions WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $message_success = "Question supprimée avec succès.";
    }
}

// 4. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE
$resultat_users = mysqli_query($conn, "SELECT * FROM utilisateurs ORDER BY nom ASC");
$utilisateurs = [];
while ($row = mysqli_fetch_assoc($resultat_users)) {
    $utilisateurs[] = $row;
}

$resultat_questions = mysqli_query($conn, "SELECT * FROM questions ORDER BY id DESC");
$questions = [];
while ($row = mysqli_fetch_assoc($resultat_questions)) {
    $questions[] = $row;
}

$resultat_themes = mysqli_query($conn, "SELECT DISTINCT theme FROM questions WHERE theme != '' AND theme IS NOT NULL ORDER BY theme ASC");
$themes_existants = [];
while ($row = mysqli_fetch_assoc($resultat_themes)) {
    $themes_existants[] = $row['theme'];
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - QCM</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; color: #333; }
        .admin-container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 40px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle; }
        th { background-color: #f2f2f2; }
        .btn-action { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 0.9em; display: inline-block; }
        .btn-danger { background-color: #e74c3c; border: none; cursor: pointer; text-decoration: none; }
        .btn-danger:hover { background-color: #c0392b; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button.btn-add { background: #2ecc71; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1em; }
        button.btn-add:hover { background: #27ae60; }
        .inline-theme-form { display: flex; gap: 6px; align-items: center; margin: 0; padding: 0; }
        .inline-theme-form input[type="text"] { padding: 6px; font-size: 0.9em; width: 140px; }
        .btn-save-theme { background: #34495e; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 0.85em; white-space: nowrap; }
        .btn-save-theme:hover { background: #2c3e50; }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>Espace Administration (MySQLi)</h1>
    <p>Connecté en tant que : <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong> (Rôle forcé : <?php echo $_SESSION['user_role']; ?>)</p>
    
    <?php if (!empty($message_success)): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius:4px; font-weight: bold;">
            <?php echo $message_success; ?>
        </div>
    <?php endif; ?>

    <h2>Gestion des utilisateurs</h2>
    <table>
        <thead>
            <tr>
                <th>Nom / Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['role']); ?></td>
                <td>
                    <a class="btn-action btn-danger" href="admin.php?action=suppr_user&id=<?php echo $u['id']; ?>" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Ajouter une question et créer/assigner un thème</h2>
    <form action="admin.php" method="POST">
        <div class="form-group">
            <label>Énoncé de la question :</label>
            <textarea name="question" required placeholder="Exemple : Quelle fonction trie un tableau en PHP ?"></textarea>
        </div>

        <div class="form-group">
            <label>Thème de la question :</label>
            <input type="text" name="theme" list="themes_list" required placeholder="Exemple : PHP, HTML, CSS, SQL..." autocomplete="off">
            <datalist id="themes_list">
                <?php foreach ($themes_existants as $t): ?>
                    <option value="<?php echo htmlspecialchars($t); ?>">
                <?php endforeach; ?>
            </datalist>
        </div>

        <div class="form-group">
            <label>Option de réponse 1 :</label>
            <input type="text" name="reponse1" required>
        </div>
        <div class="form-group">
            <label>Option de réponse 2 :</label>
            <input type="text" name="reponse2" required>
        </div>
        <div class="form-group">
            <label>Option de réponse 3 :</label>
            <input type="text" name="reponse3" required>
        </div>
        <div class="form-group">
            <label>Option de réponse 4 :</label>
            <input type="text" name="reponse4" required>
        </div>
        <div class="form-group">
            <label>Numéro de la bonne réponse :</label>
            <select name="bonne_reponse">
                <option value="1">Réponse 1</option>
                <option value="2">Réponse 2</option>
                <option value="3">Réponse 3</option>
                <option value="4">Réponse 4</option>
            </select>
        </div>
        <button type="submit" name="ajouter_question" class="btn-add">Ajouter la question</button>
    </form>

    <h2 style="margin-top: 40px;">Questions enregistrées & Modification des thèmes</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Thème (Modifiable en ligne)</th>
                <th style="width: 55%;">Question</th>
                <th style="width: 15%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $q): ?>
            <tr>
                <td>
                    <form action="admin.php" method="POST" class="inline-theme-form">
                        <input type="hidden" name="id_question" value="<?php echo $q['id']; ?>">
                        <input type="text" name="nouveau_theme" list="themes_list" value="<?php echo htmlspecialchars($q['theme']); ?>" required autocomplete="off">
                        <button type="submit" name="modifier_theme_question" class="btn-save-theme">Mettre à jour</button>
                    </form>
                </td>
                <td><?php echo htmlspecialchars($q['question']); ?></td>
                <td>
                    <a class="btn-action btn-danger" href="admin.php?action=suppr_question&id=<?php echo $q['id']; ?>" onclick="return confirm('Supprimer cette question ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>