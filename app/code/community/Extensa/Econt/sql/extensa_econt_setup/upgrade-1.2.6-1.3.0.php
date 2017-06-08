<?php

$installer = $this;
$installer->startSetup();

if ($installer->getConnection()->isTableExists($installer->getTable('extensa_econt/office'))) {
    $installer->getConnection()->addColumn(
        $installer->getTable('extensa_econt/office'),
        'is_machine',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'length' => 1,
            'nullable' => false,
            'default' => 0,
            'comment' => 'АПС офис'
        )
    );
}

if ($installer->getConnection()->isTableExists($installer->getTable('extensa_econt/customer'))) {
    $installer->getConnection()->addColumn(
        $installer->getTable('extensa_econt/customer'),
        'office_aps_id',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => false,
            'default' => 0,
            'comment' => 'АПС офис'
        )
    );
}

$installer->endSetup();
