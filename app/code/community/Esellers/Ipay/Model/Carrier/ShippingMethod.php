<?php
    
    /**
    * Our test shipping method module adapter
    */
    class Esellers_Ipay_Model_Carrier_ShippingMethod extends Mage_Shipping_Model_Carrier_Abstract
    {
      /**
       * unique internal shipping method identifier
       *
       * @var string [a-z0-9_]
       */
      protected $_code = 'ipay';
     
      /**
       * Collect rates for this shipping method based on information in $request
       *
       * @param Mage_Shipping_Model_Rate_Request $data
       * @return Mage_Shipping_Model_Rate_Result
       */
      public function collectRates(Mage_Shipping_Model_Rate_Request $request)
      {
        // skip if not enabled
        if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
            return false;
        }
     
        /**
         * here we are retrieving shipping rates from external service
         * or using internal logic to calculate the rate from $request
         * you can see an example in Mage_Usa_Model_Shipping_Carrier_Ups::setRequest()
         */
     
        // get necessary configuration values
        $handling = Mage::getStoreConfig('carriers/'.$this->_code.'/handling_fee');
     
         // this object will be returned as result of this method
        // containing all the shipping rates of this method
        $result = Mage::getModel('shipping/rate_result');
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigdata('title'));
        $rate->setMethod('ipay');     
        $rate->setMethodTitle('Cash on Delivery');
        $rate->setCost(2500); 
        $rate->setPrice($handling); //You should calculate this or obtain in a service 
        
        $result->append($rate);
     
        return $result;
      }
     
      /**
       * This method is used when viewing / listing Shipping Methods with Codes programmatically
       */
      public function getAllowedMethods() {
        return array($this->_code => $this->getConfigData('name'));
      }
    }
?>
