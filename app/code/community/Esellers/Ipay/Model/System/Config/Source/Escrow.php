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
 * Used in creating options for Yes|No config value selection
 *
 */
class Esellers_Ipay_Model_System_Config_Source_Escrow
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Mage::getStoreConfig("web/unsecure/base_url")."skin/frontend/default/default/images/ipay/logo_ipay01.gif", 'label'=>Mage::helper('ipay')->__('Logo 1')),
            array('value' => Mage::getStoreConfig("web/unsecure/base_url")."skin/frontend/default/default/images/ipay/logo_ipay02.gif", 'label'=>Mage::helper('ipay')->__('Logo 2')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Mage::getStoreConfig("web/unsecure/base_url")."skin/frontend/default/default/images/ipay/logo_ipay01.gif" => Mage::helper('ipay')->__('Logo 1'),
            Mage::getStoreConfig("web/unsecure/base_url")."skin/frontend/default/default/images/ipay/logo_ipay02.gif" => Mage::helper('ipay')->__('Logo 2'),
        );
    }

}
