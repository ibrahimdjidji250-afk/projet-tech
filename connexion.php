<?php
session_start();

// 1. Connexion à la base de données qcm1
$host = 'localhost';
$dbname = 'qcm1';
$user = 'root';
$pass = 'root'; // Requis pour MAMP
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) { die('Erreur de connexion : ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

// S'il a déjà un cookie de connexion et n'est pas en session, on le connecte automatiquement
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_email'])) {
    $cookie_email = mysqli_real_escape_string($conn, $_COOKIE['user_email']);
    $sql = "SELECT * FROM utilisateurs WHERE email = '$cookie_email'";
    $resultat = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($resultat);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
    }
}

// Redirection si déjà connecté (en vérifiant s'il n'y a pas une redirection admin en attente)
if (isset($_SESSION['user_id']) && !isset($_GET['action'])) {
    if (isset($_SESSION['redirect_to']) && !empty($_SESSION['redirect_to'])) {
        $destination = $_SESSION['redirect_to'];
        unset($_SESSION['redirect_to']);
        header("Location: " . $destination);
    } else {
        header('Location: selection_theme.php');
    }
    exit();
}

// Gestion de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    // On détruit le cookie en lui donnant une date expirée
    setcookie('user_email', '', time() - 3600, '/');
    header('Location: connexion.php?logout=success');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['mot_de_passe'];

    $sql = "SELECT * FROM utilisateurs WHERE email = '$email'";
    $resultat = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($resultat);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        // Stockage des informations essentielles de l'utilisateur en Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role']; 
        
        // Si la case "Se souvenir de moi" est cochée, on crée un cookie pour 30 jours
        if (isset($_POST['remember_me'])) {
            setcookie('user_email', $user['email'], time() + (30 * 24 * 60 * 60), '/');
        }

        // 🔥 REDIRECTION INTELLIGENTE : admin.php si demandé, sinon selection_theme.php
        if (isset($_SESSION['redirect_to']) && !empty($_SESSION['redirect_to'])) {
            $destination = $_SESSION['redirect_to'];
            unset($_SESSION['redirect_to']); // Nettoyage de la variable
            header("Location: " . $destination);
        } else {
            header('Location: selection_theme.php');
        }
        exit();
    } else {
        $error = "❌ Email ou mot de passe incorrect.";
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background-color: #f7f7f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 400px; border: 1px solid #e0e0db; }
        h1 { font-size: 1.6em; text-align: center; color: #111; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; margin-bottom: 5px; color: #555; font-weight: 500; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #e0e0db; border-radius: 6px; box-sizing: border-box; }
        .remember-container { display: flex; align-items: center; gap: 8px; margin-top: 15px; font-size: 0.95rem; color: #555; }
        button { width: 100%; padding: 14px; background: #111; color: white; border: none; border-radius: 6px; margin-top: 20px; cursor: pointer; font-weight: 600; }
        button:hover { background: #6c63ff; }
        .alert { padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .error { background: #f8d7da; color: #721c24; }
        .success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
<div class="login-box">
    <h1>Connexion</h1>
    <?php if(!empty($error)): ?><div class="alert error"><?php echo $error; ?></div><?php endif; ?>
    <?php if(isset($_GET['logout'])): ?><div class="alert success">✓ Déconnecté.</div><?php endif; ?>

    <form action="connexion.php" method="POST">
        <label>Adresse Email :</label>
        <input type="email" name="email" required>

        <label>Mot de passe :</label>
        <input type="password" name="mot_de_passe" required>

        <div class="remember-container">
            <input type="checkbox" name="remember_me" id="remember_me">
            <label for="remember_me" style="display:inline; margin:0; cursor:pointer;">Se souvenir de moi</label>
        </div>

        <button type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>

