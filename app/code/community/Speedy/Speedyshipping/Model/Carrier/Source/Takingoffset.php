<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Takingoffset
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Carrier_Source_Takingoffset {
    
    public function toOptionArray(){
        $dataArray = array();
        $result = array();
        
        
        for( $i = 0; $i < 15; $i++){
            if( $i == 0 ){
               $dataArray[$i] =  Mage::helper('core')->__('no_postpone');
            }else{
                $dataArray[$i] = (string)$i;
            }
        }
        
        foreach($dataArray as $key=>$value){
           $result[] = array('value' => $key, 'label' => $value); 
        }
        
        
        return $result;
    }
}
