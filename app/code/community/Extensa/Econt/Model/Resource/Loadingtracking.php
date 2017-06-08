<?php
/**
 * Loading Tracking resource model
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_Resource_Loadingtracking extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table and primary key
     */
    protected function _construct()
    {
        $this->_init('extensa_econt/loadingtracking', 'econt_loading_tracking_id');
    }

    public function setPkAutoIncrement($isPkAutoIncrement = true)
    {
        $this->_isPkAutoIncrement = $isPkAutoIncrement;
    }
}
