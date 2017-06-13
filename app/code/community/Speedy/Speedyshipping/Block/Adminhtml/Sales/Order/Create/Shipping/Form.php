<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Form
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_Sales_Order_Create_Shipping_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form {

    //put your code here
    protected $_selectedMethod = null;
    protected $_isExactTimeChoosen = null;
    protected $_isPriceFixed = null;
    protected $_isFreeShipping = null;

    public function __construct() {

        parent::__construct();
        $this->_isPriceFixed = Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable');
        //$this->_isFreeShipping = Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable');
    }

    
    /**
     * The main motivation behind this override of core Magento module method is
     * dictated by the fact that Magento uses the same classes when the admin is
     * creating or editing an order, but when we edit an order Magento sends 
     * two consecutive requests and the custom session data, added by the module
     * is lost. So it is necessary to save this data one more time ( this happens 
     * in Speedy_Speedyshipping_Model_Carrier_Shippingmethod::_doRequest and 
     * Speedy_Speedyshipping_Model_Carrier_Shippingmethod::_updateFreeMethodQuote
     * methods) and check the state of the data here.
     * @return type
     */

    public function getShippingRates() {
        $oldRates = parent::getShippingRates();

        $ratesFromSession = Mage::getSingleton('adminhtml/session_quote')->getSpeedyFixedHourPrices();
        if (isset($ratesFromSession) && is_array($ratesFromSession)) {

            if (array_key_exists('speedyshippingmodule', $oldRates)) {

                $speedyRates = $oldRates['speedyshippingmodule'];

                foreach ($ratesFromSession as $key => $item) {



                    foreach ($speedyRates as $rate) {

                        if ('speedy_service_' . $rate->getMethod() == $key) {
                            if (isset($item['fixedhour_amount_with_tax']) && isset($item['fixedhour_amount_without_tax'])) {
                                $rate->setSpeedyFixedHourEnabled(1);
                                $rate->setSpeedyAmountFixedHourWithoutTax($item['fixedhour_amount_without_tax']);
                                $rate->setSpeedyAmountFixedHourWithTax($item['fixedhour_amount_with_tax']);
                                $rate->setRequestContainsExactHour(1);
                            }
                            if(isset($item['is_free'])){
                                
                                $rate->setIsFree(1);
                            }
                        }
                    }
                }
            }

            Mage::getSingleton('adminhtml/session_quote')->unsSpeedyFixedHourPrices();
        }
        return $oldRates;
    }

}

?>
