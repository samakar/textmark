<?php

require_once 'model/BooksService.php';
require_once 'Controller.php';

class BooksController extends Controller{
    
    private $booksService = NULL;
    private $id_user = NULL;       

    public function __construct() {
        $this->booksService = new BooksService();
        $this->id_user = $_SESSION['user_id']; // set by UserControler during call by index.php
    }
        
    public function handleRequest() {
  
        $op = isset($_GET['op'])?$_GET['op']:'book_find';
        try {
            switch ($op){
                case 'book_find':
                    $this->findBook();
                    break;
                case 'book_add':
                    if ($this->check_privacy()) $this->addBookList();
                    break;
                case 'book_show':
                    $this->showBooklist();
                    break;
                case 'book_remove':
                    if ($this->check_privacy()) $this->removeBooklist();
                    break;
                case 'book_swap':
                    if ($this->check_privacy()) $this->swapBooklist();
                    break;
                case 'book_showcase':
                    $this->showcase();
            }
        } catch ( Exception $e ) {
            // some unknown Exception got through here, use application error page to display it
            $this->inform("Application error", $e->getMessage());
        }
    }
    
    private function findBook() {
      
        if ( isset($_POST['form-submitted']) || isset($_GET['form-submitted'])) {
            
            if (isset($_POST['ISBN'])){
                $term = $_POST['ISBN'];
            }else {
                $term = (isset($_GET['isbn'])) ? $_GET['isbn'] : NULL ;  
            }
            
            try {
                $xml = $this->booksService->search($term, $this->id_user);
            } catch (Exception $e) {
                $xml = new DomDocument;
                $xml->loadXML("<ItemLookupResponse><ISBN>" . $term . 
                        "</ISBN><Error>" . $e->getMessage() . "</Error></ItemLookupResponse>");
            }
        }else {
            $xml = new DomDocument;
            $xml->loadXML("<ItemLookupResponse></ItemLookupResponse>");
        }
       
         $xml = $this->booksService->add_recommendation($xml);
         $this->xml_to_html($xml,"book_find.xsl");
    }    
    
    private function addBookList() {
        
        $wishlist = isset($_GET['wishlist'])?$_GET['wishlist']:NULL;
        $isbn13 = isset($_GET['isbn'])?$_GET['isbn'] : NULL;
        if ( !$wishlist || !$isbn13) throw new Exception('Internal error.');
        try{
            $this->booksService->addBookList( $isbn13, $this->id_user, $wishlist);
            $this->redirect('index.php?op=book_show&wishlist=' . $wishlist);       
        } catch (Exception $e) {
            $this->inform("TextMark Message", $e->getMessage());
        }
    }

    private function removeBookList() {
        
        $wishlist = isset($_GET['wishlist'])?$_GET['wishlist']:NULL;
        $isbn13 = isset($_GET['isbn'])?$_GET['isbn'] : NULL;
        if ( !$wishlist || !$isbn13)  throw new Exception('Internal error.');

        $this->booksService->removeBookList( $isbn13 , $this->id_user , $wishlist);
        $this->redirect('index.php?op=book_show&wishlist=' . $wishlist);       
    }
    
    private function swapBookList() {
        
        $wishlist = isset($_GET['wishlist'])?$_GET['wishlist']:NULL;
        $isbn13 = isset($_GET['isbn'])?$_GET['isbn'] : NULL;
        if ( !$wishlist || !$isbn13)  throw new Exception('Input Data error.');

        $this->booksService->swapBookList( $isbn13 , $this->id_user , $wishlist);
        $this->redirect('index.php?op=book_show&wishlist=' . $wishlist);       
    }

    private function showBooklist() {
        $wishlist = isset($_GET['wishlist'])?$_GET['wishlist']:NULL;
        if ( !$wishlist )  throw new Exception('Input Error.');
        $xml = $this->booksService->getBookList( $this->id_user , $wishlist);   
        $xsl_filename = ($wishlist =='TRUE') ? 'book_wishlist.xsl' : 'book_sell.xsl';
        $this->xml_to_html($xml,$xsl_filename);
    }
      
    private function showcase() {
        $xml = $this->booksService->showcase($_SESSION['college_id']);   
        $this->xml_to_html($xml,"book_showcase.xsl");
    }

    
}    
?>