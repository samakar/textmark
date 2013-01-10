<?php

/**
 * Amazon API.
 * 
 * Each service call is transaction script that returns xml.
 *
 */
class AmazonService  extends Service{

    public function getBook($ISBN){
        $private_key = "V9hZqa6c5WvPpUo9iNiMlsErj42DUaWUpa9qim02";
        $public_key = "AKIAJY5JA7AQACECNTQQ";
        $xml = $this->awsRequest("com", array(
            "Operation"=>"ItemLookup",
            "ItemId"=>$ISBN,
            "IdType"=>"EAN",
            "SearchIndex" => "Books",
            "ResponseGroup"=>"Large",
            "Condition"=>"All",
            "MerchantId"=>"Amazon"
            )
            , $public_key, $private_key);

        if ($xml==FALSE){
            throw new Exception('No response from AWS server.');
        }else if ($xml->getElementsByTagName('Errors')->length > 0){
            // $ISBN is validated beforehand so AWS error is not related to invlaid ISBN
            throw new Exception('The requested book was not found.');
        } else {         
           $xml = $this->select_best_item($xml);
           $xml = $this->add_shipping_cost($xml);
           $xml = $this->add_rental_info($xml, $ISBN);
           $xml = $this->add_short_title($xml);
        return $xml;
        }
    } 

    public function getBookInfo($xml, $tag) {
        if ($xml === False){
            return '';
        } else {
            switch ($tag) {
                case "Thumbnail":
                    $query = '//ThumbnailImage/URL';
                    break;
                case "Image":
                    $query = '//MediumImage/URL';
                    break;
                case "NewPrice":
                    $query = '//LowestNewPrice/FormattedPrice';
                    break;
                case "UsedPrice":
                    $query = '//LowestUsedPrice/FormattedPrice';
                    break;
                case "AmazonPrice":
                    $query = '//OfferListing/Price/FormattedPrice';
                    break;
               case "OffersUrl":
                    $query = '//Offers/MoreOffersUrl';
                    break;
               case "ListPrice":
                    $query = '//ListPrice/FormattedPrice';
                    break;
               case "TradeInValue":
                    $query = '//TradeInValue/FormattedPrice';
                    break;
               case "ISBN13":
                    $query = '//ItemAttributes/EAN';
                    break;
               case "Title":
                    $query = '//ItemAttributes/Title';
                    break;
               case "Edition":
                    $query = '//ItemAttributes/Edition';
                    break;
               case "Author":
                    $query = '//ItemAttributes/Author';
                    break;
               case "TradersNumber":
                    $query = '//Textmark/TradersNumber';
                    break;
               case "TextmarkUsedPrice":
                    $query = '//Textmark/UsedPrice';
                    break;
               case "TextmarkTradeInValue":
                    $query = '//Textmark/TradeInValue';
                    break;
               case "TextmarkRentPrice":
                    $query = '//Textmark/RentPrice';
                    break;
               case "TextmarkRentOutValue":
                    $query = '//Textmark/RentOutValue';
                    break;
               case "TextmarkTradeStatus":
                    $query = '//Textmark/TradeStatus';
                    break;
               case "TradeId":
                    $query = '//Textmark/TradeId';
                    break;
               case "Rental":
                    $query = '//Textmark/Rental';
                    break;
               case "WishList":
                    $query = '//Textmark/WishList';
                    break;
               case "CheggRentPrice":
                    $query = '//Chegg/RentPrice';
                    break;
               case "CheggListPrice":
                    $query = '//Chegg/ListPrice';
                    break;
                default:
                    throw new Exception("AmazonService>> Tag was not found : $tag");
            }
            $xpath = new DOMXPath($xml);
            $queryResult = $xpath->query($query);
            if ($queryResult->item(0)==null) 
                return "";
            else 
                return $queryResult->item(0)->textContent;
        }
    }
    
    public function add_textmark_node( $bookXML, $node_name, $node_text ) {
        return $this->add_node($bookXML, 'Textmark', $node_name, $node_text);
    }

    public function update_listprice($bookXML, $newPrice){
        $node1 = $bookXML->getElementsByTagName('ItemAttributes')->item(0);

        $node2 = $bookXML->createElement("ListPrice");
        $node1->appendChild($node2);
        
        $node3 = $bookXML->createElement("Amount");
        $node2->appendChild($node3);      
        $value = $bookXML->createTextNode($newPrice * 100);
        $node3->appendChild($value);
   
        $node3 = $bookXML->createElement("FormattedPrice");
        $node2->appendChild($node3);      
        $value = $bookXML->createTextNode('$' . $newPrice);
        $node3->appendChild($value);

        return $bookXML;
    }

