<?php

$installer = $this;
$this->getConnection()->disallowDdlCache();
$this->getConnection()->resetDdlCache();
$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('speedyshippingmodule/saveorder')}` ADD COLUMN deferred_delivery_workdays TINYINT(3) UNSIGNED DEFAULT NULL");
$installer->endSetup();
