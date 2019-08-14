<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shipment tracking control form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Tracking extends Mage_Adminhtml_Block_Template
{
    /**
     * Prepares layout of block
     *
     * @return Mage_Adminhtml_Block_Sales_Order_View_Giftmessage
     */
    protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('sales')->__('Add Tracking Number'),
                    'class'   => '',
                    'onclick' => 'trackingControl.add()'
                ))

        );

    }

    /**
     * Retrieve shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }

    /**
     * Retrieve
     *
     * @return unknown
     */
    public function getCarriers()
    {
        $carriers = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers(
            $this->getShipment()->getStoreId()
        );
        $carriers['custom'] = Mage::helper('sales')->__('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        } 

        $carriers['hanjin']     =   Mage::helper('sales')->__('한진택배');
        $carriers['kyungdong']  =   Mage::helper('sales')->__('경동택배');
        $carriers['korex']      =   Mage::helper('sales')->__('대한통운');
        $carriers['dongbu']     =   Mage::helper('sales')->__('동부익스프레스');
        $carriers['innogis']    =   Mage::helper('sales')->__('이노지스');
        $carriers['hanaro']     =   Mage::helper('sales')->__('하나로택배');
        $carriers['hyundai']    =   Mage::helper('sales')->__('현대로지엠');
        $carriers['cjgls']      =   Mage::helper('sales')->__('CJGLS');
        $carriers['yellow']     =   Mage::helper('sales')->__('KG옐로우캡');		
        $carriers['kgb']        =   Mage::helper('sales')->__('로젠택배');
        $carriers['epost']      =   Mage::helper('sales')->__('우체국택배');		
        $carriers['etc']        =   Mage::helper('sales')->__('한덱스택배');
        $carriers['etc']        =   Mage::helper('sales')->__('호남택배');
        $carriers['etc']        =   Mage::helper('sales')->__('DHL택배');
        $carriers['etc']        =   Mage::helper('sales')->__('KGB택배');        
       
        
        return $carriers;
    }
}
