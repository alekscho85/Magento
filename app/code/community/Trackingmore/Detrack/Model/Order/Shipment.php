<?php

class Trackingmore_Detrack_Model_Order_Shipment extends Mage_Sales_Model_Order_Shipment {
    
    public function getAllTracks()
    {
        $tracks = parent::getAllTracks();
        $config = Mage::getStoreConfig('tr_section_setttings/settings');
        return $tracks;
    }
    

}
