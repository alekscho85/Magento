<?php

$installer = $this;
$this->getConnection()->disallowDdlCache();
$this->getConnection()->resetDdlCache();
$installer->startSetup();

$installer->run("
  ALTER TABLE `{$installer->getTable('speedyshippingmodule/tablerate')}` ADD  `fixed_time_delivery` TINYINT(1) NOT NULL AFTER `price_without_vat`
");

$installer->endSetup();
