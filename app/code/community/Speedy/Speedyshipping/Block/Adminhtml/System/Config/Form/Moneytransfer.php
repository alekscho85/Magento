<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Moneytransfer
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_System_Config_Form_Moneytransfer extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * Override method to output our custom HTML with JavaScript
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $user = Mage::getStoreConfig('carriers/speedyshippingmodule/username');
        $pass = Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/speedyshippingmodule/password'));

        if (!$user || !$pass) {
            return false;
        }

        try {
            $speedyEPSInterfaceImplementaion = new EPSSOAPInterfaceImpl(Mage::getStoreConfig('carriers/speedyshippingmodule/server'));
            $speedyEPS = new EPSFacade($speedyEPSInterfaceImplementaion, $user, $pass);

            $additionamUserParams = $speedyEPS->getAdditionalUserParams(time());
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'speedyLog.log');
        }

        if (!in_array('101', $additionamUserParams)) {
            $element->setDisabled('disabled')->setValue(0);
        }

        return parent::_getElementHtml($element);
    }
}
