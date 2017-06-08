<?php
/**
 * Country collection
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_Resource_Country_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define collection model
     */
    protected function _construct()
    {
        $this->_init('extensa_econt/country');
    }

    public function truncate()
    {
        $this->getConnection()->truncate($this->getMainTable());
    }
}
