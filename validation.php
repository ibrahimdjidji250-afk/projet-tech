<?php
session_start();
$db = new PDO("mysql:host=localhost;dbname=qcm1;charset=utf8", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'])) {
    $reponsesUtilisateur = $_POST['reponse'];
    $score = 0;
    $totalQuestions = count($reponsesUtilisateur);

    foreach ($reponsesUtilisateur as $idQuestion => $idReponseChoisie) {
        $stmt = $db->prepare("SELECT est_correcte FROM reponses WHERE id_reponse = ? AND id_question = ?");
        $stmt->execute([$idReponseChoisie, $idQuestion]);
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultat && $resultat['est_correcte'] == 1) {
            $score++;
        }
    }

    unset($_SESSION['qcm_questions']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 40px; text-align: center; }
        .result-box { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .score { font-size: 2.5em; color: #2ecc71; font-weight: bold; margin: 20px 0; }
        a { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
<div class="result-box">
    <h1>Résultat du Test</h1>
    <div class="score"><?php echo $score; ?> / <?php echo $totalQuestions; ?></div>
    <a href="qcm.php">Relancer un test</a>
</div>
</body>
</html>
<?php
} else {
    header('Location: qcm.php');
}
?>