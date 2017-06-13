<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rate
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Sales_Quote_Address_Rate extends Mage_Sales_Model_Quote_Address_Rate {

    

    private $_isFixedHourAllowed = false;

    
    
    /**
     * This method overrides the default Magento implementation, because 
     * of the necessity to add Speedy specific data (whether cash on delivery is 
     * allowed, is fixed hour allowed for the particular shipping rate etc.) to
     * the base shipping rate model
     * @param Mage_Shipping_Model_Rate_Result_Abstract $rate
     * @return type
     */
    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate) {

        $isFixedHourAllowed = Mage::getStoreConfig('carriers/speedyshippingmodule/add_fixed_hour');

        if ($rate->getRequestContainsExactHour()) {

            $this->setRequestContainsExactHour(1);
        }

        if ($rate->getIsFree()) {

            $this->setIsFree(1);
        }


        if ($isFixedHourAllowed) {

            if ($rate->getSpeedyFixedHourEnabled()) {

                $this->setSpeedyFixedHourEnabled(1);
                if ($rate->getSpeedyAmountFixedHourWithoutTax()) {
                    $this->setSpeedyAmountFixedHourWithoutTax($rate->getSpeedyAmountFixedHourWithoutTax());
                }

                if ($rate->getSpeedyAmountFixedHourWithTax()) {
                    $this->setSpeedyAmountFixedHourWithTax($rate->getSpeedyAmountFixedHourWithTax());
                }
            }
            if ($rate->getSpeedyCodAllowed()) {
                $this->setSpeedyCodAllowed($rate->getSpeedyCodAllowed());
            }
        }
        return parent::importShippingRate($rate);
    }

}

?>
