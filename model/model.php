<?php
session_start();
define('SERVER_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('SITE_ROOT', SERVER_ROOT . '/PHP ifocop/PHP/switch/');

function dbConnect()
{
    $host_db = 'mysql:host=localhost;dbname=projet_switch';
    $login = 'root';
    $password = '';
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    );
    return new PDO($host_db, $login, $password, $options);
}

function user_is_connected()
{
    $msg = '';
    if (!empty($_SESSION['membre'])) {
        return true;
    }
    return false;
}

function user_is_admin()
{
    $msg = '';
    if (user_is_connected() && $_SESSION['membre']['statut'] == 2) {
        return true;
    } else {
        return false;
    }
}

/////////////////////////////////////////////////////////////////////// ROOMS
function getAllRooms()
{
    $msg = '';
    $pdo = dbConnect();

    return $pdo->query("SELECT * FROM salle");
}

function getRoomForUpdate($room_id)
{
    $msg = '';
    $pdo = dbConnect();

    $current_room = $pdo->prepare("SELECT * FROM salle WHERE id_salle = :roomId");
    $current_room->bindparam(":roomId", $room_id, PDO::PARAM_INT);
    $current_room->execute();

    if ($current_room->rowCount() > 0) {
        return $current_room->fetch(PDO::FETCH_ASSOC);
    }
}

function deleteRoom()
{
    $msg = '';
    $pdo = dbConnect();

    $del = $pdo->prepare("DELETE FROM salle WHERE id_salle = :roomId");
    $del->bindParam(":roomId", $_GET['room-id'], PDO::PARAM_INT);
    $del->execute();
}

