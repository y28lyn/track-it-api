<?php
require_once 'db_connect.php';

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'POST':
        getUsers();
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}

function getUsers()
{
    global $conn;
    $query = "SELECT * FROM utilisateurs";
    $response = array();
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_array($result)) {
        array_push($response, array('id' => $row['id'], 'nom' => $row['nom'], 'prenom' => $row['prenom'], 'email' => $row['email'], 'mot_de_passe' => $row['mot_de_passe']));
    }

    header('Content-Type: application/json');
    echo json_encode(array('status' => 'success', 'data' => $response));
}
