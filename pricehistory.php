<?php

/*
 * recordes book prices in database when called by CRON
 */

try {

    require_once('model/BooksService.php');
    require_once 'model/Enums.php';

    // for test
    mail(Email::Archive, 'pricehistory.php was called', "received");
    
    // constants that are used in gateways
    define("TRUE_MYSQL", 1);
    define("FALSE_MYSQL", 0);

    $booksService = new BooksService();
    $total_number_of_books = $booksService->record_current_prices();
    
    echo 'pricehistory.php done!', "Price of $total_number_of_books books were recorded.";
    mail(Email::Archive, 'pricehistory.php done', "Price of $total_number_of_books books were recorded.");
    
} catch (Exception $e) {
    echo "pricehistory.php failed: " . $e->getMessage();
    error_log("pricehistory.php failed: " . $e->getMessage());
    // manually investigate errors
    $body = "pricehistory.php failed: \n" . $e->getMessage();
    mail(Email::Admin, 'pricehistory.php Warning', $body);
}

?>