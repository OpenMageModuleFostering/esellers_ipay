<?php
    include "ipaySession.php";
    class Esellers_Ipay_Model_Observer {
        const FLAG_SHOW_CONFIG = 'showConfig';
        const FLAG_SHOW_CONFIG_FORMAT = 'showConfigFormat';     

        private $request;
        
	public function cancel(Varien_Event_Observer $observer)     
	{
            $ticket = Mage::getStoreConfig("payment/ipay/certification");
            $id = Mage::getStoreConfig("payment/ipay/auctionid");
            $requestOrderNo = new requestOrderNo ($ticket,$id);
            
            $event = $observer->getEvent();
            $order = $event->getPayment()->getOrder();
            $orderid = $order->getIncrementId();
            
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $sql  = "select * from ipay where order_id='$orderid'";
                
            $result = $read->query($sql);
                
            $payinfo = $result->fetch();
            $ItemNos = $payinfo["auction_item_no"];
            $OrderNo = $payinfo["auction_order_no"];

            if($OrderNo!=""){ 
               	$nosArr = explode("@",$ItemNos);
                $orderArr = explode("@",$OrderNo);

                foreach($nosArr as $key=> $val){
                    if($val!=""){
                        $itemNo = explode("=",$val);
                        $requestResult = $requestOrderNo->doCancel($itemNo[1], $orderArr[$key]);
                    }
                }
            }
	}

        public function registerInvoice(Varien_Event_Observer $observer)
        {
            $ticket = Mage::getStoreConfig("payment/ipay/certification");
            $id = Mage::getStoreConfig("payment/ipay/auctionid");
            $requestOrderNo = new requestOrderNo ($ticket,$id);
            
            if(Mage::getStoreConfig("payment/ipay/placeorderenable")){
                $order = $observer->getData('order');
                $orderid = $order->getIncrementId();
            
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                $sql  = "select * from ipay where order_id='$orderid'";
                
                $result = $read->query($sql);
                
                $payinfo = $result->fetch();
                $ItemNos = $payinfo["auction_item_no"];
                $OrderNo = $payinfo["auction_order_no"];            

                $orderList = explode("@",$OrderNo);

                foreach($orderList as $key=> $val){
                    if($val!=""){
                        $requestResult = $requestOrderNo->doPlaceOrder($val);
                    }
                }
            }
        }

        public function startShipment(Varien_Event_Observer $observer)
        {
            $ticket = Mage::getStoreConfig("payment/ipay/certification");
            $id = Mage::getStoreConfig("payment/ipay/auctionid");
            $requestOrderNo = new requestOrderNo ($ticket,$id);

            if(Mage::getStoreConfig("payment/ipay/shipmentenable")){
                $order = $observer->getEvent()->getShipment();
                //$order = $shipment->getOrder();
                //$order = $observer->getData('shipment');
                $orderid = $order->getOrderId();

                foreach($order->getAllTracks() as $tracknum)
                {
                    $tracknums[]=$tracknum->getNumber();
                    $trackcode[]=$tracknum->getCarrierCode();
                }
                $carriercode    = $trackcode[0];
                $numbers        = $tracknums[0];
                
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                $sql  = "select entity_id,auction_order_no from sales_flat_order A,ipay B where A.increment_id=B.order_id and entity_id='$orderid'";
                
                $result = $read->query($sql);
                
                $payinfo = $result->fetch();
                $OrderNo = $payinfo["auction_order_no"];
                
                $orderList = explode("@",$OrderNo);
                foreach($orderList as $key=> $val){
                    if($val!=""){
                        $requestResult = $requestOrderNo->doShipment($val,$numbers,$carriercode);
                    }
                }
            }
        }
        
        public function checkForConfigRequest($observer) {          
            $this->request = $observer->getEvent()->getData('front')->getRequest();
            if($this->request->{self::FLAG_SHOW_CONFIG} === 'true'){
                $this->setHeader();
                $this->outputConfig();
            }
        }

        private function setHeader() {
            $format = isset($this->request->{self::FLAG_SHOW_CONFIG_FORMAT}) ? 
            $this->request->{self::FLAG_SHOW_CONFIG_FORMAT} : 'xml';                                
            switch($format){
                case 'text':
                    header("Content-Type: text/plain");
                    break;
                default:
                    header("Content-Type: text/xml");
            }           
        }

        private function outputConfig() {            
            die(Mage::app()->getConfig()->getNode()->asXML());      
        }
    }
