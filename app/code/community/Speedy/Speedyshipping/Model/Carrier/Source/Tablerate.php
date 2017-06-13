<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of beforepayment
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Carrier_Source_Tablerate extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
        Mage::getResourceModel('speedyshippingmodule/carrier_tablerate')->uploadAndImport($this);
    }
}