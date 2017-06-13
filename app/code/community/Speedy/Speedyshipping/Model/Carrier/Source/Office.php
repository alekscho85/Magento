<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Speedy_SpeedyShipping_Model_Carrier_Source_Office
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Carrier_Source_Office {
    //put your code here
    public function toOptionArray()
    {
        $speedy = Mage::getSingleton('speedyshippingmodule/carrier_shippingmethod');
        $offices = $speedy->getOffices();
        $arr = array();
            foreach ($offices as $k => $v) {
            $arr[] = array('value' => $k, 'label' => $v);
        }
        return $arr;
    }
}

?>
