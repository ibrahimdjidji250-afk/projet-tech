<?php
session_start();

// 1. Connexion à la base de données qcm1
$host = 'localhost';
$dbname = 'qcm1';
$user = 'root';
$pass = '';

$error = '';

// Gestion de la Déconnexion (Section 3.2 du projet)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy(); // Détruit toutes les variables de session
    header('Location: connexion.php?logout=success');
    exit();
}

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// 2. Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['mot_de_passe'];

    // Récupérer l'utilisateur par son email dans ta base de données
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification de l'existence de l'utilisateur et du mot de passe haché
    if ($user && password_verify($password, $user['mot_de_passe'])) {
        
        // Vérifier si l'administrateur a bloqué cet utilisateur
        if (isset($user['est_bloque']) && $user['est_bloque'] == 1) {
            $error = "❌ Votre compte a été bloqué par un administrateur.";
        } else {
            // Création de la session utilisateur (Section 3.2)
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];

            // Redirection automatique vers le QCM une fois connecté
            header('Location: qcm.php');
            exit();
        }
    } else {
        $error = "❌ Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3.2 Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 30px;
            width: 100%;
            max-width: 380px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            font-size: 1.6em;
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            margin-top: 25px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
        }
        button:hover {
            background-color: #27ae60;
        }
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: bold;
            text-align: center;
            font-size: 0.9em;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }
        .footer-links a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h1>3.2 Connexion</h1>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
        <div class="alert success">✓ Vous avez été déconnecté.</div>
    <?php endif; ?>

    <form action="connexion.php" method="POST">
        <label for="email">Adresse Email :</label>
        <input type="email" id="email" name="email" required placeholder="exemple@mail.com">

        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required placeholder="••••••••">

        <button type="submit">Se connecter</button>
    </form>

    <div class="footer-links">
        Pas encore de compte ? <a href="inscription.php">Créez un compte ici</a>
    </div>
</div>

</body>
</html>