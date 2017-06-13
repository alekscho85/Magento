<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Paymentinfo
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Onepage_Paymentinfo extends Mage_Payment_Block_Form_Cashondelivery {
    //put your code here

    /**
     * This property holds cash on delivery amount without taxes.  
     * @var type 
     */
    protected $_codAmount = null;

    /**
     * This property holds cash on delivery amount with taxes applied.  
     * @var type 
     */
    protected $_codAmountWithTaxApplied = null;

    /**
     * This property is a boolean, which is used to determine, whether the tax
     * amount should be visualized
     * @var type 
     */
    protected $_showTax = null;

    /**
     * This property is a boolean, which indcates whether the currently selected
     * shipping method is marked as free by the administrators of the shop
     * @var type 
     */
    protected $_isFreeMethod = null;

    public function __construct() {
        parent::__construct();

        $isAdmin = FALSE;
        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {
            $isAdmin = TRUE;
        }

        /**
         * In this section, we retrieve array which contains the map of 
         * method=>cash on delivery amount. This map is created in 
         * Speedy_Speedyshipping_Model_Carrier_Shippingmethod::_mapMethods
         */
        if (!$isAdmin) {
            $cod = Mage::getSingleton('checkout/session')->getSpeedyCOD();
        } else {
            $cod = Mage::getSingleton('adminhtml/session_quote')->getSpeedyCOD();
        }

        if (is_array($cod)) {

            //Are we editing an existing order
            $isEditOrderRequest = FALSE;

            $currentAction = Mage::app()->getRequest()->getActionName();
            $currentController = Mage::app()->getRequest()->getControllerName();
            $currentRoute = Mage::app()->getRequest()->getRouteName();

            if ($currentController == 'sales_order_edit' &&
                    $currentAction == 'index' && $currentRoute == 'adminhtml') {

                $isEditOrderRequest = 1;
            }

            if ($isAdmin && !$isEditOrderRequest) {

                $address = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getShippingAddress();


                $carrierMethodInRequest = Mage::app()->getRequest()->getPost('shipping_method');
                $carrierMethodInSession = $address->getShippingMethod();


                //the request takes precedence over the data in session
                if (isset($carrierMethodInRequest) && isset($carrierMethodInSession)) {
                    $carrierMethod = $carrierMethodInRequest;
                } else if (!isset($carrierMethodInRequest) && isset($carrierMethodInSession)) {
                    $carrierMethod = $carrierMethodInSession;
                } else if (isset($carrierMethodInRequest) && !isset($carrierMethodInSession)) {
                    $carrierMethod = $carrierMethodInRequest;
                }


                $isExactHourUsed = false;

                if (Mage::app()->getRequest()->getParam('speedy_exact_hour') !== FALSE &&
                        strlen(Mage::app()->getRequest()->getParam('speedy_exact_hour') !== FALSE) > 0 &&
                        Mage::app()->getRequest()->getParam('speedy_exact_minutes') !== FALSE &&
                        strlen(Mage::app()->getRequest()->getParam('speedy_exact_minutes'))) {
                    $isExactHourUsed = true;
                }
            } else if ($isAdmin && $isEditOrderRequest) {
                $address = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getShippingAddress();
                $carrierMethod = $address->getShippingMethod();
            } else {
                $quote = Mage::getModel('checkout/cart')->getQuote();
                $carrierMethod = $quote->getShippingAddress()->getShippingMethod();
            }
            
            if(isset($carrierMethod)){
            $code = explode('_', $carrierMethod);
            }
            if (isset($code)) {
                //Is Speedy the choosen courier
                if ($code[0] == 'speedyshippingmodule') {

                    $amountWithoutTax = $this->_codAmount = ($cod[$code[1]] ? number_format($cod[$code[1]], 2) : 0);


                    $taxCalculator = Mage::helper('tax');

                    //$this->_showTax = TRUE;

                    if ($taxCalculator->getShippingPrice($amountWithoutTax, true)) {
                        $this->_codAmountWithTaxApplied = number_format($taxCalculator->getShippingPrice($amountWithoutTax, true), 2);
                    }

                    if ($taxCalculator->getShippingPrice($amountWithoutTax, $this->helper('tax')->displayShippingPriceIncludingTax())) {
                        $this->_codAmount = number_format($taxCalculator->getShippingPrice($amountWithoutTax, $this->helper('tax')->displayShippingPriceIncludingTax()), 2);
                    }
                }
            }
        }
        
        if (!$isAdmin) {
            $shippingAmount = Mage::getSingleton('checkout/session')
                              ->getQuote()
                              ->getShippingAddress()
                              ->getShippingAmount();
        } else {
            $shippingAmount = Mage::getSingleton('adminhtml/session_quote')
                              ->getQuote()
                              ->getShippingAddress()
                              ->getShippingAmount();
        }
        
        //Is free shipping enabled
        /* if (Mage::getStoreConfig('carriers/speedyshippingmodule/free_shipping_enable')) {

            $freeCityMethod = Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_city');

            $freeInterCityMethod = Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_intercity');

            $freeInternationalMethod = explode(',', Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_international'));

            if (isset($code)) {
                //Is this a free method
                if (array_key_exists(1, $code)) {
                    if (($code[1] == $freeCityMethod) ||
                        ($code[1] == $freeInterCityMethod) ||
                        in_array($code[1], $freeInternationalMethod)) {
                        $this->_isFreeMethod = TRUE;
                    }
                }
            }
        }else */ if($shippingAmount == 0.000){
              $this->_isFreeMethod = TRUE;
        }

        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {

            $this->setData('area', 'adminhtml');
            $this->setTemplate('speedy_speedyshipping/sales/order/create/billing/method/paymentInfo.phtml');
        } else {

            $this->setTemplate('speedy_speedyshipping/checkout/onepage/payment_method/paymentInfo.phtml');
        }
    }

}

?>
