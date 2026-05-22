<?php
session_start();

$host = 'localhost';
$dbname = 'qcm1'; // <-- Même base de données
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'])) {
    $reponsesUtilisateur = $_POST['reponse'];
    $score = 0;
    $totalQuestions = count($_SESSION['qcm_questions'] ?? $reponsesUtilisateur);

    // Vérification de chaque réponse soumise
    foreach ($reponsesUtilisateur as $idQuestion => $idReponseChoisie) {
        $stmt = $db->prepare("SELECT est_correcte FROM reponses WHERE id_reponse = ? AND id_question = ?");
        $stmt->execute([$idReponseChoisie, $idQuestion]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultat && $resultat['est_correcte'] == 1) {
            $score++;
        }
    }

    // Nettoyage de la session pour pouvoir relancer un nouveau test propre
    unset($_SESSION['qcm_questions']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat du QCM</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 40px; text-align: center; }
        .result-box { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .score { font-size: 2.5em; color: #2ecc71; font-weight: bold; margin: 20px 0; }
        .score.bad { color: #e74c3c; }
        a { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; }
        a:hover { background-color: #2980b9; }
    </style>
</head>
<body>

<div class="result-box">
    <h1>Examen Terminé</h1>
    <p>Votre score final est de :</p>
    <div class="score <?php echo ($score < ($totalQuestions/2)) ? 'bad' : ''; ?>">
        <?php echo $score; ?> / <?php echo $totalQuestions; ?>
    </div>
    <p><?php echo ($score >= ($totalQuestions/2)) ? "Félicitations, vous avez validé le module !" : "Niveau insuffisant, continuez à réviser."; ?></p>
    
    <a href="qcm.php">Repasser un test alternatif</a>
</div>

</body>
</html>
<?php
} else {
    // Redirection si accès direct au fichier sans formulaire
    header('Location: qcm.php');
    exit();
}
?>