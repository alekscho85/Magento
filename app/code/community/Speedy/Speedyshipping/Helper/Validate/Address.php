<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Speedy_SpeedyShipping_Helper_Validate_Address
 *
 * @author killer
 */
class Speedy_Speedyshipping_Helper_Validate_Address extends
Mage_Core_Helper_Abstract {

    //put your code here


    public function validateSpeedyAddress() {
        $request = $this->_getRequest();
        $actionName = $request->getActionName();



        if ($request->isPost()) {

            $isValidSpeedyAddress = FALSE;


            $addressData = $this->_extractSpeedyData($actionName);
            if(array_key_exists('speedy_site_id', $addressData)){
            $speedy_site_id = (int) $addressData['speedy_site_id'];
            }
            if(array_key_exists('speedy_street_id', $addressData)){
            $speedy_street_id = (int) $addressData['speedy_street_id'];
            }
            if(array_key_exists('speedy_street_name', $addressData)){
            $speedy_street_name = $addressData['speedy_street_name'];
            }
            if(array_key_exists('speedy_street_number', $addressData)){
            $speedy_street_number = $addressData['speedy_street_number'];
            }
            if(array_key_exists('speedy_quarter_id', $addressData)){
            $speedy_quarter_id = $addressData['speedy_quarter_id'];
            }
            if(array_key_exists('speedy_quarter_name', $addressData)){
            $speedy_quarter_name = $addressData['speedy_quarter_name'];
            }
            if(array_key_exists('speedy_blok_number', $addressData)){
            $speedy_blok_number = $addressData['speedy_blok_number'];
            }

            if(array_key_exists('speedy_address_note', $addressData)){
            $speedy_address_note = $addressData['speedy_address_note'];
            }
            if(array_key_exists('speedy_office_id', $addressData)){
            $speedy_office_id = $addressData['speedy_office_id'];
            }

            //Perform address validation against Speedy address verification rules
            if (!$speedy_site_id) {
                return $isValidSpeedyAddress;
            }

            if (($speedy_quarter_id || $speedy_quarter_name) &&
                    ( $speedy_street_number || $speedy_blok_number)) {
                $isValidSpeedyAddress = TRUE;
            } else if (($speedy_street_id || $speedy_street_name) &&
                    ( $speedy_street_number || $speedy_blok_number)) {
                $isValidSpeedyAddress = TRUE;
            } else if ($speedy_address_note) {
                $isValidSpeedyAddress = TRUE;
            } else if ($speedy_office_id) {
                $isValidSpeedyAddress = TRUE;
            }

            return $isValidSpeedyAddress;
        }

        return FALSE;
    }

    /**
     * Extracts address data from the current request object
     * @param type $actionName
     * @return type
     */
    protected function _extractSpeedyData($actionName = null) {
        $request = $this->_getRequest();
        $requestActionName = $request->getActionName();
        $realActionName = strtolower(substr($actionName, 4));
        $requestData = array();


        /*
         * This section is executed in the frontend, on the onepage checkout page
         * when the customer is adding or editing an address
         */
        if ($realActionName == 'billing' || $realActionName == 'shipping') {


            $requestArray = $request->getPost($realActionName);

            if (array_key_exists('speedy_site_id', $requestArray)) {
                $requestData['speedy_site_id'] = (int) $requestArray['speedy_site_id'];
            }
            if (array_key_exists('speedy_street_id', $requestArray)) {
                $requestData['speedy_street_id'] = (int) $requestArray['speedy_street_id'];
            }

            if (array_key_exists('speedy_street_name', $requestArray)) {
                $requestData['speedy_street_name'] = $requestArray['speedy_street_name'];
            }
            if (array_key_exists('speedy_street_number', $requestArray)) {
                $requestData['speedy_street_number'] = $requestArray['speedy_street_number'];
            }

            if (array_key_exists('speedy_quarter_id', $requestArray)) {
                $requestData['speedy_quarter_id'] = (int) $requestArray['speedy_quarter_id'];
            }
            if (array_key_exists('speedy_quarter_name', $requestArray)) {
                $requestData['speedy_quarter_name'] = $requestArray['speedy_quarter_name'];
            }

            if (array_key_exists('speedy_block_number', $requestArray)) {
                $requestData['speedy_blok_number'] = $requestArray['speedy_block_number'];
            }
            if (array_key_exists('speedy_entrance', $requestArray)) {
                $requestData['speedy_entrance'] = $requestArray['speedy_entrance'];
            }
            if (array_key_exists('speedy_floor', $requestArray)) {
                $requestData['speedy_floor'] = $requestArray['speedy_floor'];
            }
            if (array_key_exists('speedy_apartment', $requestArray)) {
                $requestData['speedy_flat'] = $requestArray['speedy_apartment'];
            }
            if (array_key_exists('speedy_address_note', $requestArray)) {
                $requestData['speedy_address_note'] = $requestArray['speedy_address_note'];
            }
            if (array_key_exists('speedy_office_id', $requestArray)) {
                $requestData['speedy_office_id'] = $requestArray['speedy_office_id'];
            }
        } else {


            $requestData['speedy_site_id'] = (int) $request->getPost('speedy_site_id');
            $requestData['speedy_street_id'] = (int) $request->getPost('speedy_street_id');
            $requestData['speedy_street_name'] = $request->getPost('speedy_street_name');
            $requestData['speedy_street_number'] = $request->getPost('speedy_street_number');

            $requestData['speedy_quarter_id'] = (int) $request->getPost('speedy_quarter_id');
            $requestData['speedy_quarter_name'] = $request->getPost('speedy_quarter_name');
            $requestData['speedy_blok_number'] = $request->getPost('speedy_block_number');
            $requestData['speedy_entrance'] = $request->getPost('speedy_entrance');
            $requestData['speedy_floor'] = $request->getPost('speedy_floor');
            $requestData['speedy_flat'] = $request->getPost('speedy_apartment');
            $requestData['speedy_address_note'] = $request->getPost('speedy_address_note');
            $requestData['speedy_office_id'] = $request->getPost('speedy_office_id');
        }

        if ($requestData) {
            return $requestData;
        }
    }

}

?>
