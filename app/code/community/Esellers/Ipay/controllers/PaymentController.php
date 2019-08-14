<?php
class Esellers_Ipay_PaymentController extends Mage_Core_Controller_Front_Action {
      
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function reviewAction()
    {
        $this->loadLayout();
        $this->renderLayout();       
    }

    public function cancelAction()
    {
        include "class.ipay.php";
        //include "func.xml.php";
        
        $xml = xml2array($GLOBALS['HTTP_RAW_POST_DATA']);
        $_POST= $xml['IpayResponse'];
        $url = Mage::getStoreConfig("web/secure/base_url");
        
        $client = new SoapClient($url.'index.php/api/soap/?wsdl');
        
        // If somestuff requires api authentification,
        // then get a session token
        $api_user = Mage::getStoreConfig("payment/ipay/apiuser");
        $api_pass = Mage::getStoreConfig("payment/ipay/apipass");
        $session = $client->login($api_user, $api_pass);
        $orderNo = $_POST["AuctionOrderNo"];

        $read = Mage::getSingleton("core/resource")->getConnection("core_read");

        $sql  = "select order_id from ipay where auction_order_no='$orderNo@'";       
        $result = $read->query($sql);
        $payinfo = $result->fetch();
        $ipayNo = $payinfo["order_id"];
        
        try{
            if($ipayNo!=""){
                $result2 = $client->call($session, 'sales_order.info', "$ipayNo");
            
                Mage::log($url.$ipayNo . " was canceling");
                $client->call($session, 'sales_order.cancel', "$ipayNo");
                Mage::log($ipayNo . " was canceled");
            }

        } catch (soapFault $fault){
            Mage::log($fault->faultstring);
        }
    }
}