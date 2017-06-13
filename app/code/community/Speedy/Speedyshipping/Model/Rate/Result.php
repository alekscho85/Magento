<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Result
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Rate_Result extends Mage_Shipping_Model_Rate_Result{
    //put your code here
    
    
    /**
     * By default, Magento sorts shipping methods based on their prices in
     * ascending order. The reason behind this override is to preserve the
     * sorting order as returned by the Speedy API
     * @return \Speedy_Speedyshipping_Model_Rate_Result
     */
    public function sortRatesByPrice() {
        
        return $this;
        //return parent::sortRatesByPrice();
    }

}

?>
