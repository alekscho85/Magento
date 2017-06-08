<?php
/**
 * City collection
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_Resource_City_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_languageSuffix = '';
    /**
     * Define collection model
     */
    protected function _construct()
    {
        $this->_init('extensa_econt/city');

        if (Mage::helper('extensa_econt')->getLanguage() != 'bg_BG') {
            $this->_languageSuffix = '_en';
        }
    }

    public function truncate()
    {
        $this->getConnection()->truncate($this->getMainTable());
    }

    public function setNameFilter($name, $like = true)
    {
        if ($like) {
            $this->getSelect()->where("LOWER(TRIM(main_table.name)) LIKE ? OR LOWER(TRIM(main_table.name_en)) LIKE ?", '%' . mb_strtolower(trim($name), 'UTF-8') . '%');
        } else {
            $this->getSelect()->where("LOWER(TRIM(main_table.name)) = ? OR LOWER(TRIM(main_table.name_en)) = ?", mb_strtolower(trim($name), 'UTF-8'));
        }
/* //for Magento >= 1.7
        if ($like) {
            $condition = array('like' => '%' . mb_strtolower(trim($name), 'UTF-8') . '%');
        } else {
            $condition = array('eq' => mb_strtolower(trim($name), 'UTF-8'));
        }

        $this->addFieldToFilter(
            array('LOWER(TRIM(main_table.name))', 'LOWER(TRIM(main_table.name_en))'),
            array(
                $condition,
                $condition,
            ));
*/
        return $this;
    }

    public function setPostcodeFilter($postcode)
    {
        $this->getSelect()->where("TRIM(main_table.post_code) = ?", trim($postcode));
        return $this;
    }

    public function setQuarterNameFilter($name)
    {
        $this->getSelect()->where("LOWER(TRIM(q.name)) = ? OR LOWER(TRIM(q.name_en)) = ?", mb_strtolower(trim($name), 'UTF-8'));
/* //for Magento >= 1.7
        $this->addFieldToFilter(
            array('LOWER(TRIM(q.name))', 'LOWER(TRIM(q.name_en))'),
            array(
                array('eq' => mb_strtolower(trim($name), 'UTF-8')),
                array('eq' => mb_strtolower(trim($name), 'UTF-8')),
            ));
*/
        return $this;
    }

    public function setStreetNameFilter($name)
    {
        $this->getSelect()->where("LOWER(TRIM(s.name)) = ? OR LOWER(TRIM(s.name_en)) = ?", mb_strtolower(trim($name), 'UTF-8'));
/* //for Magento >= 1.7
        $this->addFieldToFilter(
            array('LOWER(TRIM(s.name))', 'LOWER(TRIM(s.name_en))'),
            array(
                array('eq' => mb_strtolower(trim($name), 'UTF-8')),
                array('eq' => mb_strtolower(trim($name), 'UTF-8')),
            ));
*/
        return $this;
    }

    public function addQuarters()
    {
        $this->getSelect()
            ->joinLeft(
                array('q' => $this->getTable('extensa_econt/quarter')),
                'main_table.city_id = q.city_id', array()
            );
        return $this;
    }

    public function addStreets()
    {
        $this->getSelect()
            ->joinLeft(
                array('s' => $this->getTable('extensa_econt/street')),
                'main_table.city_id = s.city_id', array()
            );
        return $this;
    }

    public function addOffices()
    {
        $this->removeAllFieldsFromSelect()
            ->getSelect()
            ->columns(array('name' => 'main_table.name' . $this->_languageSuffix))
            ->joinInner(
                array('o' => $this->getTable('extensa_econt/office')),
                'main_table.city_id = o.city_id', array()
            )
            ->group('main_table.city_id')
            ->order('main_table.name' . $this->_languageSuffix, self::SORT_ORDER_ASC);
        return $this;
    }

    public function setDeliveryType($delivery_type)
    {
        $this->getSelect()
            ->joinInner(
                array('co' => $this->getTable('extensa_econt/cityoffice')),
                'o.office_code = co.office_code', array()
            )
            ->where('co.delivery_type = ?', $delivery_type);
        return $this;
    }

    /**
     * Use awlays with addOffices()
     */
    public function setAps($aps)
    {
        $this->getSelect()
            ->where('o.is_machine = ?', (int)$aps);
        return $this;
    }

    public function setNameOrder() {
        $this->getSelect()
            ->columns(array('name' => 'main_table.name' . $this->_languageSuffix))
            ->order('main_table.name' . $this->_languageSuffix, self::SORT_ORDER_ASC);

        return $this;
    }
}
