<?php


class Esellers_Ipay_Model_Mysql4_Ipay extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ipay/ipay', 'id');
    }
}