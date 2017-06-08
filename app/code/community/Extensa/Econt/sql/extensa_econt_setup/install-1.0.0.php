<?php
/**
 * Econt installation script
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;

/**
 * Creating table extensa_econt_city
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/city'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/city'))
        ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID) на населеното място')
        ->addColumn('post_code', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
            'nullable' => false,
            'default'  => '',
        ), 'пощенски код на населеното място')
        ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
            'nullable' => false,
            'default'  => '',
        ), 'тип на населеното място. Възможни стойности: ‘гр.’, ‘с.’')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на населеното място')
        ->addColumn('name_en', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на населеното място на латиница')
        ->addColumn('zone_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 3,
        ), 'ID на зоната където се намира нас. място (3 - Зона В)')
        ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 1033,
        ), 'държавата, в която попада нас. място (1033 - България)')
        ->addColumn('office_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'главния офис на обслужване на нас. място (0 - главния офис)')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/city'),
                array('post_code'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('post_code'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/city'),
                array('name'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('name'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/city'),
                array('name_en'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('name_en'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/city'),
                array('office_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('office_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('информация за населените места');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_city_office
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/cityoffice'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/cityoffice'))
        ->addColumn('city_office_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID)')
        ->addColumn('office_code', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
            'nullable' => false,
            'default'  => '',
        ), 'код на офиса, обслужващ съответния тип пратки според начина на доставка / прием')
        ->addColumn('shipment_type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'тип на пратки, обслужвани от офиса')
        ->addColumn('delivery_type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'начини на доставка, обслужвани от офиса')
        ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на населеното място')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/cityoffice'),
                array('office_code'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('office_code'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/cityoffice'),
                array('city_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('city_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('офиси, които обслужват населеното място');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_country
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/country'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/country'))
        ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID) на държавата')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'наименование на държавата')
        ->addColumn('name_en', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'наименование на държавата на латиница')
        ->addColumn('zone_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на зоната')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/country'),
                array('zone_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('zone_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('държавите обслужвани от Еконт');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_customer
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/customer'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/customer'))
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID) на потребителя')
        ->addColumn('shipping_to', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'доставка до врата/офис')
        ->addColumn('company', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'фирма')
        ->addColumn('postcode', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
            'nullable' => false,
            'default'  => '',
        ), 'пощенски код')
        ->addColumn('city', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'населено място')
        ->addColumn('quarter', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'квартал')
        ->addColumn('street', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'улица')
        ->addColumn('street_num', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
            'nullable' => false,
            'default'  => '',
        ), 'номер на улица')
        ->addColumn('other', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'друго (бл., вх., ет., ап.)')
        ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на населеното място')
        ->addColumn('office_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на офиса за доставка')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/customer'),
                array('city_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('city_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/customer'),
                array('office_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('office_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('адресите на потребителите');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_loading
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/loading'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/loading'))
        ->addColumn('econt_loading_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID)')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на поръчката')
        ->addColumn('loading_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'ID на товарителницата')
        ->addColumn('loading_num', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'номер на търсената пратка')
        ->addColumn('is_imported', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
            'nullable' => false,
            'default'  => 0,
        ), 'дали товарителницата е задействана (1,0)')
        ->addColumn('storage', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'името на офиса или линията, в която в момента се намира пратката. Ако пратката е разнесена към клиент полето е празно')
        ->addColumn('receiver_person', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'лице получило пратката')
        ->addColumn('receiver_person_phone', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'телефона на получателя')
        ->addColumn('receiver_courier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'служител предал пратката')
        ->addColumn('receiver_courier_phone', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'телефон на служителя предал пратката')
        ->addColumn('receiver_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
            'default'  => '0000-00-00 00:00:00',
        ), 'време на предаване на пратката към клиента')
        ->addColumn('cd_get_sum', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'събрана сума от наложен платеж по пратката')
        ->addColumn('cd_get_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
            'default'  => '0000-00-00 00:00:00',
        ), 'време на събиране на наложения платеж')
        ->addColumn('cd_send_sum', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'изплатена сума на наложния платеж по пратката')
        ->addColumn('cd_send_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
            'default'  => '0000-00-00 00:00:00',
        ), 'време на изплащане на наложения платеж')
        ->addColumn('total_sum', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'стойност на пратката')
        ->addColumn('currency', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
            'nullable' => false,
            'default'  => '',
        ), 'валута за плащане на пратката')
        ->addColumn('sender_ammount_due', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'сума, която се дължи по пратката от подател')
        ->addColumn('receiver_ammount_due', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'сума, която се дължи по пратката от получател')
        ->addColumn('other_ammount_due', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'сума, която се дължи по пратката от 3-та страна')
        ->addColumn('delivery_attempt_count', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
            'nullable' => false,
            'default'  => '',
        ), 'брой опити за разнос на пратката')
        ->addColumn('blank_yes', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'URL до скрипта генериращ PDF-a за товарителницата върху бланка')
        ->addColumn('blank_no', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'URL до скрипта генериращ PDF-a за товарителницата без бланка')
        ->addColumn('pdf_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'URL на товарителницата на обратната пратка')
        ->addColumn('prev_parcel_num', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'номер на товарителница, от която е възникнала текущата (ако има такава)')
        ->addColumn('next_parcel_reason', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'причина за връщане за последвала пратка, създадена на база тази товарителница')
        ->addColumn('is_returned', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
            'nullable' => false,
            'default'  => 0,
        ), 'дали има генерирана обратна пратка')
        ->addColumn('returned_blank_yes', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'URL до скрипта генериращ PDF-a за товарителницата на обратната пратка върху бланка')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/loading'),
                array('order_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('order_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('статус на товарителници');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_loading_tracking
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/loadingtracking'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/loadingtracking'))
        ->addColumn('econt_loading_tracking_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID)')
        ->addColumn('econt_loading_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на Еконт поръчката')
        ->addColumn('loading_num', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'номер на търсената пратка')
        ->addColumn('time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
            'default'  => '0000-00-00 00:00:00',
        ), 'време на събитието')
        ->addColumn('is_receipt', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
            'nullable' => false,
            'default'  => 0,
        ), 'движение на обратна разписка ли е')
        ->addColumn('event', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'събитие. Възможни стойности са client – предаване към клиент; courier – предаване към куриер; courier_direction – предаване към маршрутна линия; office – предаване в офис; first_try – първи опит за доставка, second_try – последващ опит за доставка')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на склада, служителя, маршрутната линия на кирилица')
        ->addColumn('name_en', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на склада, служителя, маршрутната линия на латиница')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/loadingtracking'),
                array('econt_loading_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('econt_loading_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('тракинг на пратката');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_office
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/office'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/office'))
        ->addColumn('office_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID) на офиса')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'наименование на офиса')
        ->addColumn('name_en', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'наименование на офиса на латиница')
        ->addColumn('office_code', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
            'nullable' => false,
            'default'  => '',
        ), 'код на офиса')
        ->addColumn('address', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'адрес на офиса')
        ->addColumn('address_en', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'адрес на офиса на латиница')
        ->addColumn('phone', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable' => false,
            'default'  => '',
        ), 'телефон на офиса')
        ->addColumn('work_begin', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
            'default'  => '0000-00-00 09:00:00',
        ), 'начало на работното време за делнични дни')
        ->addColumn('work_end', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
            'default'  => '0000-00-00 18:00:00',
        ), 'край на работното време за делнични дни')
        ->addColumn('work_begin_saturday', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
            'default'  => '0000-00-00 09:00:00',
        ), 'начало на работното време за съботни дни')
        ->addColumn('work_end_saturday', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
            'default'  => '0000-00-00 13:00:00',
        ), 'край на работното време за съботни дни')
        ->addColumn('time_priority', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
            'default'  => '0000-00-00 12:00:00',
        ), 'минимален приоритетен час')
        ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на населеното място')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/office'),
                array('office_code'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('office_code'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/office'),
                array('city_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('city_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('информация за офиси');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_order
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/order'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/order'))
        ->addColumn('econt_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID) на Еконт поръчката')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на поръчката')
        ->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
            'nullable' => false,
            'default'  => '',
        ), 'всички данни на Еконт поръчката')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/order'),
                array('order_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('order_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('Еконт поръчки');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_quarter
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/quarter'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/quarter'))
        ->addColumn('quarter_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID) на квартала')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на квартала')
        ->addColumn('name_en', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на квартала на латиница')
        ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на населено място, в което попада квартала')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/quarter'),
                array('name'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('name'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/quarter'),
                array('name_en'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('name_en'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/quarter'),
                array('city_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('city_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('информация за квартали в населените места');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_region
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/region'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/region'))
        ->addColumn('region_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID) на региона')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на региона')
        ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
            'nullable' => false,
            'default'  => '',
        ), 'пощенски код на региона')
        ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на населено място, в което попада региона')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/region'),
                array('name'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('name'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/region'),
                array('code'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('code'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/region'),
                array('city_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('city_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('информация за регионите');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_street
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/street'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/street'))
        ->addColumn('street_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID) на улицата')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на улицата')
        ->addColumn('name_en', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на улицата на латиница')
        ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'default'  => 0,
        ), 'ID на населено място, в което попада улицата')
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/street'),
                array('name'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('name'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/street'),
                array('name_en'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('name_en'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addIndex($installer->getIdxName(
                $installer->getTable('extensa_econt/street'),
                array('city_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
            ),
            array('city_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->setComment('информация за улицатите в населените места');

    $installer->getConnection()->createTable($table);
}

/**
 * Creating table extensa_econt_zone
 */
if (!$installer->getConnection()->isTableExists($installer->getTable('extensa_econt/zone'))) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('extensa_econt/zone'))
        ->addColumn('zone_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'идентификатор (ID) на зоната')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на зоната')
        ->addColumn('name_en', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
            'default'  => '',
        ), 'име на зоната на латиница')
        ->addColumn('national', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
            'nullable' => false,
            'default'  => 1,
        ), 'Дали зоната е международна или е в България. Възможни стойности: 1 - в България, 0 - международна')
        ->addColumn('is_ee', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
            'nullable' => false,
            'default'  => 1,
        ), 'Дали зоната се обслужва от Еконт Експрес или от подизпълнител. 1 - обслужва се от Еконт Експрес, 0 - от подизпълнител')
        ->setComment('зони за населените места');

    $installer->getConnection()->createTable($table);
}
