<?php
/**
 * Loading Tracking collection
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_Resource_Loadingtracking_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define collection model
     */
    protected function _construct()
    {
        $this->_init('extensa_econt/loadingtracking');
    }

    public function truncate()
    {
        $this->getConnection()->truncate($this->getMainTable());
    }

    public function setEcontLoadingId($econt_loading_id)
    {
        $this->addFieldToFilter('main_table.econt_loading_id', (int)$econt_loading_id);
        return $this;
    }
}
