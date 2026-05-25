-- Création et utilisation de la base
CREATE DATABASE IF NOT EXISTS `qcm1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `qcm1`;

-- Suppression ordonnée des tables si elles existent déjà
DROP TABLE IF EXISTS `réponses`;
DROP TABLE IF EXISTS `tentatives`;
DROP TABLE IF EXISTS `questions`;
DROP TABLE IF EXISTS `utilisateurs`;

-- Table utilisateurs selon le cahier des charges
CREATE TABLE `utilisateurs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table questions selon le cahier des charges
CREATE TABLE `questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question` TEXT NOT NULL,
  `reponse1` VARCHAR(255) NOT NULL,
  `reponse2` VARCHAR(255) NOT NULL,
  `reponse3` VARCHAR(255) NOT NULL,
  `reponse4` VARCHAR(255) NOT NULL,
  `bonne_reponse` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table tentatives selon le cahier des charges
CREATE TABLE `tentatives` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `utilisateur_id` INT NOT NULL,
  `score` FLOAT NOT NULL,
  `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_tentatives_utilisateurs` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table réponses selon le cahier des charges
CREATE TABLE `réponses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tentative_id` INT NOT NULL,
  `question_id` INT NOT NULL,
  `reponse_utilisateur` INT NOT NULL,
  `correcte` TINYINT(1) NOT NULL,
  CONSTRAINT `fk_reponses_tentatives` FOREIGN KEY (`tentative_id`) REFERENCES `tentatives`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reponses_questions` FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion des 100 questions de développement web
INSERT INTO `questions` (`question`, `reponse1`, `reponse2`, `reponse3`, `reponse4`, `bonne_reponse`) VALUES
-- [PHP Core & Syntaxe : 1 à 20]
('Que signifie l''acronyme PHP ?', 'Hypertext Preprocessor', 'Personal Home Page', 'Processor Hypertext Protocol', 'Precompiled Hypertext Page', 1),
('Quelle fonction PHP permet de vérifier si une variable existe et n''est pas NULL ?', 'is_set()', 'isset()', 'defined()', 'check_var()', 2),
('Comment démarre-t-on une session en PHP ?', 'start_session()', 'session_begin()', 'session_start()', 'register_session()', 3),
('Quelle superglobale PHP contient les données envoyées via un formulaire méthode GET ?', '$_POST', '$_REQUEST', '$_SERVER', '$_GET', 4),
('Quelle fonction PHP supprime les espaces en début et fin de chaîne ?', 'strip()', 'trim()', 'clean()', 'replace()', 2),
('Comment écrit-on un commentaire sur une seule ligne en PHP ?', '# ou //', '', '/* */', '**', 1),
('Quel opérateur est utilisé pour la concaténation en PHP ?', '+', '.', ',', '&', 2),
('Quelle fonction permet de trier un tableau indexé par ordre alphabétique ?', 'sort()', 'asort()', 'ksort()', 'tsort()', 1),
('Quelle superglobale contient des informations sur les en-têtes, les chemins et les scripts ?', '$_ENV', '$_SESSION', '$_SERVER', '$_GLOBAL', 3),
('Comment déclare-t-on une constante en PHP ?', 'constante()', 'define()', 'const()', 'Les choix 2 et 3 sont corrects', 4),
('Quelle structure de contrôle permet de tester plusieurs valeurs pour une même variable ?', 'if / else', 'switch', 'while', 'foreach', 2),
('Quelle fonction PHP convertit une chaîne de caractères en tableau ?', 'implode()', 'split()', 'explode()', 'join()', 3),
('Que renvoie gettype(10.5) en PHP ?', 'integer', 'double (ou float)', 'string', 'boolean', 2),
('Comment inclure un fichier PHP en arrêtant le script en cas d''erreur ?', 'include', 'require', 'require_once', 'Les choix 2 et 3 sont valides', 4),
('Quelle superglobale est utilisée pour gérer les fichiers téléchargés ?', '$_GET', '$_POST', '$_FILES', '$_DOWNLOAD', 3),
('Quelle fonction supprime la dernière valeur d''un tableau PHP ?', 'array_pop()', 'array_push()', 'array_shift()', 'array_unset()', 1),
('Quel opérateur teste l''égalité stricte de valeur et de type en PHP ?', '=', '==', '===', '!=', 3),
('Que fait la fonction header(''Location: url'') en PHP ?', 'Elle modifie le titre HTML', 'Elle effectue une redirection HTTP', 'Elle ajoute une feuille de style', 'Elle affiche un lien hypertexte', 2),
('Quelle boucle est idéale pour parcourir un tableau associatif en PHP ?', 'for', 'while', 'do...while', 'foreach', 4),
('Quelle fonction PHP compte le nombre d''éléments dans un tableau ?', 'count()', 'size()', 'length()', 'total()', 1),

-- [HTML5 : 21 à 40]
('Que signifie HTML ?', 'HyperText Markup Language', 'High Tech Markup Language', 'Hyperlinks Text Management Language', 'Home Tool Markup Language', 1),
('Quelle balise HTML5 définit le contenu autonome principal de la page ?', '<section>', '<main>', '<content>', '<body>', 2),
('Quelle balise HTML est utilisée pour insérer une image ?', '<picture>', '<src>', '<img>', '<image>', 3),
('Quel attribut HTML permet d''ouvrir un lien dans un nouvel onglet ?', 'target="_blank"', 'rel="new"', 'window="new"', 'href="blank"', 1),
('Quelle est la balise correcte pour insérer un saut de ligne en HTML5 ?', '<lb>', '<break>', '<next>', '<br>', 4),
('Quel attribut fournit un texte alternatif pour une image si elle ne s''affiche pas ?', 'title', 'alt', 'desc', 'src', 2),
('Quelle balise est utilisée pour créer une liste ordonnée (numérotée) ?', '<ul>', '<li>', '<ol>', '<list>', 3),
('Quel élément HTML5 est utilisé pour afficher une barre de progression ?', '<bar>', '<progress>', '<meter>', '<loading>', 2),
('Quel type d''input HTML5 ajoute nativement un calendrier de sélection ?', 'type="time"', 'type="calendar"', 'type="date"', 'type="month"', 3),
('Quelle balise HTML est utilisée pour définir les choix d''une liste déroulante ?', '<select>', '<list>', '<option>', '<input>', 3),
('Quel attribut HTML5 permet de rendre un champ de formulaire obligatoire ?', 'validate', 'required', 'important', 'needed', 2),
('Quelle balise entoure une ligne entière dans un tableau HTML ?', '<td>', '<th>', '<tr_row>', '<tr>', 4),
('Quelle balise est utilisée pour insérer du code JavaScript directement dans le HTML ?', '<javascript>', '<script>', '<js>', '<code type="js">', 2),
('Quelle balise HTML5 est dédiée à l''affichage de contenu secondaire (barre latérale) ?', '<sidebar>', '<aside>', '<section>', '<meta>', 2),
('Quelle balise crée un titre principal avec la plus grande importance sémantique ?', '<h6>', '<title>', '<h1>', '<head>', 3),
('Quel élément HTML contient les métadonnées de la page (non visibles directement) ?', '<body>', '<meta>', '<head>', '<footer>', 3),
('Quelle balise permet de regrouper sémantiquement un formulaire complet ?', '<fieldset>', '<form>', '<block_form>', 'Les choix 1 et 2 travaillent ensemble', 4),
('Quel attribut lie sémantiquement une balise <label> à un <input> ?', 'id', 'for', 'name', 'type', 2),
('Quel encodage de caractères est standardisé pour le Web moderne dans la balise <meta> ?', 'ISO-8859-1', 'ASCII', 'UTF-8', 'Windows-1252', 3),
('Quelle balise HTML crée un conteneur générique de type bloc ?', '<span>', '<div>', '<section>', '<p>', 2),

-- [CSS3 : 41 à 60]
('Que signifie CSS ?', 'Computer Style Sheets', 'Cascading Style Sheets', 'Creative Style Systems', 'Colorful Style Sheets', 2),
('Quelle propriété CSS est utilisée pour changer la couleur du texte ?', 'background-color', 'font-color', 'color', 'text-style', 3),
('Comment sélectionnez-vous un élément avec l''id "menu" en CSS ?', '.menu', '#menu', 'menu', '*menu', 2),
('Quelle propriété contrôle la taille du texte en CSS ?', 'font-size', 'text-size', 'size', 'font-style', 1),
('Quelle propriété CSS applique un espace à l''INTÉRIEUR des bordures d''un élément ?', 'margin', 'border-space', 'padding', 'gap', 3),
('Quelle valeur de position retire l''élément du flux normal pour le placer par rapport au navigateur ?', 'relative', 'absolute', 'static', 'fixed', 4),
('Comment appliquer un style au survol de la souris sur un lien en CSS ?', 'a:hover', 'a:click', 'a:visit', 'a:active', 1),
('Quelle propriété permet de centrer horizontalement des éléments en Flexbox ?', 'align-items', 'justify-content', 'text-align', 'flex-center', 2),
('Quel sélecteur CSS cible tous les éléments d''une page ?', '#all', '.', '*', 'body', 3),
('Quelle propriété CSS permet de mettre le texte entièrement en majuscules ?', 'text-transform', 'text-style', 'font-weight', 'capitalize', 1),
('Quelle propriété change l''épaisseur d''une police de caractères ?', 'font-style', 'font-size', 'font-weight', 'font-bold', 3),
('En CSS Grid, quelle propriété définit l''espace entre les colonnes et lignes ?', 'margin', 'padding', 'gap', 'border-spacing', 3),
('Comment masquer un élément du DOM tout en conservant l''espace qu''il occupe ?', 'display: none;', 'visibility: hidden;', 'opacity: 1;', 'float: none;', 2),
('Quelle propriété CSS arrondit les angles d''un conteneur ?', 'border-radius', 'corner-radius', 'box-shadow', 'border-style', 1),
('Quelle unité CSS est relative à la taille de police de l''élément racine (html) ?', 'px', 'em', 'rem', '%', 3),
('Quelle règle CSS3 permet de concevoir un design responsive adapté aux écrans mobiles ?', '@responsive', '@media', '@screen', '@mobile', 2),
('Quelle propriété gère la superposition des éléments positionnés (axe Z) ?', 'z-index', 'layer', 'position-depth', 'index', 1),
('Comment écrit-on un commentaire en CSS ?', '// commentaire', '/* commentaire */', '', '# commentaire', 2),
('Quelle propriété change la police de caractères en CSS ?', 'font-family', 'font-type', 'text-font', 'font-style', 1),
('Quelle propriété CSS gère la répétition d''une image d''arrière-plan ?', 'background-repeat', 'background-size', 'background-attachment', 'image-repeat', 1),

-- [SQL & Bases de données : 61 à 80]
('Quelle clause SQL est utilisée pour trier les résultats d''une requête ?', 'SORT BY', 'GROUP BY', 'ORDER BY', 'ALIGN BY', 3),
('Que fait l''instruction SQL : SELECT COUNT(*) FROM questions ?', 'Elle supprime toutes les questions', 'Elle compte le nombre total de lignes de la table', 'Elle affiche la liste des colonnes', 'Elle sélectionne une ligne au hasard', 2),
('Quel mot-clé SQL permet d''éliminer les doublons d''un résultat ?', 'UNIQUE', 'DISTINCT', 'DIFFERENT', 'ISOLATE', 2),
('Quelle clause SQL filtre les lignes renvoyées selon une condition précise ?', 'HAVING', 'GROUP BY', 'WHERE', 'IF', 3),
('Quelle commande SQL ajoute de nouvelles données dans une table ?', 'ADD ROW', 'INSERT INTO', 'UPDATE', 'CREATE', 2),
('Que signifie l''acronyme SQL ?', 'Structured Query Language', 'Simple Query Language', 'Sequential Query Language', 'Standard Query Language', 1),
('Quel mot-clé SQL modifie des données déjà existantes dans une table ?', 'CHANGE', 'MODIFY', 'UPDATE', 'ALTER', 3),
('Quelle contrainte garantit qu''une colonne ne peut pas avoir de valeur vide ?', 'UNIQUE', 'NOT NULL', 'PRIMARY KEY', 'DEFAULT', 2),
('Quel type de jointure SQL retourne les lignes s''il y a une correspondance dans les deux tables ?', 'LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN', 'OUTER JOIN', 3),
('Quelle fonction d''agrégation SQL calcule la valeur moyenne d''une colonne numérique ?', 'SUM()', 'AVG()', 'MEAN()', 'COUNT()', 2),
('Comment supprimer une table entière nommée "test" de la base de données ?', 'DELETE TABLE test;', 'REMOVE TABLE test;', 'DROP TABLE test;', 'TRUNCATE TABLE test;', 3),
('Quelle clause regroupe les lignes ayant les mêmes valeurs pour des fonctions d''agrégation ?', 'ORDER BY', 'GROUP BY', 'HAVING', 'SORT BY', 2),
('Quel caractère joker représente toutes les colonnes d''une table dans un SELECT ?', '%', '?', '_', '*', 4),
('Quelle instruction SQL supprime définitivement toutes les lignes d''une table sans détruire sa structure ?', 'DROP', 'TRUNCATE', 'REMOVE', 'CLEAN', 2),
('Que signifie l''acronyme PDO en PHP ?', 'PHP Data Objects', 'PHP Database Option', 'Protocol Data Object', 'Precompiled Data Operator', 1),
('Quelle méthode de l''objet PDO prépare une requête pour éviter les injections SQL ?', 'query()', 'exec()', 'prepare()', 'execute()', 3),
('En base de données, qu''est-ce qu''une clé primaire ?', 'Une clé pour chiffrer la table', 'Un identifiant unique pour chaque ligne', 'Une liaison vers une autre table', 'Une colonne de type texte', 2),
('Quelle contrainte établit un lien logique entre deux tables (clé étrangère) ?', 'PRIMARY KEY', 'FOREIGN KEY', 'UNIQUE', 'LINK', 2),
('Quel mot-clé filtre les résultats d''un GROUP BY ?', 'WHERE', 'HAVING', 'ORDER BY', 'DISTINCT', 2),
('Comment s''appelle l''outil web de gestion MySQL fourni par défaut avec MAMP ?', 'MySQL Workbench', 'phpMyAdmin', 'DBeaver', 'Adminer', 2),

-- [JavaScript & Logique Web : 81 à 100]
('Quelle méthode JS permet d''écrire un message d''alerte pop-up ?', 'alert()', 'popup()', 'msg()', 'console.log()', 1),
('Comment cible-t-on un élément HTML par son ID en JavaScript ?', 'document.getClass()', 'document.getElementById()', 'document.querySelector()', 'Les choix 2 et 3 sont corrects', 4),
('Quel mot-clé introduit une variable à portée de bloc modifiable en JS moderne ?', 'var', 'let', 'const', 'global', 2),
('Quel événement JavaScript détecte un clic sur un élément HTML ?', 'onclick', 'onhover', 'onchange', 'onload', 1),
('Comment déclare-t-on une fonction en JavaScript ?', 'function maFonction()', 'method maFonction()', 'def maFonction()', 'procedure maFonction()', 1),
('Quelle méthode JavaScript planifie l''exécution d''un code après un délai en millisecondes ?', 'setInterval()', 'setTimeout()', 'delay()', 'wait()', 2),
('Quel opérateur JS teste la stricte égalité de valeur ET de type ?', '==', '=', '===', 'equal', 3),
('Comment ajouter un élément à la fin d''un tableau en JavaScript ?', 'array.pop()', 'array.push()', 'array.append()', 'array.add()', 2),
('Quel objet JavaScript permet d''interagir avec l''adresse URL courante du navigateur ?', 'window.document', 'window.location', 'window.history', 'window.screen', 2),
('Quel événement JavaScript s''active lorsque l''utilisateur quitte un onglet ou la fenêtre ?', 'onload', 'onfocus', 'onblur', 'onclose', 3),

-- [Sécurité & Architecture : 91 à 100]
('Quelle faille de sécurité arrive si on injecte du code SQL malveillant dans un formulaire ?', 'Faille XSS', 'Injection SQL', 'Faille CSRF', 'Brute Force', 2),
('Quel mécanisme PHP protège les sessions contre le vol d''identité basique ?', 'session_regenerate_id()', 'session_unset()', 'md5()', 'strip_tags()', 1),
('Quelle fonction PHP convertit les caractères spéciaux en entités HTML pour éviter les failles XSS ?', 'htmlspecialchars()', 'strip_tags()', 'md5()', 'addslashes()', 1),
('Quelle méthode d''envoi de formulaire masque les données de l''URL et n''a pas de limite de taille ?', 'GET', 'POST', 'PUT', 'REQUEST', 2),
('Quel type de chiffrement/hachage génère un hash unique de longueur fixe impossible à inverser ?', 'Hachage à sens unique', 'Chiffrement symétrique', 'Chiffrement asymétrique', 'Décodage Base64', 1),
('Quelle architecture sépare la logique de données (Modèle) de l''affichage (Vue) et du code (Contrôleur) ?', 'Architecture API', 'Architecture MVC', 'Architecture REST', 'Architecture Microservices', 2),
('Dans les statuts de réponse HTTP, à quoi correspond la célèbre erreur 404 ?', 'Succès de la requête', 'Erreur interne du serveur', 'Accès interdit', 'Ressource non trouvée', 4),
('Que signifie le protocole HTTPS par rapport à HTTP ?', 'Il est plus rapide', 'Il chiffre les échanges de données', 'Il n''utilise pas de cookies', 'Il est réservé aux mobiles', 2),
('Quel fichier de configuration permet de paramétrer le comportement d''Apache sous MAMP ?', 'php.ini', '.htaccess', 'my.cnf', 'config.xml', 2),
('Quel code de statut HTTP indique que la requête a réussi avec succès ?', '200 OK', '301 Moved Permanently', '403 Forbidden', '500 Internal Server Error', 1);