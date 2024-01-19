<?php
// S'il n'est pas fourni, on renvoie vers 404
// Récupérer le paramètre matricule
if(!isset($_GET['matricule'])) {
    header("Location: 404.php");
    exit();
}

$matricule = $_GET["matricule"];

// Se connecter à la base de données
// Si ça ne marche pas, on renvoie vers 500

$hostname = "localhost";
$dbname = "istdiplome";
$user = "root";
$password = "";
$dns = "mysql:host=$hostname;dbname=$dbname";

try {
    $db = new PDO($dns, $user, $password);

    // Lancer les 3 requêtes pour récupérer les informations
    // En cas de vide, on renvoie vers 404


    // Récupérer les informations de l'étudiant
    $student_sql = "SELECT etudiants.matricule, etudiants.nom, etudiants.prenom, departements.nom AS departement, personnel.nom AS nom_chef, personnel.prenom AS prenom_chef
    FROM etudiants
    INNER JOIN departements
    ON etudiants.depart_id = departements.id
    INNER JOIN personnel
    ON personnel.id = departements.chef_id
    WHERE etudiants.matricule=?";

    $student_stmt = $db->prepare($student_sql);
    $student_stmt->execute(array($matricule, ));

    // Si aucun resultat n'est retourné, on renvoie vers 404
    if($student_stmt->rowCount() === 0) {
        header("Location: 404.php");
        exit();
    }
    $student_row = $student_stmt->fetch(PDO::FETCH_ASSOC);



    // Récupérer les informations du DG
    $dg_sql = "SELECT * FROM personnel
    WHERE code_poste='DG';";

    $dg_stmt = $db->prepare($dg_sql);
    $dg_stmt->execute();
    $dg_row = $dg_stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    header("Location: 500.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récupérez votre Diplôme !</title>
    <link rel="stylesheet" href="assets/css/download.css" />
</head>
<body>
    <h1>Téléchargez votre diplôme</h1>

    <div class="diplome">
        <h1>Diplôme de l'Institut Supérieur de Technologie de Mamou</h1>

        <p>Nous déclarons que</p>

        <h2>
            <?php echo $student_row["prenom"] . " " . $student_row["nom"] ?>
        </h2>
        
        <p>sous le matricule <?php echo $student_row["matricule"] ?></p>

        <div class="ligne"></div>

        <p>a satisfait aux exigences de la licence en <?php echo $student_row["departement"] ?>.</p>

        <div class="footer">
            <div>
                <div class="ligne"></div>
                <p class="fullname">
                    <?php echo $student_row["prenom_chef"] . " " . $student_row["nom_chef"] ?>
                </p>
                <p class="poste">Chef de Département</p>
            </div>

            <div class="badge">
                <img src="assets/images/badge.png" alt="Badge du diplôme" />
            </div>

            <div>
                <div class="ligne"></div>
                <p class="fullname">
                    Dr 
                    <?php echo $dg_row['prenom'] . " " . $dg_row["nom"] ?>
                </p>
                <p class="poste">Directeur Général</p>
            </div>
        </div>
    </div>

    <button>
        <img src="assets/images/download.png" alt="Icône de téléchargement" />
        <br>
        Imprimez
    </button>


    <script src="assets/js/html2pdf.bundle.min.js"></script>
    <script>
        const button = document.querySelector("button");
        button.addEventListener('click', triggerDownload);

        function triggerDownload() {
            const diplome = document.querySelector(".diplome");
            var opt = {
                margin:       1,
                filename:     'myfile.pdf',
                image:        { type: 'png', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
            };
            
            // Enregistrer en PDF
            html2pdf(diplome, opt);
        }
    </script>
</body>
</html>