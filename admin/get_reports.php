<?php

// Vérification de l'identification de l'utiliateur, il doit être role 1 donc admin, sinon page login.php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
    header('Location: ../login.php'); // Redirection vers la page de connexion si l'utilisateur n'est pas administrateur
    exit;
}

require '../functions.php';

// Connexion à la base de données

$db = new Database();
$conn = $db->connect();

// Récupération des variables de filtrage à partir des paramètres GET ou null si il y a rien

$visit_date = $_GET['visit_date'] ?? null;
$animal_id = $_GET['animal_id'] ?? null;

// Requête SQL de base pour récupérer les rapports vétérinaires*

$query = "SELECT vr.id, a.name as animal_name, vr.health_status, vr.food_given, vr.food_quantity, vr.visit_date, vr.details 
          FROM vet_reports vr
          JOIN animals a ON vr.animal_id = a.id
          WHERE 1=1";

// Tableau pour stocker les paramètres à utiliser dans la requête préparée

$params = [];

// Ajout des conditions de filtrage si les paramètres sont définis

if ($visit_date) {
    $query .= " AND vr.visit_date = :visit_date"; // Ajout de la condition de date de visite si visit_date est défini
    $params['visit_date'] = $visit_date; // Ajout de visit_date au tableau des paramètres
}
if ($animal_id) {
    $query .= " AND vr.animal_id = :animal_id"; // Ajout de la condition d'animal_id si animal_id est défini
    $params['animal_id'] = $animal_id; // Ajout de animal_id au tableau des paramètres
}

// Préparation de la requête SQL avec PDO

$stmt = $conn->prepare($query);

// Liaison des paramètres de la requête préparée avec leurs valeurs respectives

foreach ($params as $key => $value) {
    $stmt->bindParam(':'.$key, $value);
}
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configuration de l'en-tête HTTP pour indiquer que le contenu est au format JSON

header('Content-Type: application/json');

// Encodage des résultats en JSON et affichage
echo json_encode($reports);
