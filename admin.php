<?php
session_start();

// 1. SÉCURITÉ & LISTE BLANCHE DES ADMINS
$emails_admins = [
    'alioudiarrapro@gmail.com',
    'ibrahimdjidji250@gmail.com',
    'fommarc5@gmail.com',
    'djamaldinefathidouga@gmail.com'
];  

// Vérification : L'utilisateur est-il connecté ?
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    // On mémorise la page demandée pour y retourner après la connexion
    $_SESSION['redirect_to'] = 'admin.php'; 
    header('Location: connexion.php');
    exit();
}
$email_session_verif = strtolower(trim($_SESSION['user_email']));

if (in_array($email_session_verif, $emails_admins)) {
    $_SESSION['user_role'] = 'admin';
} else {
    $_SESSION['user_role'] = 'user';
}

if ($_SESSION['user_role'] !== 'admin') {
    die("<h1 style='font-family:sans-serif; text-align:center; margin-top:50px; color:#e44d26;'>Accès refusé. Vous devez être administrateur pour voir cette page. <a href='selection_theme.php'>Retour</a></h1>");
}

// 2. CONNEXION À LA BASE DE DONNÉES
$conn = mysqli_connect('localhost', 'root', 'root', 'qcm1');
if (!$conn) { 
    die('Erreur de connexion : ' . mysqli_connect_error()); 
}
mysqli_set_charset($conn, 'utf8mb4');

if (isset($_SESSION['msg_success'])) {
    $message_success = $_SESSION['msg_success'];
    unset($_SESSION['msg_success']);
} else {
    $message_success = "";
}

// 3. TRAITEMENTS ACTIONS

// ACTION AJOUTÉE : Modifier le rôle d'un utilisateur (Exigence du PDF)
if (isset($_GET['action']) && $_GET['action'] === 'changer_role') {
    $id = (int)$_GET['id'];
    $role_actuel = mysqli_real_escape_string($conn, $_GET['role']);
    $nouveau_role = ($role_actuel === 'admin') ? 'user' : 'admin';
    
    $sql = "UPDATE utilisateurs SET role = '$nouveau_role' WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['msg_success'] = "Rôle de l'utilisateur mis à jour avec succès.";
    }
    header("Location: admin.php");
    exit();
}

// Supprimer un utilisateur
if (isset($_GET['action']) && $_GET['action'] === 'suppr_user') {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM utilisateurs WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['msg_success'] = "Utilisateur supprimé avec succès.";
    }
    header("Location: admin.php");
    exit();
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
        $_SESSION['msg_success'] = "Question ajoutée avec succès.";
    }
    header("Location: admin.php");
    exit();
}

// ACTION AJOUTÉE / MODIFIÉE : Modifier entièrement une question en ligne (Exigence du PDF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_complete_question'])) {
    $id_question = (int)$_POST['id_question'];
    $question = mysqli_real_escape_string($conn, trim($_POST['question']));
    $theme = mysqli_real_escape_string($conn, trim($_POST['theme']));
    
    $sql = "UPDATE questions SET question = '$question', theme = '$theme' WHERE id = $id_question";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['msg_success'] = "La question et son thème ont été modifiés avec succès.";
    }
    header("Location: admin.php");
    exit();
}

// Supprimer une question
if (isset($_GET['action']) && $_GET['action'] === 'suppr_question') {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM questions WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['msg_success'] = "Question supprimée avec succès.";
    }
    header("Location: admin.php");
    exit();
}

// 4. RÉCUPÉRATION DES DONNÉES
$resultat_users = mysqli_query($conn, "SELECT * FROM utilisateurs ORDER BY nom ASC");
$utilisateurs = [];
while ($row = mysqli_fetch_assoc($resultat_users)) { $utilisateurs[] = $row; }

$resultat_questions = mysqli_query($conn, "SELECT * FROM questions ORDER BY id DESC");
$questions = [];
while ($row = mysqli_fetch_assoc($resultat_questions)) { $questions[] = $row; }

