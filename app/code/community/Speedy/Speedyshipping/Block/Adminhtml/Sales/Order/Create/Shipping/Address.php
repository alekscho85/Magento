<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Address
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_Sales_Order_Create_Shipping_Address extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Address {

    //put your code here

    protected $_speedyData = null;

    public function __construct() {
        $currentAction = Mage::app()->getRequest()->getActionName();
        $currentController = Mage::app()->getRequest()->getControllerName();
        $currentRoute = Mage::app()->getRequest()->getRouteName();
        parent::__construct();
    }

    /*
     * This method builds a list of valid Speedy addresses (if any).
     * This list is used when the end user change his address via 
     * the address <select> element in the backend of Magento
     */

    protected function getValidAddressIds() {
        $custId = $this->getCustomerId();
        $customer = Mage::getModel('customer/customer')
                ->load($custId);


        $ids = array();
        foreach ($customer->getAddresses() as $address) {
            if($address->getCountryId() == 'BG' && $address->getSpeedySiteId()){
                $ids[] = $address->getId();
            } elseif ($address->getCountryId() != 'BG' && $address->getCity()) {
                $ids[] = $address->getId();
            }
        }
        //return 'var test =['.$cust.']';
        return implode(',', $ids);
    }

    /**
     * This method rearragnes the form fields in the admin, so that they are
     * identical to those in the frontend
     * 
     * @return type
     */
    protected function _prepareForm() {

        $origForm = parent::_prepareForm();
        $fieldset = $this->_form->getElement('main');

        //Set country
        $speedyCountryName = $this->_form->getElement('country_id');

        $fieldset->removeField($speedyCountryName->getId());

        $speedyCountryNameField = $fieldset->addField($speedyCountryName->getId(), 'select', $speedyCountryName->getData(), $this->_form->getElement('company')->getId()
        );

        //Set postcode
        $speedyPostcode = $this->_form->getElement('postcode');

        $fieldset->removeField($speedyPostcode->getId());

        $speedyPostcodeField = $fieldset->addField($speedyPostcode->getId(), 'text', $speedyPostcode->getData(), $this->_form->getElement('city')->getId()
        );

        //Set office enabled
        $speedyOfficeEnabled = $this->_form->getElement('speedy_office_chooser');

        $fieldset->removeField($speedyOfficeEnabled->getId());

        $speedyOfficeField = $fieldset->addField($speedyOfficeEnabled->getId(), 'select', $speedyOfficeEnabled->getData(), $this->_form->getElement('region')->getId()
        );



        //Set office name
        $speedyOfficeName = $this->_form->getElement('speedy_office_name');

        $fieldset->removeField($speedyOfficeName->getId());

        $speedyOfficeNameField = $fieldset->addField($speedyOfficeName->getId(), 'text', $speedyOfficeName->getData(), $this->_form->getElement('speedy_office_chooser')->getId()
        );


        //Set quarter 
        $speedyQuarterName = $this->_form->getElement('speedy_quarter_name');


        $fieldset->removeField($speedyQuarterName->getId());

        $speedyQuarterField = $fieldset->addField($speedyQuarterName->getId(), 'text', $speedyQuarterName->getData(), $this->_form->getElement('speedy_office_chooser')->getId()
        );

        //Set street name
        $speedyStreetName = $this->_form->getElement('speedy_street_name');

        $fieldset->removeField($speedyStreetName->getId());

        $speedyStreetField = $fieldset->addField($speedyStreetName->getId(), 'text', $speedyStreetName->getData(), $this->_form->getElement('speedy_quarter_name')->getId()
        );

        //Set street number   
        $speedyStreetNumber = $this->_form->getElement('speedy_street_number');

        $fieldset->removeField($speedyStreetNumber->getId());

        $speedyStreetNumberField = $fieldset->addField($speedyStreetNumber->getId(), 'text', $speedyStreetNumber->getData(), $this->_form->getElement('speedy_street_name')->getId()
        );

        //Set block number   
        $speedyBlokNumber = $this->_form->getElement('speedy_block_number');

        $fieldset->removeField($speedyBlokNumber->getId());

        $speedyBlockNumberField = $fieldset->addField($speedyBlokNumber->getId(), 'text', $speedyBlokNumber->getData(), $this->_form->getElement('speedy_street_number')->getId()
        );

        //Set entrance  
        $speedyEntrance = $this->_form->getElement('speedy_entrance');

        $fieldset->removeField($speedyEntrance->getId());

        $speedyEntranceField = $fieldset->addField($speedyEntrance->getId(), 'text', $speedyEntrance->getData(), $this->_form->getElement('speedy_block_number')->getId()
        );


        //Set floor 
        $speedyFloor = $this->_form->getElement('speedy_floor');

        $fieldset->removeField($speedyFloor->getId());

        $speedyFloorField = $fieldset->addField($speedyFloor->getId(), 'text', $speedyFloor->getData(), $this->_form->getElement('speedy_entrance')->getId()
        );

        //Set floor 
        $speedyApartment = $this->_form->getElement('speedy_apartment');

        $fieldset->removeField($speedyApartment->getId());

        $speedyApartmentField = $fieldset->addField($speedyApartment->getId(), 'text', $speedyApartment->getData(), $this->_form->getElement('speedy_floor')->getId()
        );

        //Set address note 
        $speedyAddressNote = $this->_form->getElement('speedy_address_note');

        $fieldset->removeField($speedyAddressNote->getId());

        $speedyAddressNoteField = $fieldset->addField($speedyAddressNote->getId(), 'text', $speedyAddressNote->getData(), $this->_form->getElement('speedy_apartment')->getId()
        );

        return $origForm;
    }

}

?>
