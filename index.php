<?php
// Configuration de la connexion MAMP
$host = 'localhost';
$dbname = 'qcm1';
$user = 'root';
$pass = 'root'; // Mot de passe MAMP requis

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}

// 1. Sélectionner exactement 10 questions aléatoires parmi les 100 disponibles [cite: 7, 11]
$queryQuestions = $db->query("SELECT * FROM questions ORDER BY RAND() LIMIT 10");
$questions = $queryQuestions->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passage du QCM</title>
    <style>
        /* Styles généraux */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 40px 20px;
            background-color: #f4f7f9; 
            color: #333; 
            user-select: none; /* Anti-triche : empêche la sélection de texte [cite: 69] */
        }

        /* Conteneur principal corrigé (800px au lieu de 80px) */
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
        }

        h1 { 
            text-align: center;
            color: #007BFF; 
            margin-bottom: 30px;
            font-size: 28px;
            border-bottom: 3px solid #eef2f5;
            padding-bottom: 15px;
        }

        /* Blocs de questions [cite: 47] */
        .question-block { 
            margin-bottom: 30px; 
            background: #f8fafc; 
            padding: 25px; 
            border-radius: 8px; 
            border-left: 5px solid #007BFF;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .question-text {
            font-size: 18px;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 15px;
            color: #1e293b;
        }

        /* Options de réponses sous forme de grands boutons radio cliquables [cite: 47] */
        label { 
            display: flex;
            align-items: center;
            margin: 10px 0; 
            cursor: pointer; 
            padding: 12px 15px; 
            border: 1px solid #e2e8f0;
            border-radius: 6px; 
            background: #ffffff;
            transition: all 0.2s ease-in-out; 
            font-size: 15px;
        }

        label:hover { 
            background: #f1f5f9; 
            border-color: #cbd5e1;
        }

        /* Quand le bouton radio est coché, on colore le fond de la réponse */
        label:has(input:checked) {
            background-color: #e0f2fe;
            border-color: #0ea5e9;
            color: #0369a1;
            font-weight: 500;
        }

        input[type="radio"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Bouton de validation [cite: 49] */
        .btn-container {
            text-align: center;
            margin-top: 40px;
        }

        button { 
            background-color: #007BFF; 
            color: white; 
            border: none; 
            padding: 15px 35px; 
            font-size: 18px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: bold; 
            transition: background 0.2s;
            box-shadow: 0 4px 6px rgba(0, 123, 255, 0.15);
        }

        button:hover { 
            background-color: #0056b3; 
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Examen : QCM de Développement Web</h1>
        
        <form action="validation.php" method="POST">
            <?php foreach ($questions as $index => $q): ?>
                <div class="question-block">
                    <p class="question-text">
                        <strong>Question <?php echo $index + 1; ?> :</strong> 
                        <?php echo htmlspecialchars($q['question']); ?>
                    </p>
                    
                    <label>
                        <input type="radio" name="reponse[<?php echo $q['id']; ?>]" value="1" required>
                        <?php echo htmlspecialchars($q['reponse1']); ?>
                    </label>
                    
                    <label>
                        <input type="radio" name="reponse[<?php echo $q['id']; ?>]" value="2">
                        <?php echo htmlspecialchars($q['reponse2']); ?>
                    </label>
                    
                    <label>
                        <input type="radio" name="reponse[<?php echo $q['id']; ?>]" value="3">
                        <?php echo htmlspecialchars($q['reponse3']); ?>
                    </label>
                    
                    <label>
                        <input type="radio" name="reponse[<?php echo $q['id']; ?>]" value="4">
                        <?php echo htmlspecialchars($q['reponse4']); ?>
                    </label>
                </div>
            <?php endforeach; ?>

            <div class="btn-container">
                <button type="submit">Valider mes réponses</button>
            </div>
        </form>
    </div>

    <script>
        [cite_start]// Anti-triche : Détection de changement d'onglet [cite: 58]
        window.onblur = function() {
            console.log("L'utilisateur a quitté la page [cite: 60, 61]");
        };
    </script>
</body>
</html>







