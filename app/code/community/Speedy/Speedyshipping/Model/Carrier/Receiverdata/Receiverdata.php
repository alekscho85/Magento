<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReceiverData
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Carrier_Receiverdata_ReceiverData {
    //put your code here
    
    protected $_receiverData;


    public function setReceiverData($address, $phone, $name) {

        $receiverData = new StdClass();
        $receiverData->address = new StdClass();
        if ($address->getInSessionOnly()) {
            $receiverData->address->siteID = $address->getReceiverCityId();
        } else {
            $receiverData->address->siteID = $address->getReceiverCityId(); //'БУРГАС';

            $receiverData->address->quarterName = null;
            if(!$address->getQuarterId()){
            $receiverData->address->quarterName = $address->getSpeedyQuarterName();
            }
            $receiverData->address->quarter = $address->getQuarterId();


            $receiverData->address->blockNo = $address->getBlockId();

            $receiverData->address->streetName = null;
            if(!$address->getStreetId()){
            $receiverData->address->streetName = $address->getSpeedyStreetName();
            }
            $receiverData->address->street = $address->getStreetId(); //null;


            $receiverData->address->streetNo = $address->getStreetNo();
            
            $receiverData->address->speedyEntrance = $address->getSpeedyEntrance();
            
            $receiverData->address->speedyFloor = $address->getSpeedyFloor();
            
            $receiverData->address->speedyApartment = $address->getSpeedyApartment();

            $receiverData->address->speedyAddressNote = $address->getSpeedyAddressNote();

            $receiverData->address->speedyCountryId = $address->getSpeedyCountryId();
            $receiverData->address->speedyStateId = $address->getSpeedyStateId();
            $receiverData->address->city = $address->getCity();
            $receiverData->address->postcode = $address->getPostcode();
            $receiverData->address->street1 = $address->getStreet1();
            $receiverData->address->street2 = $address->getStreet2();

            $receiverData->partnerName = $name;
            //$receiverData->contactName = $this->_request->getRecipientContactPersonName();
            $receiverData->contactPhone = $phone;
        }
        $this->_receiverData =  $receiverData;
    }
    
    public function getReceiverData(){
        return $this->_receiverData;
    }
}

?>
