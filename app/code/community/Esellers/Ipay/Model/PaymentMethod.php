<?php
	class Esellers_IPay_Model_PaymentMethod extends Mage_Payment_Model_Method_Checkmo
	{
		protected $_code = 'ipay';
		protected $_isGateway               = false;
		protected $_canAuthorize            = false;
		protected $_canCapture              = false;
		protected $_canCapturePartial       = false;
		protected $_canRefund               = false;
		protected $_canVoid                 = false;
		protected $_canUseInternal          = true;
		protected $_canUseCheckout          = true;
		protected $_canUseForMultishipping  = true;
		protected $_canSaveCc               = false;

	}
?>