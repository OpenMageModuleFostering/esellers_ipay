<?php

class Esellers_Ipay_Helper_Active extends Mage_Core_Helper_Abstract {

    public function getAppId() {
        return Mage::getStoreConfig('ipay/settings/appid');
    }

    public function getSecretKey() {
        return Mage::getStoreConfig('ipay/settings/secret');
    }

    public function isActiveActivity()
    {
        return Mage::getStoreConfig('ipay/activity/enabled');
    }               
}
