<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Speedy_SpeedyShipping_Block_Onepage_Billing
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Onepage_Billing_Billing 
      extends Mage_Checkout_Block_Onepage_Billing{
    //put your code here
    
    
    /**
     * This method returns an array, used in the frontend, to check if the currently
     * selected customer address is valid according to Speedy rules. This is used
     * mostly on the onepage checkout page.
     * 
     * @return type
     */
    protected function getValidAddressIds(){
        $custId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $customer = Mage::getModel('customer/customer')
                     ->load($custId);
                
        
        $ids = array();
        foreach($customer->getAddresses() as $address){
            if($address->getCountryId() == 'BG' && $address->getSpeedySiteId()){
                $ids[] = $address->getId();
            } elseif ($address->getCountryId() != 'BG' && $address->getCity()) {
                $ids[] = $address->getId();
            }
        }
        return implode(',', $ids);
    }
}

?>