$resultat_themes = mysqli_query($conn, "SELECT DISTINCT theme FROM questions WHERE theme != '' AND theme IS NOT NULL ORDER BY theme ASC");
$themes_existants = [];
while ($row = mysqli_fetch_assoc($resultat_themes)) { $themes_existants[] = $row['theme']; }

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - QCM</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; color: #333; }
        .admin-container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        .nav-admin { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; background: #34495e; padding: 15px; border-radius: 6px; color: white; }
        .nav-admin a { color: #fff; text-decoration: none; background: #e74c3c; padding: 8px 15px; border-radius: 4px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 40px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle; }
        th { background-color: #f2f2f2; }
        .btn-action { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 0.85em; display: inline-block; margin-right: 5px; font-weight: bold;}
        .btn-danger { background-color: #e74c3c; }
        .btn-danger:hover { background-color: #c0392b; }
        .btn-role { background-color: #3498db; }
        .btn-role:hover { background-color: #2980b9; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button.btn-add { background: #2ecc71; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button.btn-add:hover { background: #27ae60; }
        .edit-input { padding: 6px; font-size: 0.9em; margin-bottom: 5px; display: block; width: 100%; }
        .btn-save-inline { background: #2c3e50; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 0.85em; }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="nav-admin">
        <span>Connecté : <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong> (Rôle : <?php echo $_SESSION['user_role']; ?>)</span>
        <a href="acceuil.php">Retour au site</a>
    </div>

    <h1>Espace Administration</h1>
    
    <?php if (!empty($message_success)): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius:4px; font-weight: bold;">
            <?php echo $message_success; ?>
        </div>
    <?php endif; ?>

    <h2>Gestion des utilisateurs (Modifier rôles / Supprimer)</h2>
    <table>
        <thead>
            <tr>
                <th>Nom / Prénom</th>
                <th>Email</th>
                <th>Rôle actuel</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><span style="text-transform: uppercase; font-weight: bold; color: <?php echo ($u['role'] === 'admin') ? '#e44d26' : '#555'; ?>"><?php echo htmlspecialchars($u['role']); ?></span></td>
                <td>
                    <a class="btn-action btn-role" href="admin.php?action=changer_role&id=<?php echo $u['id']; ?>&role=<?php echo $u['role']; ?>">Inverser Rôle</a>
                    <a class="btn-action btn-danger" href="admin.php?action=suppr_user&id=<?php echo $u['id']; ?>" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Ajouter une question</h2>
    <form action="admin.php" method="POST">
        <div class="form-group">
            <label>Énoncé de la question :</label>
            <textarea name="question" required placeholder="Exemple : Quelle fonction trie un tableau en PHP ?"></textarea>
        </div>
        <div class="form-group">
            <label>Thème de la question :</label>
            <input type="text" name="theme" list="themes_list" required placeholder="Exemple : PHP, HTML, CSS..." autocomplete="off">
            <datalist id="themes_list">
                <?php foreach ($themes_existants as $t): ?>
                    <option value="<?php echo htmlspecialchars($t); ?>">
                <?php endforeach; ?>
            </datalist>
        </div>
        <div class="form-group"><label>Option 1 :</label><input type="text" name="reponse1" required></div>
        <div class="form-group"><label>Option 2 :</label><input type="text" name="reponse2" required></div>
        <div class="form-group"><label>Option 3 :</label><input type="text" name="reponse3" required></div>
        <div class="form-group"><label>Option 4 :</label><input type="text" name="reponse4" required></div>
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

    <h2 style="margin-top: 40px;">Questions enregistrées (Modifier / Supprimer)</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Thème</th>
                <th style="width: 65%;">Énoncé de la question</th>
                <th style="width: 15%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $q): ?>
            <tr>
                <td colspan="2">
                    <form action="admin.php" method="POST" style="display: block; width:100%;">
                        <input type="hidden" name="id_question" value="<?php echo $q['id']; ?>">
                        
                        <label style="font-size: 0.8em; color: #777;">Thème :</label>
                        <input type="text" name="theme" class="edit-input" value="<?php echo htmlspecialchars($q['theme']); ?>" required>
                        
                        <label style="font-size: 0.8em; color: #777;">Énoncé :</label>
                        <textarea name="question" class="edit-input" rows="2" required><?php echo htmlspecialchars($q['question']); ?></textarea>
                        
                        <button type="submit" name="modifier_complete_question" class="btn-save-inline">💾 Enregistrer les modifications</button>
                    </form>
                </td>
                <td>
                    <a style="margin-top: 25px;" class="btn-action btn-danger" href="admin.php?action=suppr_question&id=<?php echo $q['id']; ?>" onclick="return confirm('Supprimer cette question ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html> 