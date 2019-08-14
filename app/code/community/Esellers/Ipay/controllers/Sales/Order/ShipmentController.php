<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';

class Esellers_Ipay_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    /**
     * Initialize shipment items QTY
     */
    protected function _getItemQtys()
    {
        parent::_getItemQtys();
    }

    /**
     * Initialize shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment|bool
     */
    protected function _initShipment()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));

        $shipment = false;
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');

        if ($shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        } elseif ($orderId) {
            $order      = Mage::getModel('sales/order')->load($orderId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->_getSession()->addError($this->__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedDoShipmentWithInvoice()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys();
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

            $tracks = $this->getRequest()->getPost('tracking');
            if ($tracks) {
                foreach ($tracks as $data) {
                    if (empty($data['number'])) {
                        Mage::throwException($this->__('Tracking number cannot1 be empty.'));
                    }
                    
                    
                    $track = Mage::getModel('sales/order_shipment_track')
                        ->addData($data);
                    $shipment->addTrack($track);
                }
            }
        }

        Mage::register('current_shipment', $shipment);
        return $shipment;
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Mage_Adminhtml_Sales_Order_ShipmentController
     */
    protected function _saveShipment($shipment)
    {
        parent::_saveShipment($shipment);
    }

    /**
     * Shipment information page
     */
    public function viewAction()
    {
        if ($this->_initShipment()) {
            $this->_title($this->__('View Shipment'));

            $this->loadLayout();
            $this->getLayout()->getBlock('sales_shipment_view')
                ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
            $this->_setActiveMenu('sales/order')
                ->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Start create shipment action
     */
    public function startAction()
    {
        /**
         * Clear old values for shipment qty's
         */
       $this->_redirect('*/*/new', array('order_id'=>$this->getRequest()->getParam('order_id')));
    }

    /**
     * Shipment create page
     */
    public function newAction()
    {
        parent::newAction();
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return null
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }

            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }


            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }
             
            $tracking = $this->getRequest()->getPost('tracking');

            if(Mage::getStoreConfig("payment/ipay/active")){
                foreach($tracking as $key=>$arr){
                    $tracknums = $arr["number"];
                    $trackcode = $arr["carrier_code"];
                
                    $flag=$this->chk($tracknums,$trackcode);
                    if($flag==false){
                        Mage::throwException($this->__('Invalid tracking number.'));
                    }
                }
            }

            if($flag){
                $this->_saveShipment($shipment);

                $shipment->sendEmail(!empty($data['send_email']), $comment);

                $shipmentCreatedMessage = $this->__('The shipment has been created.');
                $labelCreatedMessage    = $this->__('The shipping label has been created.');

                $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                    : $shipmentCreatedMessage);
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
            }
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
        }
    }

    /**
     * Send email with shipment data to customer
     */
    public function emailAction()
    {
        parent::emailAction();
    }

    /**
     * Add new tracking number action
     */
    public function addTrackAction()
    {
        try {
            $carrier = $this->getRequest()->getPost('carrier');
            $number  = $this->getRequest()->getPost('number');
            $title  = $this->getRequest()->getPost('title');
            if (empty($carrier)) {
                Mage::throwException($this->__('The carrier needs to be specified.'));
            }
            
            if (empty($number)) {
                Mage::throwException($this->__('Tracking number cannot be empty.'));
            }
            
            $flag=$this->chk($number,$carrier);
            if($flag==false){
                Mage::throwException($this->__("$flag".'Invalid tracking number.'));
            }
            
            $shipment = $this->_initShipment();
            
            if ($shipment) {
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($number)
                    ->setCarrierCode($carrier)
                    ->setTitle($title);
                $shipment->addTrack($track)
                    ->save();

                $this->loadLayout();
                $response = $this->getLayout()->getBlock('shipment_tracking')->toHtml();
            } else {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot initialize shipment for adding tracking number.1'),
                );
            }
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot add tracking number.'),
            );
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Return grid with shipping items for Ajax request
     *
     * @return Mage_Core_Controller_Response_Http
     */
    public function getShippingItemsGridAction()
    {
        $this->_initShipment();
        return $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('adminhtml/sales_order_shipment_packaging_grid')
                ->setIndex($this->getRequest()->getParam('index'))
                ->toHtml()
           );
    }
    
   public function chk($invoiceNo,$company)
   {
      $digit=1;
      if($company=="cjgls"){
        if(strlen($invoiceNo)==12)
          $digit=2;
        else if(strlen($invoiceNo==11))
          $digit=3;
      }
      $array = $this->invoiceNoLength();
      $companyLength = $array[$company];
            
      if(array_search($company,$array)!==false){
          return true;
      }
      
      if(!$companyLength)
          return true;
      
      if(!array_search(strlen($invoiceNo),$companyLength)){
        return false;
      }
      
      if($companyLength["flag"]){
        $lastNum = substr($invoiceNo,-1);
        $firstNum = substr($invoiceNo,0+($digit-1),-1);
        if($firstNum%7 == $lastNum)
          return true;
        else
          return false;
      }else{
        if($company=="epost" ){
          $fnum = substr($invoiceNo,0,1);
          $keyArr = array( 1=>6, 2=>7, 3=>8);
          if(!array_search($fnum,$keyArr)){
            return false;
          }
        }
      }
    }

   public function invoiceNoLength(){
      return array(   "hanjin"        =>      array("flag"=>true, 1=>10 ,2=>12),
                      "cjgls"         =>      array("flag"=>true,  1=>10 ,2=>11  ,3=>12),
                      "kyungdong"     =>      array("flag"=>false, 1=>8  ,2=>9   ,3=>10  ,4=>11  ,5=>12  ,6=>13  ,7=>14  ,8=>15  ,9=>16),
                      "korex"         =>      array("flag"=>false, 1=>10),
                      "dongbu"        =>      array("flag"=>true,  1=>12),
                      "innogis"       =>      array("flag"=>false, 1=>10 ,2=>11  ,3=>12  ,4=>13),
                      "hanaro"        =>      array("flag"=>true,  1=>10),
                      "hyundai"       =>      array("flag"=>true,  1=>10 ,2=>12),
                      "yellow"        =>      array("flag"=>true,  1=>11),
                      "kgb"           =>      array("flag"=>true,  1=>11),
                      "epost"         =>      array("flag"=>false, 1=>13),
                      "etc"           =>      array(1=>10),
                      );
    }
}


?>
