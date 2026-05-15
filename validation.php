<?php
$host = 'localhost';
$dbname = 'votre_bdd';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'])) {
    $reponsesUtilisateur = $_POST['reponse']; // Tableau : [id_question => id_reponse_choisie]
    $score = 0;
    $totalQuestions = count($reponsesUtilisateur);

    foreach ($reponsesUtilisateur as $idQuestion => $idReponseChoisie) {
        // On vérifie si l'ID de la réponse choisie a la colonne 'est_correcte' à 1
        $stmt = $db->prepare("SELECT est_correcte FROM reponses WHERE id_reponse = ? AND id_question = ?");
        $stmt->execute([$idReponseChoisie, $idQuestion]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultat && $resultat['est_correcte'] == 1) {
            $score++;
        }
    }

    echo "<h1>Résultat du QCM</h1>";
    echo "<p>Vous avez obtenu un score de : <strong>$score / $totalQuestions</strong></p>";
    echo "<a href='index.php'>Recommencer</a>";
} else {
    header('Location: index.php');
}
?>