<?php
/**
 * Street collection
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_Resource_Street_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_languageSuffix = '';
    /**
     * Define collection model
     */
    protected function _construct()
    {
        $this->_init('extensa_econt/street');

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

    public function setCityFilter($city_id)
    {
        $this->addFieldToFilter('main_table.city_id', (int)$city_id);

        return $this;
    }

    public function setNameOrder() {
        $this->getSelect()
            ->columns(array('name' => 'main_table.name' . $this->_languageSuffix))
            ->order('main_table.name' . $this->_languageSuffix, self::SORT_ORDER_ASC);

        return $this;
    }
}
