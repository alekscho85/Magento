<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Deferreddays
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Carrier_Source_Deferreddays {
    //put your code here
    
    public function toOptionArray(){
        $arr = array(0=>"Без отместване",1=>"1",2=>"2");
        $result = array();
        foreach($arr as $key=>$value){
           $result[] = array('value' => $key, 'label' => $value); 
        }
        
        return $result;
    }
}

?>
