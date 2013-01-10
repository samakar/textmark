<?php

/*
 * Enumerated Data Types
 */
define("SANDBOX", true);
define("TRUE_MYSQL", 1);
define("FALSE_MYSQL", 0);

$domain = (SANDBOX)? "http://localhost/TextMark/" : "http://textmark.net/";
define("DOMAIN",$domain );

abstract class Transaction  {   
    const Pending               = 1;    //pending to be paid
    const Pay                   = 2;    //buyer paid.
    const Deliver               = 3;    //book was delivered.

    const CanceledByBuyer       = 10;   //buyer requests to cancel transaction
    const CanceledBySeller      = 11;   //seller requests to cancel transaction
    const CancelConfirmed       = 12;   //I accept the cancel reason
    const CancelUndo            = 13;   //I undo my  cancel request

    const CanceledForSchedule   = 21;   //because we could not agree on delivery time and place
    const CanceledForDelivery   = 22;   //because the delivery did not happen as scheduled
    const CanceledForQuality    = 23;   //because the quality was not as expected
    const CanceledForPayment    = 24;   //because buyer did not pay
    const CanceledForOther      = 25;   //because of another reason
}

abstract class BookStatus  {   
    const None              = 0;
    const Alert             = 1;    //taker wants to buy/rent , giver wants to sell/rentout
    const Inform            = 2;    //buyer informed about availability
    const Pending           = 3;    //pending to be paid
    const Lock              = 4;    //waiting for delivery
    const CancelByBuyer     = 5;
    const CancelBySeller    = 6;
    //                      = 7;    //reserved for future use
    const Rent              = 8;
    const RentOut           = 9;
    const Delivered         = 21;
    const Deleted           = 22;
}

abstract class BookTrade  {   
    const Sale      = 0;
    const Rental    = 1;
}

abstract class Tag  {   
    const SellerMessage     = 'SellerMessage';
    const BookList          = 'BookList';
    const BuyerMessage      = 'BuyerMessage';
    const BuyerConfirmLink  = 'BuyerConfirmLink';
    const TradersNumber     = "TradersNumber";
    const TradeStatus       = 'TradeStatus';
    const NumberOfTradeIn   = 'NumberOfTradeIn';
    const NumberOfUsed      = 'NumberOfUsed';
    const WishList          = 'WishList';
    const Confirmation      = 'Confirmation';
    const SellerNickname    = 'SellerNickname';
    const SellerPhone       = 'SellerPhone';
    const SellerEmail       = 'SellerEmail';
    const Nickname          = 'Nickname';
    const CellPhone         = 'CellPhone';
    const UsedPrice         = 'UsedPrice';
    const TradeInValue      = 'TradeInValue';
    const PriceAfterTradeIn = 'PriceAfterTradeIn';
    const YouSave           = 'YouSave';
    const CancelCode        = 'CancelCode';
    const Payment           = 'Payment';
    const TradeId           = 'TradeId';
    const Rental            = 'Rental';
    const TextmarkUsedPrice = 'TextmarkUsedPrice';
    const TextmarkRentPrice = 'TextmarkRentPrice';
    const TextmarkTradeInValue = 'TextmarkTradeInValue';
    const TextmarkRentOutValue = 'TextmarkRentOutValue';
}

abstract class Offer  {   
    const Buyback     = 'BUYBACK';  //student wants to sell his book to TextMark
    const Used        = 'USED';     //student wants to buy a used book from TextMark
    const Rent        = 'RENT';     //student wants to rent a book from TextMark
    const RentOut     = 'RENTOUT';     //student wants to rent out his book to TextMark
}

abstract class Method  {   
    const None     = 0;  //book keeping
    const PayPal   = 1;  //PayPal
}

abstract class Email  {   
    const Student       = 'student@textmark.net';   //receives 'contact us' messages
    const Service       = 'service@textmark.net';   //sends trade related info
    const Archive       = 'archive@textmark.net';   //receives a copy of IPN, daily book updates,..
    const Admin         = 'admin@textmark.net';     //receives a copy of error messages
    const Password      = 'sama1351';               //password for Service
}


abstract class UserRole  {   
    const Student   = 0;
    const Admin     = 1;
    const Marketer  = 2;
}

?>