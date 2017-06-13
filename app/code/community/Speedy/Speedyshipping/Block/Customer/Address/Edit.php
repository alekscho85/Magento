<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Speedy_SpeedyShipping_Block_Customer_Address_Edit
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Customer_Address_Edit extends Mage_Customer_Block_Address_Edit {
    //put your code here

    /**
     * This variable holds the type of nomenclature Speedy has for a particular
     * site
     * @var type 
     */
    protected $_isFullNomenclature;

    /**
     * This method check the current available nomenclature, for the siteID 
     * of the address currently being edited
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $addressModel = Mage::getModel('speedyshippingmodule/autocomplete_address');
        $cityId = $this->getAddress()->getSpeedySiteId();

        if ($cityId) {
            $result = $addressModel->getSite($cityId);

            if (isset($result)) {
                $result = json_decode($result);

                $this->_isFullNomenclature = $result[0]->is_full_nomenclature;
            }
        }
    }

    /**
     * This method check if current address is a valid Speedy address, by 
     * looking if it has na siteId assigned to it
     * @return boolean
     */
    protected function checkIsAddressVerifiyed() {
        parent::_prepareLayout();
        $address = $this->_address;

        if ($address->getSpeedySiteId()) {
            return $this->__("Valid Speedy Address");
        } elseif ($address->getId() && !$address->getSpeedySiteId()) {
            return $this->__("Invalid Speedy Address");
        } else {
            return FALSE;
        }
    }

}

?>
