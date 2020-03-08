<!-- I AM CONTROLLER -->
<?php
require('model/model.php');

function showOrders(){
    $orders_list = getAllOrders();
    require('view/back-office/ordersView.php');

}

function listProductsIndex()
{
    $products_list = getAllProducts();
    require('indexView.php');
}
function listRooms()
{
    $rooms_list = getAllRooms();
    require('view/back-office/roomsView.php');
}

function showRoom($room_id)
{
    $current_room = getRoomForUpdate($room_id);
    $rooms_list = getAllRooms();
    require('view/back-office/roomsView.php');
}

function getDeleteRoom()
{
    deleteRoom();
    $rooms_list = getAllRooms();
    require('view/back-office/roomsView.php');
}

function saveRoom() {

    $msg = saveOrUpdateRoom();

    $rooms_list = getAllRooms();
    require('view/back-office/roomsView.php');
}

function getSignUp() {
    if (isset($_SESSION)) {
        session_destroy();
    }    require('view/front-office/signupView.php');
}

function doSignUp() {

    $msg = saveUser();
    if(!$msg){
        $products_list = getAllProducts();
        require('indexView.php');
    } else {
        $_POST='';
        require('view/front-office/signupView.php');
    }
}

function getLogin() {
    require('view/front-office/loginView.php');
}

function doLogin() {
    $msg = verifyLogin();
    if(!$msg){
        $products_list = getAllProducts();
        require('indexView.php');
    } else {
        $_POST='';
        require('view/front-office/loginView.php');
    }
}
//function getIduser(){
//      header('view/front-office/profileView.php');
//
//}
//function showProfile(){
//
//    require('view/front-office/profileView.php');
//}

function searchProducts(){
    $products_list = getSearchedProducts();
    require('indexView.php');
}

function getUsers(){
    $users_list = getAllUsers();
    require('view/back-office/usersView.php');
}

function getDeleteOrder()
{
    deleteOrder();
    $orders_list = getAllOrders();
    require('view/back-office/ordersView.php');
}

