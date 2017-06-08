<?php
/**
 * Loading collection
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_Resource_Loading_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define collection model
     */
    protected function _construct()
    {
        $this->_init('extensa_econt/loading');
    }

    public function truncate()
    {
        $this->getConnection()->truncate($this->getMainTable());
    }

    public function setPrevParcelNum($loading_num)
    {
        $this->addFieldToFilter('main_table.prev_parcel_num', $loading_num);
        return $this;
    }
}
