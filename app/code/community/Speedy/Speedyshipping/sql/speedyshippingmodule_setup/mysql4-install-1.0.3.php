<?php

$installer = $this;
$this->getConnection()->disallowDdlCache();
$this->getConnection()->resetDdlCache();
$installer->startSetup();

//use get table

$installer->run("DROP TABLE IF EXISTS `{$installer->getTable('speedyshippingmodule/saveorder')}`;
    
CREATE TABLE `{$installer->getTable('speedyshippingmodule/saveorder')}` (
  `speedy_order_id` INT(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Speedy_order_id',
  `order_id` INT(10) UNSIGNED NOT NULL COMMENT 'Magento order ID',
  `pick_from_office` SMALLINT(6) DEFAULT '0' COMMENT 'Whether the customer will pick up the package from speedy office',
  `bol_id` BIGINT(20) UNSIGNED DEFAULT NULL COMMENT 'speedy bill of lading ID',
  `speedy_servicetype_id` INT(10) UNSIGNED NOT NULL COMMENT 'Unique identifier of speedy service chosen for the shipment',
  `office_id` SMALLINT(5) UNSIGNED DEFAULT NULL COMMENT 'The of the office from which the customer will pick up a package',
  `is_cod` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Is_cod',
  `payer_type` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Payer_type',
  `message` TEXT COMMENT 'Message',
  `fixed_time` INT(10) DEFAULT NULL,
  `bol_created_time` TIME DEFAULT NULL,
  `bol_created_year` YEAR(4) DEFAULT NULL,
  `bol_created_month` TINYINT(3) DEFAULT NULL,
  `bol_created_day` TINYINT(3) DEFAULT NULL,
  `send_for_shipping` SMALLINT(5) UNSIGNED DEFAULT NULL COMMENT 'Whether a Speedy courier has taken a package or not',
  PRIMARY KEY (`speedy_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='speedy_order_shipping';");

$this->addAttribute('customer_address', 'speedy_site_id', array(
    'type' => 'int',
    'input' => 'hidden',
    'label' => 'speedy site id',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

$this->addAttribute('customer_address', 'speedy_office_id', array(
    'type' => 'int',
    'input' => 'hidden',
    'label' => 'speedy office id',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

$this->addAttribute('customer_address', 'speedy_office_chooser', array(
    'type' => 'int',
    'input' => 'boolean',
    'label' => 'speedy office chooser',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

$this->addAttribute('customer_address', 'speedy_office_name', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'speedy office txtBox',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));
$this->addAttribute('customer_address', 'speedy_quarter_id', array(
    'type' => 'varchar',
    'input' => 'hidden',
    'label' => 'speedy quarter id',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));
$this->addAttribute('customer_address', 'speedy_quarter_name', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'speedy quarter name',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

$this->addAttribute('customer_address', 'speedy_street_name', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'speedy street name',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

$this->addAttribute('customer_address', 'speedy_street_id', array(
    'type' => 'varchar',
    'input' => 'hidden',
    'label' => 'speedy street id',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

$this->addAttribute('customer_address', 'speedy_street_number', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'speedy street number',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));
$this->addAttribute('customer_address', 'speedy_block_number', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'speedy block number',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));


$this->addAttribute('customer_address', 'speedy_entrance', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'speedy entrance',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

$this->addAttribute('customer_address', 'speedy_floor', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'speedy floor',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

$this->addAttribute('customer_address', 'speedy_apartment', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'speedy apartment',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));


$this->addAttribute('customer_address', 'speedy_address_note', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'speedy address note',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));


Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_site_id')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_office_id')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_office_chooser')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();


Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_office_name')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_quarter_id')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_quarter_name')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_street_name')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_street_id')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_street_number')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();
Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_block_number')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();


Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_apartment')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();


Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_entrance')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();


Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_floor')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_address_note')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();





$order_address_table = $this->getTable('sales/order_address');
$quote_address_table = $this->getTable('sales/quote_address');


$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_street_id` int(10) 
");


$installer->run("
ALTER TABLE  $quote_address_table ADD  `speedy_street_id` int(10) 
");
 



$installer->run("
ALTER TABLE  $quote_address_table ADD  `speedy_site_id` int(10) NOT NULL
");


$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_site_id` int(10) NOT NULL
");



$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_office_id` int(10) NULL
");


$installer->run("
ALTER TABLE  $quote_address_table ADD  `speedy_office_id` int(10) NULL
");


$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_office_chooser` int(10) NULL
");


$installer->run("
ALTER TABLE  $quote_address_table ADD  `speedy_office_chooser` int(10) NULL
");

$installer->run("
ALTER TABLE  $quote_address_table ADD  `speedy_quarter_name` varchar(255) 
");
 

$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_quarter_name` varchar(255) 
");


$installer->run("
ALTER TABLE  $quote_address_table ADD  `speedy_street_name` varchar(255) 
");
 

$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_street_name` varchar(255) 
");


$installer->run("
ALTER TABLE  $quote_address_table ADD  `speedy_quarter_id` int(10) 
");
 

$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_quarter_id` int(10) 
");

$installer->run("
ALTER TABLE  $quote_address_table ADD  `speedy_block_number` varchar(255) 
");
 

$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_block_number` varchar(255) 
");


$installer->run("
ALTER TABLE  $quote_address_table ADD `speedy_street_number` varchar(10) 
");
 

$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_street_number` varchar(10)
");

$installer->run("
ALTER TABLE  $quote_address_table ADD `speedy_apartment` varchar(255) 
");
 

$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_apartment` varchar(255) 
");

$installer->run("
ALTER TABLE  $quote_address_table ADD `speedy_entrance` varchar(10)
");
 

$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_entrance` varchar(10)
");

$installer->run("
ALTER TABLE  $quote_address_table ADD `speedy_floor` varchar(10)
");
 

$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_floor` varchar(10)
");


$installer->run("
ALTER TABLE  $quote_address_table ADD `speedy_address_note` varchar(255)
");
 

$installer->run("
ALTER TABLE  $order_address_table ADD  `speedy_address_note` varchar(255)
");


$installer->endSetup();

//remove closing
?>