    private function awsRequest($region, $params, $public_key, $private_key) {
        /*
        Copyright (c) 2009 Ulrich Mierendorff

        Permission is hereby granted, free of charge, to any person obtaining a
        copy of this software and associated documentation files (the "Software"),
        to deal in the Software without restriction, including without limitation
        the rights to use, copy, modify, merge, publish, distribute, sublicense,
        and/or sell copies of the Software, and to permit persons to whom the
        Software is furnished to do so, subject to the following conditions:

        The above copyright notice and this permission notice shall be included in
        all copies or substantial portions of the Software.

        THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
        IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
        FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
        THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
        LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
        FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
        DEALINGS IN THE SOFTWARE.
        */

        /*
        Parameters:
            $region - the Amazon(r) region (ca,com,co.uk,de,fr,jp)
            $params - an array of parameters, eg. array("Operation"=>"ItemLookup",
                            "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
            $public_key - your "Access Key ID"
            $private_key - your "Secret Access Key"
        */

        // some paramters
        $method = "GET";
        $host = "webservices.amazon.".$region;
        $uri = "/onca/xml";

        // additional parameters
        $params["AWSAccessKeyId"] = $public_key;
        $params["Service"] = "AWSECommerceService";
        // GMT timestamp
        $params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
        // API version
        $params["Version"] = "2011-08-01";
        $params["AssociateTag"] = "textmark-20";
        // sort the parameters
        ksort($params);

        // create the canonicalized query
        $canonicalized_query = array();
        foreach ($params as $param=>$value)
        {
            $param = str_replace("%7E", "~", rawurlencode($param));
            $value = str_replace("%7E", "~", rawurlencode($value));
            $canonicalized_query[] = $param."=".$value;
        }
        $canonicalized_query = implode("&", $canonicalized_query);

        // create the string to sign
        $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;

        // calculate HMAC with SHA256 and base64-encoding
        $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $private_key, True));

        // encode the signature for the request
        $signature = str_replace("%7E", "~", rawurlencode($signature));

        // create request
        $request = "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
        return $this->getXmlReply($request);
    }

    private function bookrenterRequest($isbn,$developer_key) {
        /*
        https://tikinet-bookrenterapi.pbworks.com/w/page/33968687/xml%20fetch-book-info
        */

        $request = "http://www.bookrenter.com/api/fetch_book_info?developer_key=".$developer_key."&version=2011-02-01&isbn=".$isbn;
        return $this->getXmlReply($request);
    }

    private function cheggRequest($isbn) {
        $request = "http://www.chegg.com/search/$isbn";
        if (SANDBOX) {
            $response = @file_get_contents($request);
        } else {
            $response = $this->file_get_contents_curl($request);
        }
        return $response; //html in text format
    }
    
    private function cheggRequest2($isbn, $key, $password) {       
        $request = "http://api.chegg.com/rent.svc?KEY=$key&PW=$password&R=XML&V=2.0&isbn=$isbn&with_pids=1";
        return $this->getXmlReply($request);
    }

    private function getXmlReply($request) {
        if (SANDBOX) {
            $response = @file_get_contents($request);
        } else {
            $response = $this->file_get_contents_curl($request);
        }

        if ($response === False) {
            return False;
        }else{
            // Gets rid of all namespace definitions 
            $xml_string = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $response);
            // Gets rid of all namespace references
            $xml_string = preg_replace('/[a-zA-Z]+:([a-zA-Z]+[=>])/', '$1', $xml_string);

            $xml = new DomDocument;
            if ($xml->loadXML($xml_string)) {
                return $xml;
            }else{
                return False;
            }     
        }        
    }

    private function add_node( $bookXML, $root_name, $node_name, $node_text ) {
        // get Textmark node. if it doesn't exist, create it under 'Item'.
        $parent_list = $bookXML->getElementsByTagName($root_name);
        if ($parent_list->length==0){
            $doc_root = $bookXML->getElementsByTagName('Item')->item(0);
            $parent = $bookXML->createElement($root_name);
            $doc_root->appendChild($parent);
        }  else {
          $parent = $parent_list->item(0);          
        }   
        
        // add nodes under Textmark node
        $child = $bookXML->createElement($node_name);
        $parent->appendChild($child);
        $value = $bookXML->createTextNode($node_text);
        $child->appendChild($value);
        return $bookXML;
    }

    private function add_shipping_cost($bookXML){
            $xpath = new DOMXPath($bookXML);
            //UsedPrice
            $node = $xpath->query('//LowestUsedPrice/FormattedPrice')->item(0);
            $price = str_replace('$', '', $node->textContent);
            if ($price!='') {
                $price += 3.99;
                $node->nodeValue = "$" . $price;
                
                $node = $xpath->query('//LowestUsedPrice/Amount')->item(0);
                $node->nodeValue = $node->textContent + 399;             
            }
            // NewPrice
            $node = $xpath->query('//LowestNewPrice/FormattedPrice')->item(0);
            if (!is_null($node)){
                $price = str_replace('$', '', $node->textContent);
                if ($price!='') {
                    $price += 3.99;
                    $node->nodeValue = "$" . $price;

                    $node = $xpath->query('//LowestNewPrice/Amount')->item(0);
                    $node->nodeValue = $node->textContent + 399;             
                }
            }
            // AmazonPrice
            $node = $xpath->query('//OfferListing/Price/FormattedPrice')->item(0);
            if (!is_null($node)){
                $price = str_replace('$', '', $node->textContent);
                if ($price!='' && $price<=25) {
                    $price += 3.99;
                    $node->nodeValue = "$" . $price;

                    $node = $xpath->query('//OfferListing/Price/Amount')->item(0);
                    $node->nodeValue = $node->textContent + 399;             
                }
            }
            return $bookXML;
    }
    
    private function add_rental_info($bookXML, $isbn){
        /*
        // BookRenter
        $url = '';
        $price = '-';
        $xml= $this->bookrenterRequest($isbn, '4oj2DuG9AsJuLjUd6VIINr5vPmclW8df');
        if ($xml!=false){
            $xpath = new DOMXPath($xml);
            $node = $xpath->query("//availability")->item(0);
            $availability=$node->textContent;
            if ($availability=='In Stock'){
                $node = $xpath->query("//rental_price[@days='125']")->item(0);
                $price = $node->textContent;
                $node = $xpath->query("//book_url")->item(0);
                $url = $node->textContent;
            }
        }
        $bookXML = $this->add_node($bookXML, 'Bookrenter', 'RentPrice', $price);
        $bookXML = $this->add_node($bookXML, 'Bookrenter', 'RentURL', $url);
        */
 
        // Chegg
        //$url = '';
        //$price = '-';
        $xml= $this->cheggRequest2($isbn, 'd25865c52510f397c97fcc3fd33a5425','7193600');
        if ($xml!=false){
            $listPrice = $xml->getElementsByTagName("ListPrice")->item(0)->nodeValue;
            /*
            $xpath = new DOMXPath($xml);
            $availability = $xpath->query("//Renting")->item(0)->textContent;
            
            if ($availability=='1'){
                //die('chegg');
                $termlist = $xml->getElementsByTagName( "Term" ); 
                foreach($termlist as $row){
                    if ($row->hasChildNodes()){
                        $name = $row->getElementsByTagName("Name")->item(0)->nodeValue;
                        If ($name=='Semester Rental'){
                            $price = $row->getElementsByTagName("Price")->item(0)->nodeValue;
                        }
                    }
                }                
                $url = "http://www.chegg.com/search/$isbn"; //correct later
            }
             */
        }

        //$bookXML = $this->add_node($bookXML, 'Chegg', 'RentPrice', $price);
        //$bookXML = $this->add_node($bookXML, 'Chegg', 'RentURL', $url);
        $bookXML = $this->add_node($bookXML, 'Chegg', 'ListPrice', $listPrice);
        
        // Chegg
        
        $url = '';
        $price = '-';
        $html= $this->cheggRequest($isbn);
        if ($html!=''){
            $price = $this->get_string_between($html, '"price":', ',"pricingType"');
            $url = "http://www.chegg.com/search/$isbn";
        }
        $bookXML = $this->add_node($bookXML, 'Chegg', 'RentPrice', $price);
        $bookXML = $this->add_node($bookXML, 'Chegg', 'RentURL', $url);
        return $bookXML;    
    }

    private function add_short_title($bookXML){
        $title = $this->omit_brackets($this->getBookInfo($bookXML,'Title'));
        $bookXML = $this->add_textmark_node( $bookXML, 'Title', $title );
        return $bookXML;
    }

    private function select_best_item($bookXML){
            //sometimes Amazon returns more than one item for an ISBN 
            //because a user has registered a book with a differ name and the same ISBN. 
            //select the item with the highest SalesRank and delete the rest
            $xpath = new DOMXPath($bookXML);
            $item_list = $xpath->query('//Item');
            if ($item_list->length > 1){
                //find the item with highest SalesRank
                $highest_rank = 0;
                foreach($item_list as $item){
                 $new_rank = intval($item->getElementsByTagName('SalesRank')->item(0)->textContent);
                 if ($new_rank>$highest_rank) $highest_rank=$new_rank;
                }
                
                //delete items with a lower rank
                foreach($item_list as $item){
                 $new_rank = intval($item->getElementsByTagName('SalesRank')->item(0)->textContent);
                 if ($new_rank!=$highest_rank) $item->parentNode->removeChild($item);
                }
            }
            
            return $bookXML;
    }
    
    private function file_get_contents_curl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
}

    private function get_string_between($string, $start, $end){
        //http://www.justin-cook.com/wp/2006/03/31/php-parse-a-string-between-two-strings/
	$string = " ".$string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);
	$len = strpos($string,$end,$ini) - $ini;
	return substr($string,$ini,$len);
    }
    
  
}
?>