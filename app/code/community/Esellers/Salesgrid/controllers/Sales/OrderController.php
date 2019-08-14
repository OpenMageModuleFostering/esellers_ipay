<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once "Mage".DS."Adminhtml".DS."controllers".DS."Sales".DS."OrderController.php";
class Esellers_Salesgrid_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/salesgrid');

        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/sales_order')
        );

        $this->renderLayout();
        
        
        
    }
    
 public function gridAction()
    {
        parent::gridAction();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }   
}
?>
