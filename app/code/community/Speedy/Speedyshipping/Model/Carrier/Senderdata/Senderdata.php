<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Senderdata
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Carrier_Senderdata_Senderdata {
    //put your code here
    
    protected $_senderData;


    public function setSenderData($senderInfo){
        if ($senderInfo) {
            $senderAddress = $senderInfo->getAddress();

            $senderData = new StdClass();
            $senderData->address = new StdClass();
    

            $phonenumber = Mage::getStoreConfig('carriers/speedyshippingmodule/contact_telephone');
            if ((int) $phonenumber) {

                $senderData->contactPhone = $phonenumber;
            }
            $senderData->address = $senderAddress;
            $this->_senderData =  $senderData;
        } else {
            return false;
        }
    }
    
    public function getSenderData(){
        return $this->_senderData;
    }
}

?>
