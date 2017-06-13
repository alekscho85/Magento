<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Speedy_SpeedyShipping_Model_Carrier_Source_Method
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Carrier_Source_Method {
    //put your code here
    
    public function toOptionArray()
    {
        $speedy = Mage::getSingleton('speedyshippingmodule/carrier_shippingmethod');
        $arr = array();
            foreach ($speedy->getCode('method') as $k => $v) {
            $arr[] = array('value' => $k, 'label' => $v . ' (' . Mage::helper('speedyshippingmodule')->__('Service ID:') . ' ' . $k . ')');
        }
        return $arr;
    }
}

?>
