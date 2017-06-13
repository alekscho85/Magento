<?php

class Trackingmore_Detrack_Model_Resource_Track extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('detrack/track', 'id');
    }

}