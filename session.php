<?php
// Vérifier si l'utilisateur est bien connecté via sa session
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion s'il n'est pas identifié
    header('Location: connexion.php');
    exit();
}
?>