<?php

$installer = $this;
$this->getConnection()->disallowDdlCache();
$this->getConnection()->resetDdlCache();
$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS `{$installer->getTable('speedyshippingmodule/tablerate')}` (
  `pk` INT(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `service_id` INT(11) NOT NULL,
  `take_from_office` TINYINT(1) NOT NULL,
  `weight` DECIMAL(15,4) NOT NULL,
  `order_total` DECIMAL(15,4) NOT NULL,
  `price_without_vat` DECIMAL(15,4) NOT NULL,
  `website_id` INT(11) NOT NULL COMMENT 'Website Id',
  PRIMARY KEY (`pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$order_address_table = $installer->getTable('sales/order_address');
$quote_address_table = $installer->getTable('sales/quote_address');

$installer->run("
  ALTER TABLE `{$installer->getTable('sales/order_address')}` ADD  `speedy_country_id` int(10) 
");

$installer->run("
  ALTER TABLE `{$installer->getTable('sales/quote_address')}` ADD  `speedy_country_id` int(10) 
");

$installer->run("
  ALTER TABLE `{$installer->getTable('sales/order_address')}` ADD  `speedy_state_id` varchar(50) 
");

$installer->run("
  ALTER TABLE `{$installer->getTable('sales/quote_address')}` ADD  `speedy_state_id` varchar(50) 
");

$this->addAttribute('customer_address', 'speedy_country_id', array(
    'type' => 'varchar',
    'input' => 'hidden',
    'label' => 'speedy country id',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

$this->addAttribute('customer_address', 'speedy_state_id', array(
    'type' => 'varchar',
    'input' => 'hidden',
    'label' => 'speedy state id',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_country_id')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'speedy_state_id')
    ->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
    ->save();

$installer->endSetup();