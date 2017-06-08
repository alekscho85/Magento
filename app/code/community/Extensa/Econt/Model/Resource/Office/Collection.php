<?php
/**
 * Office collection
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_Resource_Office_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_languageSuffix = '';
    /**
     * Define collection model
     */
    protected function _construct()
    {
        $this->_init('extensa_econt/office');

        if (Mage::helper('extensa_econt')->getLanguage() != 'bg_BG') {
            $this->_languageSuffix = '_en';
        }
    }

    public function truncate()
    {
        $this->getConnection()->truncate($this->getMainTable());
    }

    public function setCityId($city_id)
    {
        $this->getSelect()
            ->columns(array(
                'name'    => 'main_table.name' . $this->_languageSuffix,
                'address' => 'main_table.address' . $this->_languageSuffix))
            ->where('main_table.city_id = ?', (int)$city_id)
            ->order('main_table.name' . $this->_languageSuffix, self::SORT_ORDER_ASC);
        return $this;
    }

    public function setDeliveryType($delivery_type)
    {
        $this->getSelect()
            ->joinInner(
                array('co' => $this->getTable('extensa_econt/cityoffice')),
                'main_table.office_code = co.office_code', array()
            )
            ->where('co.delivery_type = ?', $delivery_type)
            ->group('main_table.office_code');
        return $this;
    }

    public function setAps($aps)
    {
        $this->getSelect()
            ->where('main_table.is_machine = ?', (int)$aps);
        return $this;
    }
}
