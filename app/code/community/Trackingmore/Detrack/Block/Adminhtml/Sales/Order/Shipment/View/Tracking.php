<?php

class Trackingmore_Detrack_Block_Adminhtml_Sales_Order_Shipment_View_Tracking extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Tracking
{
    public static $carrierList = array();
    public function getCarriers()
    { 
        if (!self::$carrierList) {
            $express = parent::getCarriers();
 
            $enabled = Mage::getStoreConfig('tr_section_setttings/settings/status');
            if (!$enabled)
                return $express; 

            $disableDefault = isset($config['disable_default_carriers']) && $config['disable_default_carriers'] ? 1 : 0;

           
            $TrackingmoreCarriers = Mage::getModel('detrack/carrier')
                ->getList();
            if ($TrackingmoreCarriers) {
                if ($disableDefault) {
                    $express = array();
                }
                else {
                    $express[''] = $this->__('----- Trackingmore carriers -----');
                }
                foreach ($TrackingmoreCarriers as $item) {
                    $express[$item->getPrefixedCode()] = $item->getData('name');
                }
            }

            self::$carrierList = $express;
        }

        return self::$carrierList;
    }

    public function getCarrierTitle($code)
    {
        $enabled = Mage::getStoreConfig('tr_section_setttings/settings/status');
        if (!$enabled)
            return parent::getCarrierTitle($code);

        $allCarriers = $this->getCarriers();

        if (isset($allCarriers[$code]))
            return $allCarriers[$code];
        else
            return parent::getCarrierTitle($code);

    }

}