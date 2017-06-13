<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Speedy_Speedyshipping_Model_Carrier_Source_Moneytransfer extends Mage_Core_Model_Config_Data
{
    public function _beforeSave()
    {
        if ($this->getValue()) {
            $groups = Mage::app()->getRequest()->getParam('groups');
            $server = $groups['speedyshippingmodule']['fields']['server']['value'];
            $username = $groups['speedyshippingmodule']['fields']['username']['value'];
            $password = $groups['speedyshippingmodule']['fields']['password']['value'];

            try {
                $speedyEPSInterfaceImplementaion = new EPSSOAPInterfaceImpl($server);
                $speedyEPS = new EPSFacade($speedyEPSInterfaceImplementaion, $username, $password);

                $additionamUserParams = $speedyEPS->getAdditionalUserParams(time());
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'speedyLog.log');
            }

            if (!in_array('101', $additionamUserParams)) {
                $this->setValue(0);
            }
        }

        return $this;
    }
}
