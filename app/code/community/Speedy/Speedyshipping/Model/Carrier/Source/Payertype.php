<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of beforepayment
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Carrier_Source_Payertype {

    public function toOptionArray() {
        $arr = array(
            ParamCalculation::PAYER_TYPE_SENDER     => Mage::helper('speedyshippingmodule')->__('sender'),
            ParamCalculation::PAYER_TYPE_RECEIVER   => Mage::helper('speedyshippingmodule')->__('receiver'),
         );
        
        $options = array();

        foreach ($arr as $key=>$value) {
            $options[] = array('value' => $key, 'label' => $value);
        }

        return $options;
    }
}
