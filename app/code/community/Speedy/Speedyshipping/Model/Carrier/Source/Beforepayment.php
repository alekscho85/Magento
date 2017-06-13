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
class Speedy_Speedyshipping_Model_Carrier_Source_Beforepayment {

    //put your code here

    public function toOptionArray() {
//        $arr = array('no_option'=>'Без опция',
//                     'test'=>'Теставай преди да платиш (ТПП)',
//                     'open'=>'Отвори преди да платиш (ОПП)');
        
        $arr = array('no_option'=>Mage::helper('core')->__('No option'),
                     'test'=>Mage::helper('core')->__('Test before payment'),
                     'open'=>Mage::helper('core')->__('Open before payment'));
        
        $options = array();

        foreach ($arr as $key=>$value) {
            $options[] = array('value' => $key, 'label' => $value);
        }

        return $options;
    }

}

?>