function saveOrUpdateRoom()
{
    $msg = '';
    $pdo = dbConnect();

    $room_id = trim($_GET['room-id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $country = trim($_POST['country']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);
    $zip = trim($_POST['zip']);
    $capacity = trim($_POST['capacity']);
    $category = trim($_POST['category']);
    if (!empty($_POST['current-img'])) {
        $db_img = $_POST['current-img'];
    }
    if (empty($zip) || !is_numeric($zip)) {
        $msg = '<div class="alert alert-danger mt-3">Attention, le code postal est obligatoire et doit être numérique.</div>';
    }

    $ref_check = $pdo->prepare("SELECT * FROM salle WHERE id_salle = :roomId");
    $ref_check->bindParam(':roomId', $room_id, PDO::PARAM_INT);
    $ref_check->execute();

    if ($ref_check->rowCount() > 0 && empty($room_id)) {
        $msg = '<div class="alert alert-danger mt-3">Attention, référence indisponible car déjà attribuée.</div>';
    } else {

        if (!empty($_FILES['img']['name'])) {

            $extension = strrchr($_FILES['img']['name'], '.');
            $extension = strtolower(substr($extension, 1));
            $valid_extensions = array('png', 'gif', 'jpg', 'jpeg');
            $check_extension = in_array($extension, $valid_extensions);

            if ($check_extension) {
                $img_name = $_FILES['img']['name'];
                $db_img = $img_name;
                $img_file = 'img/' . $img_name;
                copy($_FILES['img']['tmp_name'], $img_file);
            } else {
                $msg = '<div class="alert alert-danger mt-3">Attention, le format description de la photo est invalide, extensions autorisées : jpg, jpeg, png, gif.</div>';
            }
        }
    }

    if (empty($msg)) {
        if (!empty($room_id)) {
            $save = $pdo->prepare("UPDATE salle SET titre = :title, photo = :img, description = :description, pays = :country, ville = :city, adresse = :address, cp = :zip, capacite = :capacity, categorie = :category WHERE id_salle = :roomId");
            $save->bindParam(":roomId", $room_id, PDO::PARAM_INT);
        } else {
            $save = $pdo->prepare("INSERT INTO salle
    (titre, categorie, description, photo, pays, ville, adresse, cp, capacite)
    VALUES (:title, :category, :description, :img, :country, :city, :address, :zip, :capacity)");
        }

        $save->bindParam(":title", $title, PDO::PARAM_STR);
        $save->bindParam(":category", $category, PDO::PARAM_STR);
        $save->bindParam(":description", $description, PDO::PARAM_STR);
        $save->bindParam(":img", $db_img, PDO::PARAM_STR);
        $save->bindParam(":country", $country, PDO::PARAM_STR);
        $save->bindParam(":city", $city, PDO::PARAM_STR);
        $save->bindParam(":address", $address, PDO::PARAM_STR);
        $save->bindParam(":zip", $zip, PDO::PARAM_INT);
        $save->bindParam(":capacity", $capacity, PDO::PARAM_INT);
        $save->execute();
    } else {
        return $msg;
    }
}

/////////////////////////////////////////////////////////////////////// ROOMS

/////////////////////////////////////////////////////////////////////// USERS
function getAllUsers()
{
    $msg = '';
    $pdo = dbConnect();

    return $pdo->query('SELECT * FROM membre');
}

function deleteUser()
{
    $msg = '';
    $pdo = dbConnect();

    $del = $pdo->prepare("DELETE FROM membre WHERE id_membre = :userId");
    $del->bindParam(":userId", $_GET['user-id'], PDO::PARAM_INT);
    $del->execute();
}

function getUserForUpdate($user_id)
{
    $msg = '';
    $pdo = dbConnect();

    $current_user = $pdo->prepare("SELECT * FROM membre WHERE id_membre = :userId");
    $current_user->bindparam(":userId", $user_id, PDO::PARAM_INT);
    $current_user->execute();

    if ($current_user->rowCount() > 0) {
        return $current_user->fetch(PDO::FETCH_ASSOC);
    }
}

function saveUserByUser()
{
    $msg = '';
    $pdo = dbConnect();

    $pseudo = trim($_POST['pseudo']);
    $mdp = trim($_POST['mdp']);
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $civilite = trim($_POST['civilite']);

    $verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $pseudo);

    if (!$verif_caractere && !empty($pseudo)) {
        $msg = '<div class="alert alert-danger mt-3">Pseudo invalide, caractères autorisés : a-z et de 0-9</div>';
    }

    if (iconv_strlen($pseudo) < 4 || iconv_strlen($pseudo) > 14) {
        $msg = '<div class="alert alert-danger mt-3">Pseudo invalide, le pseudo doit avoir entre 4 et 14 caractères inclus</div>';
    }

    $verif_pseudo = $pdo->prepare("SELECT * FROM membre WHERE pseudo = :pseudo");
    $verif_pseudo->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
    $verif_pseudo->execute();
    $verif_email = $pdo->prepare("SELECT * FROM membre WHERE email = :email");
    $verif_email->bindParam(":email", $email, PDO::PARAM_STR);
    $verif_email->execute();

    if ($verif_pseudo->rowCount() > 0 || $verif_email->rowCount() > 0) {
        $msg = '<div class="alert alert-danger mt-3">Pseudo ou email indisponible !</div>';
    } else if (empty($msg)) {
        $mdp = password_hash($mdp, PASSWORD_DEFAULT);

        $save = $pdo->prepare("INSERT INTO membre 
            (pseudo, mdp, nom, prenom, email, civilite, statut,date_enregistrement)
             VALUES (:pseudo, :mdp, :nom, :prenom, :email, :civilite, 1,NOW())");

        $save->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $save->bindParam(':mdp', $mdp, PDO::PARAM_STR);
        $save->bindParam(':nom', $nom, PDO::PARAM_STR);
        $save->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $save->bindParam(':email', $email, PDO::PARAM_STR);
        $save->bindParam(':civilite', $civilite, PDO::PARAM_STR);
        $save->execute();

        $_SESSION['membre'] = array();
        $_SESSION['membre']['pseudo'] = $pseudo;
        $_SESSION['membre']['nom'] = $nom;
        $_SESSION['membre']['prenom'] = $prenom;
        $_SESSION['membre']['email'] = $email;
        $_SESSION['membre']['statut'] = 1;
    }
    return $msg;
}

function saveUserByAdmin()
{
    $msg = '';
    $pdo = dbConnect();

    $user_id = trim($_POST['id_membre']);
    $pseudo = trim($_POST['pseudo']);
    $mdp = trim($_POST['mdp']);
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $civilite = trim($_POST['civilite']);
    $statut = trim($_POST['statut']);

    $verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $pseudo);

    if (!$verif_caractere && !empty($pseudo)) {
        $msg = '<div class="alert alert-danger mt-3">Pseudo invalide, caractères autorisés : a-z et de 0-9</div>';
    }

    if (iconv_strlen($pseudo) < 4 || iconv_strlen($pseudo) > 14) {
        $msg = '<div class="alert alert-danger mt-3">Pseudo invalide, le pseudo doit avoir entre 4 et 14 caractères inclus</div>';
    }

    if (!empty($user_id)) {
        $save = $pdo->prepare("UPDATE membre SET id_membre = :userId, pseudo = :pseudo, mdp = :mdp, nom = :nom, prenom = :prenom, email = :email, civilite = :civilite, statut = :statut WHERE id_membre = :userId");
        $save->bindParam(":userId", $user_id, PDO::PARAM_INT);
    } else {
        $verif_pseudo = $pdo->prepare("SELECT * FROM membre WHERE pseudo = :pseudo");
        $verif_pseudo->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
        $verif_pseudo->execute();
        $verif_email = $pdo->prepare("SELECT * FROM membre WHERE email = :email");
        $verif_email->bindParam(":email", $email, PDO::PARAM_STR);
        $verif_email->execute();

        if ($verif_pseudo->rowCount() > 0 || $verif_email->rowCount() > 0) {
            $msg = '<div class="alert alert-danger mt-3">Pseudo ou email indisponible !</div>';
        } else {
            $save = $pdo->prepare("INSERT INTO membre 
            (pseudo, mdp, nom, prenom, email, civilite, statut, date_enregistrement)
            VALUES (:pseudo, :mdp, :nom, :prenom, :email, :civilite, :statut, NOW())");
        }
    }
    if (empty($msg)) {
        $mdp = password_hash($mdp, PASSWORD_DEFAULT);
        $save->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $save->bindParam(':mdp', $mdp, PDO::PARAM_STR);
        $save->bindParam(':nom', $nom, PDO::PARAM_STR);
        $save->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $save->bindParam(':email', $email, PDO::PARAM_STR);
        $save->bindParam(':civilite', $civilite, PDO::PARAM_STR);
        $save->bindParam(':statut', $statut, PDO::PARAM_INT);
        $save->execute();
    }
    return $msg;
}

/////////////////////////////////////////////////////////////////////// USERS

/////////////////////////////////////////////////////////////////////// PRODUCTS
function getAllProductsIndex()
{
    $msg = '';
    $pdo = dbConnect();
    return $pdo->query('SELECT id_produit, prix, date_arrivee, date_depart, titre, description, photo FROM produit, salle WHERE produit.id_salle = salle.id_salle AND etat = \'libre\' AND date_arrivee >= NOW()');
}

function getAllProducts()
{
    $msg = '';
    $pdo = dbConnect();
    return $pdo->query(
        'SELECT id_produit, date_arrivee, date_depart, produit.id_salle, salle.titre, salle.capacite, salle.adresse, salle.cp, salle.ville, salle.photo, salle.description, prix, etat 
FROM produit, salle 
WHERE produit.id_salle = salle.id_salle'
    );
}

function getProductForUpdate($product_id)
{
    $msg = '';
    $pdo = dbConnect();

    $current_product = $pdo->prepare('SELECT * FROM produit WHERE id_produit = :productId');
    $current_product->bindParam(":productId", $product_id, PDO::PARAM_INT);
    $current_product->execute();

    if ($current_product->rowCount() > 0) {
        return $current_product->fetch(PDO::FETCH_ASSOC);
    }
}

function getProduct($product_id)
{
    $msg = '';
    $pdo = dbConnect();
    $get = $pdo->prepare('
        SELECT titre, (SELECT ROUND(AVG(avis.note), 2) FROM avis WHERE avis.id_salle = produit.id_salle) AS note, photo, description, date_arrivee, date_depart, capacite, categorie, prix
        FROM salle, produit
        WHERE produit.id_salle = salle.id_salle
        AND produit.id_produit = :productId
        '
    );
    $get->bindParam(":productId", $product_id, PDO::PARAM_INT);
    $get->execute();
    if ($get->rowCount() > 0) {
        return $get->fetch(PDO::FETCH_ASSOC);
    }
}

function getSearchedProducts()
{
    $msg = '';
    $pdo = dbConnect();
    $categorie = trim($_POST['category']);
    $ville = trim($_POST['city']);
    $capacite = trim($_POST['capacity']);
    $prix = trim($_POST['price']);
    $date_arrivee = trim($_POST['arrival']);
    $date_depart = trim($_POST['departure']);

    $result_products = $pdo->prepare(
        '
SELECT produit.id_produit, produit.prix, produit.date_arrivee, produit.date_depart, salle.titre, salle.description, salle.photo
FROM produit, salle
WHERE produit.id_salle = salle.id_salle
AND salle.categorie = :categorie
AND salle.ville = :ville
AND salle.capacite >= :capacite
AND produit.prix <= :prix
AND produit.date_arrivee >= NOW()
AND produit.etat = \'libre\'
-- Dates not working :(
-- AND UNIX_TIMESTAMP(produit.date_arrivee) >= UNIX_TIMESTAMP(:date_arrivee)
-- AND UNIX_TIMESTAMP(produit.date_depart) <= UNIX_TIMESTAMP(:date_depart)
');

    $result_products->bindParam(":categorie", intval($categorie), PDO::PARAM_INT);
    $result_products->bindParam(":ville", $ville, PDO::PARAM_STR);
    $result_products->bindParam(":capacite", $capacite, PDO::PARAM_INT);
    $result_products->bindParam(":prix", $prix, PDO::PARAM_INT);
//  Dates not working :(
//  $result_products->bindParam(":date_arrivee", $date_arrivee, PDO::PARAM_STR);
//  $result_products->bindParam(":date_depart", $date_depart, PDO::PARAM_STR);
    $result_products->execute();

    if ($result_products->rowCount() > 0) {
        return $result_products;
    }
}

function deleteProduct()
{
    $msg = '';
    $pdo = dbConnect();

    $del = $pdo->prepare("DELETE FROM produit WHERE id_produit = :productId");
    $del->bindParam(":productId", $_GET['product-id'], PDO::PARAM_INT);
    $del->execute();
}

function saveOrUpdateProduct()
{
    $msg = '';
    $pdo = dbConnect();

    $product_id = trim($_GET['product-id']);
    $room_id = trim($_POST['room']);
    $arrival = trim($_POST['arrival']);
    $departure = trim($_POST['departure']);
    $price = trim($_POST['price']);

    if (!empty($product_id)) {
        $save = $pdo->prepare("UPDATE produit SET id_salle = :roomId, date_arrivee = :arrival, date_depart = :departure, prix = :price WHERE id_produit = :productId");
        $save->bindParam(":productId", $product_id, PDO::PARAM_INT);
    } else {
        $save = $pdo->prepare("INSERT INTO produit
    (id_salle, date_arrivee, date_depart, prix)
    VALUES (:roomId, :arrival, :departure, :price)");
    }

    $save->bindParam(":roomId", $room_id, PDO::PARAM_STR);
    $save->bindParam(":arrival", $arrival, PDO::PARAM_STR);
    $save->bindParam(":departure", $departure, PDO::PARAM_STR);
    $save->bindParam(":price", $price, PDO::PARAM_STR);
    $save->execute();

    return $msg;

}

/////////////////////////////////////////////////////////////////////// PRODUCTS

/////////////////////////////////////////////////////////////////////// LOG&SIGN
function verifyLogin()
{
    $msg = '';

    $pdo = dbConnect();
    $pseudo = trim($_POST['pseudo']);
    $mdp = trim($_POST['mdp']);

    $verif_connexion = $pdo->prepare("SELECT * FROM membre WHERE pseudo = :pseudo");
    $verif_connexion->bindParam(":pseudo", $pseudo, PDO::PARAM_STR);
    $verif_connexion->execute();

    if ($verif_connexion->rowCount() > 0) {
        $infos = $verif_connexion->fetch(PDO::FETCH_ASSOC);

        if (password_verify($mdp, $infos['mdp'])) {

            $_SESSION['membre'] = array();

            $_SESSION['membre']['id_membre'] = $infos['id_membre'];
            $_SESSION['membre']['pseudo'] = $infos['pseudo'];
            $_SESSION['membre']['nom'] = $infos['nom'];
            $_SESSION['membre']['prenom'] = $infos['prenom'];
            $_SESSION['membre']['email'] = $infos['email'];
            $_SESSION['membre']['statut'] = $infos['statut'];

        } else {
            $msg = '<div class="alert alert-danger mt-3">Erreur sur le pseudo et / ou le mot de passe !</div>';
        }
    } else {
        $msg = '<div class="alert alert-danger mt-3">Erreur sur le pseudo et / ou le mot de passe !</div>';
    }
    return $msg;
}

/////////////////////////////////////////////////////////////////////// LOG&SIGN

/////////////////////////////////////////////////////////////////////// ORDERS
function getAllOrders()
{
    $msg = '';
    $pdo = dbConnect();
    return $pdo->query(
        'SELECT commande.id_commande, commande.id_membre, membre.email, commande.id_produit, salle.titre, produit.date_arrivee, produit.date_depart, produit.prix, commande.date_enregistrement 
FROM commande, produit, membre, salle 
WHERE commande.id_membre = membre.id_membre 
  AND commande.id_produit = produit.id_produit 
  AND produit.id_salle = salle.id_salle'
    );
}

function deleteOrder()
{
    $msg = '';
    $pdo = dbConnect();

    $del = $pdo->prepare("DELETE FROM commande WHERE id_commande = :commandeId");
    $del->bindParam(":commandeId", $_GET['order-id'], PDO::PARAM_INT);
    $del->execute();
}

/////////////////////////////////////////////////////////////////////// ORDERS

/////////////////////////////////////////////////////////////////////// RATINGS
function getAllRatings()
{
    $msg = '';
    $pdo = dbConnect();
    return $pdo->query('SELECT avis.id_avis, avis.id_membre, membre.email, avis.id_salle, salle.titre, avis.commentaire, avis.note, avis.date_enregistrement  
    FROM avis, membre, salle 
    WHERE avis.id_membre = membre.id_membre 
    AND avis.id_salle = salle.id_salle');
}

/////////////////////////////////////////////////////////////////////// RATINGS

/////////////////////////////////////////////////////////////////////// STATS (TOP5s)
function getRoomRatingStats()
{
    $msg = '';
    $pdo = dbConnect();
    return $pdo->query('
    SELECT salle.id_salle, salle.titre, (SELECT ROUND(AVG(avis.note), 2) FROM avis WHERE avis.id_salle = salle.id_salle) AS rating 
    FROM salle 
    ORDER BY rating DESC 
    LIMIT 5
    ');

}

function getRoomTimesOrderedStats()
{
    $msg = '';
    $pdo = dbConnect();
    return $pdo->query('
    SELECT salle.id_salle, salle.titre, (SELECT COUNT(commande.id_commande) FROM commande, produit WHERE commande.id_produit = produit.id_produit AND produit.id_salle = salle.id_salle) AS times_ordered 
    FROM salle 
    ORDER BY times_ordered DESC 
    LIMIT 5
    ');
}

function getUserPurchasesStats()
{
    $msg = '';
    $pdo = dbConnect();
    return $pdo->query('
    SELECT membre.id_membre, membre.pseudo, (SELECT COUNT(commande.id_commande) FROM commande WHERE commande.id_membre = membre.id_membre) AS times_purchased 
    FROM membre
    WHERE membre.statut = 1
    ORDER BY times_purchased DESC
    LIMIT 5
    ');
}

function getUserValueStats()
{
    $msg = '';
    $pdo = dbConnect();
    return $pdo->query('
    SELECT membre.id_membre, membre.pseudo, (SELECT SUM(produit.prix) FROM commande, produit WHERE commande.id_membre = membre.id_membre AND commande.id_produit = produit.id_produit) AS amount_spent
    FROM membre 
    WHERE membre.statut = 1
    ORDER BY amount_spent DESC
    LIMIT 5
    ');
}

/////////////////////////////////////////////////////////////////////// STATS (TOP5s)

/////////////////////////////////////////////////////////////////////// PROFILE
function getProfileDetails($user_id)
{
    $pdo = dbConnect();
    $get = $pdo->prepare(
        'SELECT commande.id_commande, commande.id_produit, salle.titre, produit.date_arrivee, produit.date_depart, produit.prix, commande.date_enregistrement 
    FROM commande, produit, salle 
    WHERE commande.id_membre = :userId 
    AND commande.id_produit = produit.id_produit 
    AND produit.id_salle = salle.id_salle'
    );
    $get->bindParam(":userId", $user_id, PDO::PARAM_INT);
    $get->execute();
    if ($get->rowCount() > 0) {
        return $get;
    }

}
/////////////////////////////////////////////////////////////////////// PROFILE