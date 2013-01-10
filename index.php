<?php
require_once 'controller/BooksController.php';
require_once 'controller/UsersController.php';
require_once 'controller/TradeController.php';
require_once 'controller/AdminController.php';

$userController = new UsersController();
$userController->handleRequest();

$bookController = new BooksController();
$bookController->handleRequest();

$tradeController = new TradeController();
$tradeController->handleRequest();

$adminController = new AdminController();
$adminController->handleRequest();
?>