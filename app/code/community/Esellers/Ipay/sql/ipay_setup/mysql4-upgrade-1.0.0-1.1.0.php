<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('ipay')} (
  `ipay_id` int(11) unsigned NOT NULL auto_increment,
  `auction_order_no` varchar(50) NOT NULL,
  `ipay_cart_no` int(10) NOT NULL,
  `orderdate` date,
  `bankcode` varchar(20) ,
  `bankname` varchar(50) ,
  `paydate` date,
  `payprice` int(10),
  `payment_type` char(1),
  `expire_date` date,
  `card_name` varchar(20),
  `auction_pay_no` varchar(20),
  `quote_id` int(11),
  `order_id` varchar(30),
  `checkoutsession` varchar(200),
  `auction_item_no` varchar(20),
   PRIMARY KEY (`ipay_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into core_config_data (scope, scope_id,path,value) values ('default',0,'carriers/ipay/model','Esellers_Ipay_Model_Carrier_ShippingMethod');
insert into core_config_data (scope, scope_id,path,value) values ('default',0,'carriers/ipay/handling_fee','0');
insert into core_config_data (scope, scope_id,path,value) values ('default',0,'carriers/ipay/active','1');
insert into core_config_data (scope, scope_id,path,value) values ('default',0,'carriers/ipay/title','Cash On Delivery');
insert into core_config_data (scope, scope_id,path,value) values ('default',0,'carriers/ipay/handling_action','O');
insert into core_config_data (scope, scope_id,path,value) values ('default',0,'carriers/ipay/handling_type','F');
insert into core_config_data (scope, scope_id,path,value) values ('default',0,'carriers/ipay/shiptype','3');
insert into core_config_data (scope, scope_id,path,value) values ('default',0,'pament/ipay/ipayuse','1');
insert into core_config_data (scope, scope_id,path,value) values ('default',0,'pament/ipay/ipay_payment_method','0')
");
$installer->endSetup();
?>
