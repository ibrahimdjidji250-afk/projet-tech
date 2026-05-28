<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
$host = 'localhost';
$dbname = 'qcm1';
$user = 'root';

$pass = 'root'; // Mot de passe par défaut MAMP
$pass = 'root';

$message = '';
$status = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['mot_de_passe'];

    // 1. Correction de la requête : 'id' au lieu de 'id_utilisateur' pour correspondre à la BDD
    $stmtCheck = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    // 1. Vérifier si l'email existe déjà (Contrainte email unique)
   $stmtCheck = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmtCheck->execute([$email]);
    
    if ($stmtCheck->fetch()) {
        $message = "Cette adresse email est déjà utilisée pour un autre compte.";
        $status = "error";
    } else {
        // 2. Sécuriser le mot de passe (Contrainte hash du cahier des charges)
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insertion dans la table avec les colonnes exactes de la BDD (nom, prenom, email, mot_de_passe)
        // Par défaut, le rôle n'est pas spécifié pour qu'il soit un 'user' basique en BDD
        $stmtInsert = $db->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
        
        try {
            if ($stmtInsert->execute([$nom, $prenom, $email, $passwordHash])) {
                $message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                $status = "success";
            } else {
                $message = "Une erreur est survenue lors de l'inscription.";
                $status = "error";
            }
        } catch (PDOException $ex) {
            $message = "Erreur technique lors de l'enregistrement : " . $ex->getMessage();
            $status = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3.1 Inscription</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 40px; }
        .form-box { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; font-size: 1.5em; margin-bottom: 20px; text-align: center; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input { width: 100%; padding: 10px; margin-top: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; margin-top: 20px; cursor: pointer; font-size: 1em; }
        button:hover { background: #2980b9; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; text-align: center; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .link { text-align: center; margin-top: 15px; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="form-box">
    <h1>3.1 Inscription</h1>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $status; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="inscription.php" method="POST">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required>

        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" required>

        <label for="email">Adresse Email :</label>
        <input type="email" id="email" name="email" required>

        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>

        <button type="submit">Créer mon compte</button>
    </form>

    <div class="link">
        Déjà inscrit ? <a href="connexion.php">Connectez-vous ici</a>
    </div>
</div>

</body>
</html>
