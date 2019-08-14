<?php

class Esellers_Ipay_Model_Ipay extends Mage_Core_Model_Abstract {
       
        var $orderno;
        var $ipaycartno;
        var $insdate;
        var $paydate;
        var $payprice;
        var $paymenttype;
        var $serviceurl;
        var $redirecturl; 
        var $emoney;
        var $itemnos;
        var $cardname;
        var $nointerestyn;
        var $cardmonth;
        var $cardnumb;
        var $payno;
        var $auctionorderno;
        var $apprnumb;
        var $cardcode;

        public function _construct()
        {
            parent::_construct();
            $this->_init('ipay/ipay');
        }        
        
	function ipay(){
		include dirname(__FILE__)."/conf.php";
		$this->cfg = $cfg[ipay];
	}

	function reset(){
		unset($this->orderno); unset($this->payno); unset($this->cartno); unset($this->itemno); unset($this->sdate);
		unset($this->edate); unset($this->data); unset($this->error); unset($this->errormsg);
		unset($this->request); unset($this->response);
	}
        
        function setAuctionOrderNo($auctionorderno){
            $this->auctionorderno=$auctionorderno;
        }
}
?>
