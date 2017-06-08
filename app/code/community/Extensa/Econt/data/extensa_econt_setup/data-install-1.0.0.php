<?php
/**
 * Econt data installation script
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
if (!Mage::getStoreConfigFlag('carriers/extensa_econt/installed')) {
    Mage::getModel('core/config')->saveConfig('carriers/extensa_econt/installed', 1);

    $installer = $this;

    @mail('support@extensadev.com', 'Econt Express Shipping Module installed (Magento)', Mage::getBaseUrl() . ' - ' . Mage::getStoreConfig('general/store_information/name') . "\r\n" . 'version - ' . Mage::getVersion() . "\r\n" . 'IP - ' . Mage::helper('core/http')->getRemoteAddr(), 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n" . 'From: ' . Mage::getStoreConfig('trans_email/ident_general/name') . ' <' . Mage::getStoreConfig('trans_email/ident_general/email') . '>' . "\r\n");

    $dir = dirname(__FILE__) . DS . 'sql' . DS;

    $handle = @fopen($dir . 'extensa_econt_city.sql', 'r');
    if ($handle) {
        $installer->run("TRUNCATE TABLE {$this->getTable('extensa_econt/city')};");
        while (($line = fgets($handle)) !== false) {
            $installer->run("INSERT INTO {$this->getTable('extensa_econt/city')} (`city_id`, `post_code`, `type`, `name`, `name_en`, `zone_id`, `country_id`, `office_id`) VALUES ({$line});");
        }
        fclose($handle);
    }

    $handle = @fopen($dir . 'extensa_econt_city_office.sql', 'r');
    if ($handle) {
        $installer->run("TRUNCATE TABLE {$this->getTable('extensa_econt/cityoffice')};");
        while (($line = fgets($handle)) !== false) {
            $installer->run("INSERT INTO {$this->getTable('extensa_econt/cityoffice')} (`city_office_id`, `office_code`, `shipment_type`, `delivery_type`, `city_id`) VALUES ({$line});");
        }
        fclose($handle);
    }

    $handle = @fopen($dir . 'extensa_econt_country.sql', 'r');
    if ($handle) {
        $installer->run("TRUNCATE TABLE {$this->getTable('extensa_econt/country')};");
        while (($line = fgets($handle)) !== false) {
            $installer->run("INSERT INTO {$this->getTable('extensa_econt/country')} (`country_id`, `name`, `name_en`, `zone_id`) VALUES ({$line});");
        }
        fclose($handle);
    }

    $handle = @fopen($dir . 'extensa_econt_office.sql', 'r');
    if ($handle) {
        $installer->run("TRUNCATE TABLE {$this->getTable('extensa_econt/office')};");
        while (($line = fgets($handle)) !== false) {
            $installer->run("INSERT INTO {$this->getTable('extensa_econt/office')} (`office_id`, `name`, `name_en`, `office_code`, `address`, `address_en`, `phone`, `work_begin`, `work_end`, `work_begin_saturday`, `work_end_saturday`, `time_priority`, `city_id`) VALUES ({$line});");
        }
        fclose($handle);
    }

    $handle = @fopen($dir . 'extensa_econt_quarter.sql', 'r');
    if ($handle) {
        $installer->run("TRUNCATE TABLE {$this->getTable('extensa_econt/quarter')};");
        while (($line = fgets($handle)) !== false) {
            $installer->run("INSERT INTO {$this->getTable('extensa_econt/quarter')} (`quarter_id`, `name`, `name_en`, `city_id`) VALUES ({$line});");
        }
        fclose($handle);
    }

    $handle = @fopen($dir . 'extensa_econt_region.sql', 'r');
    if ($handle) {
        $installer->run("TRUNCATE TABLE {$this->getTable('extensa_econt/region')};");
        while (($line = fgets($handle)) !== false) {
            $installer->run("INSERT INTO {$this->getTable('extensa_econt/region')} (`region_id`, `name`, `code`, `city_id`) VALUES ({$line});");
        }
        fclose($handle);
    }

    $handle = @fopen($dir . 'extensa_econt_street.sql', 'r');
    if ($handle) {
        $installer->run("TRUNCATE TABLE {$this->getTable('extensa_econt/street')};");
        while (($line = fgets($handle)) !== false) {
            $installer->run("INSERT INTO {$this->getTable('extensa_econt/street')} (`street_id`, `name`, `name_en`, `city_id`) VALUES ({$line});");
        }
        fclose($handle);
    }

    $handle = @fopen($dir . 'extensa_econt_zone.sql', 'r');
    if ($handle) {
        $installer->run("TRUNCATE TABLE {$this->getTable('extensa_econt/zone')};");
        while (($line = fgets($handle)) !== false) {
            $installer->run("INSERT INTO {$this->getTable('extensa_econt/zone')} (`zone_id`, `name`, `name_en`, `national`, `is_ee`) VALUES ({$line});");
        }
        fclose($handle);
    }
}
