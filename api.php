<?php
require_once 'db_connect.php';

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'POST':
        // Récupérer les données du formulaire
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action'])) {
            $action = $data['action'];

            if ($action == 'login') {
                if (isset($data['email']) && isset($data['password'])) {
                    $email = $data['email'];
                    $password = md5($data['password']); // Vous devriez envisager des méthodes de hachage plus sécurisées

                    // Requête SQL pour vérifier les informations de connexion dans la base de données
                    $sql = "SELECT id, nom, prenom FROM utilisateurs WHERE email = ? AND mot_de_passe = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $email, $password);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        // L'utilisateur est authentifié
                        $row = $result->fetch_assoc();
                        $response = array(
                            "success" => true,
                            "message" => "Utilisateur authentifié.",
                            "user" => array(
                                "id" => $row['id'],
                                "nom" => $row['nom'],
                                "prenom" => $row['prenom']
                            )
                        );
                        echo json_encode($response);
                    } else {
                        // L'utilisateur n'est pas authentifié
                        $response = array("success" => false, "message" => "Identifiants incorrects.");
                        echo json_encode($response);
                    }
                } else {
                    // Paramètres manquants
                    $response = array("success" => false, "message" => "Paramètres manquants pour la connexion.");
                    echo json_encode($response);
                }
            } elseif ($action == 'register') {
                if (isset($data['nom']) && isset($data['prenom']) && isset($data['email']) && isset($data['mot_de_passe'])) {
                    $nom = $data['nom'];
                    $prenom = $data['prenom'];
                    $email = $data['email'];
                    $mot_de_passe = md5($data['mot_de_passe']); // Simple hash MD5 pour l'exemple, envisagez d'utiliser des techniques plus sécurisées

                    // Requête SQL pour vérifier si l'utilisateur existe déjà
                    $check_sql = "SELECT id FROM utilisateurs WHERE email = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("s", $email);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows > 0) {
                        // L'utilisateur existe déjà
                        $response = array("success" => false, "message" => "Cet utilisateur existe déjà.");
                        echo json_encode($response);
                    } else {
                        // Insérer l'utilisateur dans la base de données
                        $insert_sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)";
                        $insert_stmt = $conn->prepare($insert_sql);
                        $insert_stmt->bind_param("ssss", $nom, $prenom, $email, $mot_de_passe);

                        if ($insert_stmt->execute()) {
                            $response = array("success" => true, "message" => "Inscription réussie.");
                            echo json_encode($response);
                        } else {
                            $response = array("success" => false, "message" => "Une erreur est survenue lors de l'inscription.");
                            echo json_encode($response);
                        }
                    }
                } else {
                    // Paramètres manquants
                    $response = array("success" => false, "message" => "Paramètres manquants pour l'inscription.");
                    echo json_encode($response);
                }
            } else {
                // Action non valide
                $response = array("success" => false, "message" => "Action non valide.");
                echo json_encode($response);
            }
        } else {
            // Action manquante
            $response = array("success" => false, "message" => "Action manquante.");
            echo json_encode($response);
        }
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode(array("message" => "Méthode non autorisée."));
        break;
}
