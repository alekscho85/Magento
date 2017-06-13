<?php

$installer = $this;
$this->getConnection()->disallowDdlCache();
$this->getConnection()->resetDdlCache();
$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('speedyshippingmodule/saveorder')}` ADD COLUMN bol_datetime DATETIME DEFAULT NULL");
$installer->endSetup();
