<!-- I AM ROUTER -->
<?php
require('controller/controller.php');

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'editRoom' && !empty($_GET['room-id'])) {
        if (
            isset($_POST['room-id']) &&
            isset($_POST['title']) &&
            isset($_POST['description']) &&
            isset($_POST['country']) &&
            isset($_POST['city']) &&
            isset($_POST['address']) &&
            isset($_POST['zip']) &&
            isset($_POST['capacity']) &&
            isset($_POST['category'])
        ) {
            saveRoom();
        } else {
            showRoom($_GET['room-id']);
        }
    } else if ($_GET['action'] == 'deleteRoom' && !empty($_GET['room-id'])) {
        getDeleteRoom();
    } else if ($_GET['action'] == 'listRooms') {
        listRooms();
    } else if ($_GET['action'] == 'signup') {
        if (isset($_POST['pseudo']) &&
            isset($_POST['mdp']) &&
            isset($_POST['prenom']) &&
            isset($_POST['nom']) &&
            isset($_POST['email']) &&
            isset($_POST['civilite'])) {
            doSignUp();
        } else {
            getSignUp();
        }
    } else if ($_GET['action'] == 'login') {
        getLogin();
    } else if ($_GET['action'] == 'doLogin') {
        if(
            isset($_POST['pseudo']) &&
            isset($_POST['mdp'])
        ) {
            doLogin();
        }
    } else if ($_GET['action'] == 'loggedIn') {
        listProductsIndex();
     } else if ($_GET['action'] == 'editUser' && !empty($_GET['user-id'])) {
            if (
                isset($_POST['id_membre']) &&
                isset($_POST['pseudo']) &&
                isset($_POST['mdp']) &&
                isset($_POST['nom']) &&
                isset($_POST['prenom']) &&
                isset($_POST['email']) &&
                isset($_POST['civilite']) &&
                isset($_POST['statut']) 
            ) {
                createUser();
            } else {
                showUser($_GET['user-id']);
            }
    } else if ($_GET['action'] == 'deleteUser' && !empty($_GET['user-id'])) {
        getDeleteUser();
    } else if ($_GET['action'] == 'listUsers') {
        listUsers();


    } else if ($_GET['action'] == 'searchProducts') {
        if (isset($_POST['category']) &&
        isset($_POST['city']) &&
        isset($_POST['capacity']) &&
        isset($_POST['price']) &&
        isset($_POST['arrival']) &&
        isset($_POST['departure'])){
            searchProducts();
        }
    } else if ($_GET['action'] == 'deconnexion') {
        if (!isset($_SESSION)){
            session_start();
        }
        if (isset($_SESSION)) {
            session_destroy();
            header("Location: " . $_SERVER['PHP_SELF']);
        }
        getLogin();
    } else if ($_GET['action'] == 'listOrders') {
        showOrders();
    } else if ($_GET['action'] == 'deleteOrder') {
   
    } else if ($_GET['action'] == 'noticeList') {
        showRates();
    } else if ($_GET['action'] == 'deleteNotice') {
    
    } else {
        getLogin();
    }
} else {
    getLogin();
}
