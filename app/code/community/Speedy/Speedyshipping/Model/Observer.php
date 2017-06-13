<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Speedy_SpeedyShipping_Model_Observer
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Observer extends Varien_Object {

    protected $_speedyEPSInterfaceImplementaion;
    protected $_speedyEPS;
    protected $_speedySessionId;

    /**
     * This method is used for various server side validations
     * @param type $evt
     * @return type
     */
    public function actionPredispatchHook($evt) {
        $controller = $evt->getControllerAction();
        $actionName = $controller->getRequest()->getActionName();
        $controllerName = $controller->getRequest()->getControllerName();
        $action = $evt->getEvent()->getControllerAction();
        $currentRoute = $controller->getRequest()->getRouteName();

        $this->_initSpeedyService();

        /**
         * Validation of customer address on onepage checkout->saveBilling action
         */
        if ($controllerName == 'onepage' && $actionName == 'saveBilling') {
            $validateHelper = Mage::helper('speedyshippingmodule/validate_address');
            $customerAddressId = $controller->getRequest()->getPost('billing_address_id', false);

            //the address is new
            if (!$customerAddressId) {
                // $request = $this->_getRequest();
                // $actionName = $request->getActionName();
                $billing_post_address = $controller->getRequest()->getPost('billing');

                if (!empty($billing_post_address['active_currency_code'])) {
                    $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
                    $rates = Mage::getModel('directory/currency')->getCurrencyRates(Mage::app()->getBaseCurrencyCode(), array_values($allowedCurrencies));
                    if (!isset($rates[$billing_post_address['active_currency_code']])) {
                        $result = array();
                        $result['error'] = 1;
                        
                        $result['message'] = Mage::helper('speedyshippingmodule')->__('The currency %s is missing or invalid. Please contact the administrators of the store!', $billing_post_address['active_currency_code']);
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }
                }

                if ($billing_post_address && !empty($billing_post_address['country_id']) && $billing_post_address['country_id'] != 'BG') {
                    $speedy_address = array(
                        'city_id'      => (isset($billing_post_address['speedy_site_id']) ? $billing_post_address['speedy_site_id'] : 0),
                        'city'         => (isset($billing_post_address['city']) ? $billing_post_address['city'] : ''),
                        'postcode'     => (isset($billing_post_address['postcode']) ? $billing_post_address['postcode'] : ''),
                        'address_1'    => (isset($billing_post_address['street'][0]) ? $billing_post_address['street'][0] : ''),
                        'address_2'    => (isset($billing_post_address['street'][1]) ? $billing_post_address['street'][1] : ''),
                        'country_id'   => (isset($billing_post_address['speedy_country_id']) ? $billing_post_address['speedy_country_id'] : 0),
                        'state_id'     => (isset($billing_post_address['speedy_state_id']) ? $billing_post_address['speedy_state_id'] : 0),
                    );

                    $valid = $this->validateAddress($speedy_address);
                    if ($valid !== true) {
                         $isValidSpeedyAddress = false;
                         $message = $valid;
                    } else {
                        $isValidSpeedyAddress = true;
                    }
                } else {
                    $isValidSpeedyAddress = $validateHelper->validateSpeedyAddress();
                    if (!$isValidSpeedyAddress) {
                        $message = $controller->__("Please enter a valid address");
                    }
                }

                if (!$isValidSpeedyAddress) {
                    $result = array();
                    $result['error'] = 1;
                    $result['message'] = $message; 
                    $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                } else {
                    // $address = Mage::getModel('checkout/session')->getQuote()->getShippingAddress();
                    //Clean up previously set Speedy data
                    $session = Mage::getSingleton('checkout/session');
                    $session->unsSpeedyCurrentExactTimeMethod();
                    $session->unsSpeedyExactHour();
                    $session->unsSpeedyExactMinutes();
                }
            } else {
                $session = Mage::getSingleton('checkout/session');
                $session->unsSpeedyCurrentExactTimeMethod();
                $session->unsSpeedyExactHour();
                $session->unsSpeedyExactMinutes();
            }
            /**
             * Validation of customer address on onepage checkout->saveShipping action
             */
        } else if ($controllerName == 'onepage' && $actionName == 'saveShipping') {
            $validateHelper = Mage::helper('speedyshippingmodule/validate_address');
            $customerAddressId = $controller->getRequest()->getPost('shipping_address_id', false);




            //the address is new
            if (!$customerAddressId) {
                $shipping_post_address = $controller->getRequest()->getPost('shipping');

                if (!empty($shipping_post_address['active_currency_code'])) {
                    $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
                    $rates = Mage::getModel('directory/currency')->getCurrencyRates(Mage::app()->getBaseCurrencyCode(), array_values($allowedCurrencies));
                    if (!isset($rates[$shipping_post_address['active_currency_code']])) {
                        $result = array();
                        $result['error'] = 1;
                        
                        $result['message'] = Mage::helper('speedyshippingmodule')->__('The currency %s is missing or invalid. Please contact the administrators of the store!', $billing_post_address['active_currency_code']);
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }
                }

                if ($shipping_post_address && !empty($shipping_post_address['country_id']) && $shipping_post_address['country_id'] != 'BG') {
                    $speedy_address = array(
                        'city_id'      => (isset($shipping_post_address['speedy_site_id']) ? $shipping_post_address['speedy_site_id'] : 0),
                        'city'         => (isset($shipping_post_address['city']) ? $shipping_post_address['city'] : ''),
                        'postcode'     => (isset($shipping_post_address['postcode']) ? $shipping_post_address['postcode'] : ''),
                        'address_1'    => (isset($shipping_post_address['street'][0]) ? $shipping_post_address['street'][0] : ''),
                        'address_2'    => (isset($shipping_post_address['street'][1]) ? $shipping_post_address['street'][1] : ''),
                        'country_id'   => (isset($shipping_post_address['speedy_country_id']) ? $shipping_post_address['speedy_country_id'] : 0),
                        'state_id'     => (isset($shipping_post_address['speedy_state_id']) ? $shipping_post_address['speedy_state_id'] : 0),
                    );

                    $valid = $this->validateAddress($speedy_address);
                    if ($valid !== true) {
                         $isValidSpeedyAddress = false;
                         $message = $valid;
                    } else {
                        $isValidSpeedyAddress = true;
                    }
                } else {
                    $isValidSpeedyAddress = $validateHelper->validateSpeedyAddress();
                    if (!$isValidSpeedyAddress) {
                        $message = $controller->__("Please enter a valid address");
                    }
                }


                if (!$isValidSpeedyAddress) {
                    $result = array();
                    $result['error'] = 1;
                    $result['message'] = $message; 
                    $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                } else {
                    // $address = Mage::getModel('checkout/session')->getQuote()->getShippingAddress();
                    //Clean up previously set Speedy data
                    $session = Mage::getSingleton('checkout/session');
                    $session->unsSpeedyCurrentExactTimeMethod();
                    $session->unsSpeedyExactHour();
                    $session->unsSpeedyExactMinutes();
                }
            } else {
                $session = Mage::getSingleton('checkout/session');
                $session->unsSpeedyCurrentExactTimeMethod();
                $session->unsSpeedyExactHour();
                $session->unsSpeedyExactMinutes();
            }

            /**
             * Validation of fixed hour value during onepage saveshipping method
             */
        } else if ($controllerName == 'onepage' && $actionName == 'saveShippingMethod') {
            $selectedMethod = $controller->getRequest()->getPost('shipping_method', '');

            $validationError = FALSE;

            $result = null;

            $code = explode('_', $selectedMethod);

            if ($code[0] == 'speedyshippingmodule') {
                $session = Mage::getSingleton('checkout/session');


                $session->setSpeedyCurrentExactTimeMethod((int) $code[1]);

                if ($controller->getRequest()->getParam('speedy_exact_hour_amount_withouttax_speedyshippingmodule_' . (int) $code[1]) && $controller->getRequest()->getParam('speed_exact_hour_enable') == 'on') {

                    if (!$controller->getRequest()->getParam('speedy_exact_hour') ||
                            !$controller->getRequest()->getParam('speedy_exact_minutes')) {
                        $result = array();
                        $result['error'] = 1;
                        $result['message'] = $controller->__("invalid_hour_warning");
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                        return;
                    }

                    $hour = $controller->getRequest()->getParam('speedy_exact_hour');
                    $minutes = $controller->getRequest()->getParam('speedy_exact_minutes');

                    if (strlen($hour) > 2 || strlen($minutes) > 2) {
                        $result = array();
                        $result['error'] = 1;
                        $result['message'] = $controller->__("invalid_hour_warning");
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }


                    if ((!is_numeric($hour) || !is_numeric($minutes))) {

                        $result = array();
                        $result['error'] = 1;
                        $result['message'] = $controller->__("invalid_hour_warning");
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }

                    //Convert string from request to integers
                    $hour = (int) $hour;
                    $minutes = (int) $minutes;


                    if (!is_integer($hour) || !is_integer($minutes)) {
                        $result = array();
                        $result['error'] = 1;
                        $result['message'] = $controller->__("invalid_hour_warning");
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }


                    if (($hour <= 17 && $hour >= 10)) {


                        if ($hour == 17 && $minutes >= 31) {

                            $result = array();
                            $result['error'] = 1;
                            $result['message'] = $controller->__("invalid_hour_warning");
                            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                            $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                        }

                        if ($hour == 10 && $minutes <= 29) {
                            $result = array();
                            $result['error'] = 1;
                            $result['message'] = $controller->__("invalid_hour_warning");
                            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                            $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                        }


                        if ($minutes <= 59 && $minutes >= 0) {

                            $session->setSpeedyExactHour($hour);
                            $session->setSpeedyExactMinutes($minutes);
                            $session->setSpeedyCurrentExactTimeMethod((int) $code[1]);
                        } else {
                            $result = array();
                            $result['error'] = 1;
                            $result['message'] = $controller->__("invalid_hour_warning");
                            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                            $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                        }
                    } else {
                        $result = array();
                        $result['error'] = 1;
                        $result['message'] = $controller->__("invalid_hour_warning");
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }
                } else {
                    $session->unsSpeedyCurrentExactTimeMethod();
                    $session->unsSpeedyExactHour();
                    $session->unsSpeedyExactMinutes();
                }
            }

            if ($controller->getRequest()->getParam('speedy_address_note')) {
                $session->setSpeedyAddressNote($controller->getRequest()->getParam('speedy_address_note'));
            }


            /**
             * Retrigger shipping calculation again. This is neededed because of
             * the fixed hour option, which might change the final price of 
             * the shipping
             */
            $shipping = $controller->getOnepage()->getQuote()->getShippingAddress();

            $rate = $shipping->getShippingRateByCode($selectedMethod);


            if (is_null($result)) {

                $shipping->unsetData('cached_items_all');
                $shipping->unsetData('cached_items_nominal');
                $shipping->unsetData('cached_items_nonnominal');


                $shipping->setShippingMethod($selectedMethod);
                $shipping->setCollectShippingRates(true);

                $controller->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
                $controller->getOnepage()->getQuote()->collectTotals();

                $controller->getOnepage()->getQuote()->save();
            }
        } else if ($controllerName == 'onepage' && $actionName == 'savePayment') {
            $activeCurrencyCode = Mage::getSingleton('checkout/session')->getSpeedyActiveCurrencyCode();
            $paymentMethod = $controller->getRequest()->getPost('payment');
            $shippingMethod = $controller->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod();
            $code = explode('_', $shippingMethod);

            if ($code[0] == 'speedyshippingmodule' && $paymentMethod['method'] == 'cashondelivery' && $activeCurrencyCode) {
                $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
                if (!in_array($activeCurrencyCode, $allowedCurrencies)) {
                    $result = array();
                    $result['error'] = 1;
                    $result['message'] = Mage::helper('speedyshippingmodule')->__('You can\'t use Cash on Delivery, the currency %s is missing. Please contact the administrators of the store!', $activeCurrencyCode);
                    $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                }
            }
        } else if ($controllerName == 'address' && $actionName == 'formPost') {
            $validateHelper = Mage::helper('speedyshippingmodule/validate_address');
            $address = Mage::getModel('customer/address');
            $isValidSpeedyAddress = $validateHelper->validateSpeedyAddress();

            if (!$isValidSpeedyAddress) {

                Mage::getSingleton('customer/session')->setAddressFormData($controller->getRequest()->getPost());
                $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('customer/session')->addError($controller->__("Please enter a valid address"));
                $controller->getResponse()->setRedirect(Mage::getUrl('*/*/edit', array('id' => $address->getId())));
            }
        }
    }

    
    public function postDispatchHook($evt){
        $controller = $evt->getControllerAction();
        $actionName = $controller->getRequest()->getActionName();
        $controllerName = $controller->getRequest()->getControllerName();
        $action = $evt->getEvent()->getControllerAction();
        $currentRoute = $controller->getRequest()->getRouteName();
        
        if($currentRoute == 'adminhtml' && $actionName =='save' &&
                $controllerName == 'customer'){
            
        }
    }
    
    
    
    /**
     * CRITITAL SECTION. This method ensures, that the right address parameters
     * are stored in the current user quote object. DO NOT CHANGE THIS!
     * @param type $evt
     */
    public function checkQuoteAddress($evt) {
        $quote = $evt->getQuote();
        $address = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();

        $request = Mage::app()->getRequest();

        $currentAction = Mage::app()->getRequest()->getActionName();
        $currentController = Mage::app()->getRequest()->getControllerName();
        $currentRoute = Mage::app()->getRequest()->getRouteName();

        if ($currentController == 'onepage') {

            if ($address->getCustomerAddressId()) {
                $customerAddress = Mage::getModel('customer/address')->load($address->getCustomerAddressId());
            }

            if ($currentAction == 'saveBilling') {



                $billingData = $request->getParam('billing');

                if ($billingData['use_for_shipping'] == 1) {

                    if ($address->getSameAsBilling()) {

                        $address->setStreet($billingAddress->getStreet());
                        $address->setSpeedyStreetId($billingAddress->getSpeedyStreetId());
                        $address->setSpeedyStreetName($billingAddress->getSpeedyStreetName());
                        $address->setSpeedyStreetNumber($billingAddress->getSpeedyStreetNumber());

                        $address->setSpeedyQuarterName($billingAddress->getSpeedyQuarterName());
                        $address->setSpeedyQuarterId($billingAddress->getSpeedyQuarterId());

                        $address->setSpeedyBlockNumber($billingAddress->getSpeedyBlockNumber());

                        $address->setSpeedyEntrance($billingAddress->getSpeedyEntrance());

                        $address->setSpeedyFloor($billingAddress->getSpeedyFloor());

                        $address->setSpeedyApartment($billingAddress->getSpeedyApartment());

                        $address->setSpeedyAddressNote($billingAddress->getSpeedyAddressNote());

                        $address->setSpeedyOfficeId($billingAddress->getSpeedyOfficeId());

                        $address->setSpeedyCountryId($billingAddress->getSpeedyCountryId());

                        $address->setSpeedyStateId($billingAddress->getSpeedyStateId());

                        //$quote->save();
                    }
                }
            }






            if (isset($customerAddress)) {

                if (($address->getSpeedyOfficeId() && $customerAddress->getSpeedyOfficeId()) &&
                        ($address->getSpeedyOfficeId() === $customerAddress->getSpeedyOfficeId())) {
                    
                } else if ($address->getSpeedyOfficeId()) {
                    $address->unsSpeedyOfficeId();
                }
            }
        }
    }

    /**
     * CRITITAL SECTION. This method ensures, that the right address parameters
     * are stored in the current user quote object. DO NOT CHANGE THIS!
     * @param type $evt
     */
    public function checkAddress($evt) {
        $order = $evt->getOrder();
        $address = $order->getShippingAddress();

        $customerAddress = null;

        if ($address->getCustomerAddressId()) {
            $customerAddress = Mage::getModel('customer/address')->load($address->getCustomerAddressId());
        }

        if ($customerAddress) {

            if (($address->getSpeedyOfficeId() && $customerAddress->getSpeedyOfficeId()) &&
                    ($address->getSpeedyOfficeId() === $customerAddress->getSpeedyOfficeId())) {
                
            } else if ($address->getSpeedyOfficeId()) {
                $address->unsSpeedyOfficeId();
            }
        }
    }

    /**
     * This method saves Speedy specific data about the current order, after 
     * succesful completition of the checkout process in the frontend.
     * @param type $evt
     */
    public function saveOrderAfter($evt) {

        $order = $evt->getOrder();
        $shippingAmount = $order->getShippingAmount();
        $address = $order->getShippingAddress();
        $session = Mage::getSingleton('checkout/session');
        $paymentMethod = $order->getPayment()->getMethodInstance()->getInfoInstance()->getMethod();
        $carrierCode = $order->getShippingCarrier()->getCarrierCode();


        //Check to see if Speedy is choosen to be the courier
        if ($carrierCode == "speedyshippingmodule") {

            $isEnabled = Mage::getStoreConfig('carriers/speedyshippingmodule/free_shipping_enable');


            $freeMethodSubtotal = Mage::getStoreConfig('carriers/speedyshippingmodule/free_shipping_subtotal');


            $subtotal = $order->getSubtotal();


            $saveSpeedyData = Mage::getModel('speedyshippingmodule/saveorder');


            $saveSpeedyData->setOrderId($order->getId());




            if ($session->getSpeedyAddressNote() && strlen(trim($session->getSpeedyAddressNote())) > 0) {
                $saveSpeedyData->setMessage(trim($session->getSpeedyAddressNote()));
            }



            if ($session->getSpeedyExactHour() !== FALSE && $session->getSpeedyExactMinutes() !== FALSE) {

                $hour = $session->getSpeedyExactHour();
                $minutes = $session->getSpeedyExactMinutes();

                /**
                 * Format hour and minutes values, if the user has entered
                 * values like 12:5 => 12:05
                 */
                if (strlen($hour) == 1) {

                    $hour = sprintf('%02d', $hour);
                }

                if (strlen($minutes) == 1) {

                    $minutes = sprintf('%02d', $minutes);
                }
                $saveSpeedyData
                        ->setFixedTime($hour . $minutes);
            }



            /**
             * Check if the payment method is cod on delivery
             */
            if ($paymentMethod == 'cashondelivery') {
                $saveSpeedyData->setIsCod(1);
            } else {
                $saveSpeedyData->setIsCod(0);
            }


            $shippingMethodCode = $order->getShippingMethod();


            $shippingCosts = $order->getShippingAmount();


            if ($shippingMethodCode) {
                $code = explode('_', $shippingMethodCode);
                $saveSpeedyData->setSpeedyServicetypeId($code[1]);
            }



            $isFreeShippingChoosen = FALSE;

            /**
             * Check if the shipping is free for the customer
             */
            if ($isEnabled) {

                $freeCityMethod = Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_city');

                $freeInterCityMethod = Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_intercity');

                $freeInternationalMethod = explode(',', Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_international'));

                if (($code[1] == $freeCityMethod) ||
                    ($code[1] == $freeInterCityMethod) ||
                    in_array($code[1], $freeInternationalMethod) ||
                    ($shippingAmount == 0.000)) {
                    $isFreeShippingChoosen = TRUE;
                }
            }




            //Determine who should pay the price for the shipment

            if ( ($isEnabled && ($order->getSubtotal() >= (float) $freeMethodSubtotal) && $isFreeShippingChoosen) ||
                  $shippingAmount == 0.000 || $address->getCountryId() != 'BG') {
                $saveSpeedyData->setPayerType(ParamCalculation::PAYER_TYPE_SENDER);  //SENDER 
            } else {
                $saveSpeedyData->setPayerType(ParamCalculation::PAYER_TYPE_RECEIVER);  //RECEIVER 
            }




            //Check to see if the delivery is send to a Speedy office


            if ($address->getSpeedyOfficeId()) {
                $saveSpeedyData->setPickFromOffice(1);
                $saveSpeedyData->setOfficeId($address->getSpeedyOfficeId());
            }


            $saveSpeedyData->setShippingCosts((float) number_format($shippingCosts, 2));

            $saveSpeedyData->setSendForShipping(0);

            try {
                $transactionSave = Mage::getModel('core/resource_transaction');
                $transactionSave->addObject($saveSpeedyData);
                $transactionSave->save();

                //$saveSpeedyData->save();


                $session->unsSpeedyCurrentExactTimeMethod();
                $session->unsSpeedyExactHour();
                $session->unsSpeedyExactMinutes();
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'speedyLog.log');
                $transactionSave->rollback();
            }
        }
    }

    /**
     * This method saves Speedy specific data about the current order, after 
     * succesful completition of the checkout process in the backend.
     * @param type $evt
     */
    public function saveOrderAdminCheckout($evt) {

        if (!Mage::app()->getStore()->isAdmin() || !Mage::getDesign()->getArea() == 'adminhtml') {
            return;
        }
        $order = $evt->getOrder();
        $address = $order->getShippingAddress();
        $shippingAmount = $order->getShippingAmount();
        $session = Mage::getSingleton('checkout/session');
        $paymentMethod = $order->getPayment()->getMethodInstance()->getInfoInstance()->getMethod();
        $carrierCode = $order->getShippingCarrier()->getCarrierCode();

        //Check to see if Speedy is choosen to be the courier
        if ($carrierCode == "speedyshippingmodule") {


            $isEnabled = Mage::getStoreConfig('carriers/speedyshippingmodule/free_shipping_enable');


            $freeMethodSubtotal = Mage::getStoreConfig('carriers/speedyshippingmodule/free_shipping_subtotal');


            $subtotal = $order->getSubtotal();


            $saveSpeedyData = Mage::getModel('speedyshippingmodule/saveorder');


            $saveSpeedyData->setOrderId($order->getId());

            if ($session->getSpeedyAddressNote() && strlen(trim($session->getSpeedyAddressNote())) > 0) {
                $saveSpeedyData->setMessage(trim($session->getSpeedyAddressNote()));
            }

            if ($session->getSpeedyExactHour() !== FALSE && $session->getSpeedyExactMinutes() !== FALSE) {

                $hour = $session->getSpeedyExactHour();
                $minutes = $session->getSpeedyExactMinutes();

                /**
                 * Format hour and minutes values, if the user has entered
                 * values like 12:5 => 12:05
                 */
                if (strlen($hour) == 1) {

                    $hour = sprintf('%02d', $hour);
                }

                if (strlen($minutes) == 1) {

                    $minutes = sprintf('%02d', $minutes);
                }
                if (strlen($minutes) > 2) {
                    $minutes = substr($minutes, 0, 2);
                }
                if (strlen($hour) > 2) {
                    $hour = substr($hour, 0, 2);
                }


                $saveSpeedyData
                        ->setFixedTime($hour . $minutes);
            }
            if ($paymentMethod == 'cashondelivery') {
                $saveSpeedyData->setIsCod(1);
            } else {
                $saveSpeedyData->setIsCod(0);
            }

            $shippingMethodCode = $order->getShippingMethod();


            $shippingCosts = $order->getShippingAmount();


            if ($shippingMethodCode) {
                $code = explode('_', $shippingMethodCode);
                $saveSpeedyData->setSpeedyServicetypeId($code[1]);
            }


            $isFreeShippingChoosen = FALSE;

            if ($isEnabled) {

                $freeCityMethod = Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_city');

                $freeInterCityMethod = Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_intercity');

                $freeInternationalMethod = explode(',', Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_international'));

                if (($code[1] == $freeCityMethod) || 
                    ($code[1] == $freeInterCityMethod) ||
                    in_array($code[1], $freeInternationalMethod) ||
                    ($shippingAmount == 0.000)) {
                    $isFreeShippingChoosen = TRUE;
                }
            }




            if ( ($isEnabled && ($order->getSubtotal() >= (float) $freeMethodSubtotal) && $isFreeShippingChoosen) ||
                  $shippingAmount == 0.000 || $address->getCountryId() != 'BG') {
                $saveSpeedyData->setPayerType(ParamCalculation::PAYER_TYPE_SENDER);  //SENDER 
            } else {
                $saveSpeedyData->setPayerType(ParamCalculation::PAYER_TYPE_RECEIVER);  //RECEIVER 
            }


            $shippingMethodCode = $order->getShippingMethod();


            $shippingCosts = $order->getShippingAmount();


            if ($shippingMethodCode) {
                $code = explode('_', $shippingMethodCode);
                $saveSpeedyData->setSpeedyServicetypeId($code[1]);
            }


            if ($address->getSpeedyOfficeId()) {
                $saveSpeedyData->setPickFromOffice(1);
                $saveSpeedyData->setOfficeId($address->getSpeedyOfficeId());
            }


            $saveSpeedyData->setShippingCosts((float) number_format($shippingCosts, 2));

            $saveSpeedyData->setSendForShipping(0);
            try {
                $transactionSave = Mage::getModel('core/resource_transaction');
                $transactionSave->addObject($saveSpeedyData);

                $transactionSave->save();

                //$saveSpeedyData->save();


                $session->unsSpeedyCurrentExactTimeMethod();
                $session->unsSpeedyExactHour();
                $session->unsSpeedyExactMinutes();
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'speedyLog.log');
                $transactionSave->rollback();
            }
        }
    }

    /**
     * This Method is called, when the user click delete tracking number on the 
     * View Shipment page in the backend 
     * @param type $evt
     */
    public function removeBol($evt) {
        $track = $evt->getTrack();
        $isBolCanceled = FALSE;
        $dataHolder = $evt->getDataObject();
        $orderId = $track->getOrderId();
        $trackNumber = $track->getTrackNumber();
        $carrierCode = $track->getCarrierCode();

        $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                ->getCollection()
                ->addFilter('order_id', $orderId, 'eq')
                ->load()
                ->getFirstItem();

        $bolID = $speedyData->getBolId();


        if (($bolID == $trackNumber) && $carrierCode == 'speedyshippingmodule') {

            $this->_initSpeedyService();
            try {
                $this->_speedyEPS->invalidatePicking((float)$bolID);
                $isBolCanceled = TRUE;
            } catch (ServerException $se) {
                Mage::log($se->getMessage(), null, 'speedyLog.log');

                Mage::log("Bol with ID:" . htmlentities($bolID, 'UTF-8', ENT_QUOTES) . "cannot be cancelled", null, 'speedyLog.log');
            } catch (ClientException $ce) {
                Mage::log($ce->getMessage(), null, 'speedyLog.log');
            }
        }

        if ($isBolCanceled) {
            $speedyData->setBolId(null);
            $speedyData->setBolCreatedDay(null);
            $speedyData->setBolCreatedMonth(null);
            $speedyData->setBolCreatedYear(null);
            $speedyData->setBolCreatedTime(null);
            $speedyData->setBolDatetime(null);

            $transactionSave = Mage::getModel('core/resource_transaction');

            try {
                $transactionSave->addObject($speedyData);
                $transactionSave->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'speedyLog.log');
                $transactionSave->rollback();
            }
        }
    }

    /**
     * This method is used to change the templates of various blocks in the
     * admin area.
     * @param type $evt
     */
    public function changeWidgetTemplate($evt) {
        $block = $evt->getBlock();
        /**
         * Changes the template of the shipping methods in the admin in order to
         * attach additional javascript events to input elements, but also to
         * alter the input elements themselves.
         */
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form) {
            // consider getting the template name from configuration
            $template = 'speedy_speedyshipping/sales/order/create/shipping/method/form.phtml';
            $block->setTemplate($template);
        }

        
        if($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses){
            $template = 'speedy_speedyshipping/customer/edit/tab/addresses.phtml';
            $block->setTemplate($template);
        }


        /**
         * Changes the address form in the admin area, so that in conforms to
         * the Speedy stadard.
         */
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Create_Form_Address) {
            $template = 'speedy_speedyshipping/sales/order/create/form/address.phtml';
            $block->setTemplate($template);
        }

        /**
         * Add two buttons (Print bill of lading and Cancel bill of lading) to
         * the view shipment page.
         */
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View_Items) {

            $layout = Mage::getSingleton('core/layout');

            $billOfLadingBlock = $layout->createBlock('speedyshippingmodule/adminhtml_billoflading');
            $html = $billOfLadingBlock->toHtml();
            $template = 'speedy_speedyshipping/billoflading.phtml';
            $block->append($billOfLadingBlock);
        }


        /**
         * Change the totals template in the admin create/edit order page. This is
         * neccessary in order to present correct calculations to the customer. 
         * The main reason behind the need of this change is driven by the 
         * fixed hour calculation.
         */
        if ($block instanceof Speedy_Speedyshipping_Block_Adminhtml_Sales_Order_Create_Totals) {
            $template = 'speedy_speedyshipping/sales/order/create/totals.phtml';
            $block->setTemplate($template);
        }

        /**
         * Display the value of fixed hour (if any) on the view order details
         * page.
         */
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View_Tab_Info) {
            $order = $block->getOrder();
            $shippingMethod = $order->getShippingMethod();
            $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                    ->getCollection()
                    ->addFilter('order_id', $order->getId(), 'eq')
                    ->load()
                    ->getFirstItem();

            $shippingDescription = $order->getShippingDescription();

            if ($speedyData->getFixedTime()) {
                $fixedHour = $speedyData->getFixedTime();

                $hour = substr($fixedHour, 0, 2);
                $minutes = substr($fixedHour, 2);
                $trans = 
                // Add fixed hour to the existing description
                $shippingDescription = $shippingDescription . ' ('.$block->__('fixed hour').': ' . $hour . ':' . $minutes . ')';
                $order->setShippingDescription($shippingDescription);
            }
        }

        /**
         * Display the value of fixed hour (if any) on the view shipment details
         * page.
         */
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View_Form) {
            $order = $block->getOrder();
            $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                    ->getCollection()
                    ->addFilter('order_id', $order->getId(), 'eq')
                    ->load()
                    ->getFirstItem();

            $shippingDescription = $order->getShippingDescription();

            if ($speedyData->getFixedTime()) {
                $fixedHour = $speedyData->getFixedTime();

                $hour = substr($fixedHour, 0, 2);
                $minutes = substr($fixedHour, 2);

                // Add fixed hour to the existing description
                $shippingDescription = $shippingDescription . ' ('.$block->__('fixed hour').': ' . $hour . ':' . $minutes . ')';
                $order->setShippingDescription($shippingDescription);
            }
        }
    }

    public function augmentBlockOutput($evt) {
        $block = $evt->getBlock();

        if ($block instanceof Mage_Sales_Block_Order_Info) {
            $order = $block->getOrder();
            $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                    ->getCollection()
                    ->addFilter('order_id', $order->getId(), 'eq')
                    ->load()
                    ->getFirstItem();

            $shippingDescription = $order->getShippingDescription();

            if ($speedyData->getFixedTime()) {
                $fixedHour = $speedyData->getFixedTime();

                $hour = substr($fixedHour, 0, 2);
                $minutes = substr($fixedHour, 2);

                $shippingDescription = $shippingDescription . ' ('.$block->__('fixed hour').': ' . $hour . ':' . $minutes . ')';
                $order->setShippingDescription($shippingDescription);
            }
        }
    }
    
    
    public function customerAdminSaveBefore($evt){
        $customer = $evt->getCustomer();
        
        $addressCollection = $customer->getAddressesCollection();
        
        foreach($addressCollection as $address){
            $addressData = $address->getData();
            unset($addressData['_deleted']);
            $address->setData($addressData);
        }
    }

    protected function _initSpeedyService() {
        $speedyUtil = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'util' . DS . 'Util.class.php';
        $speedyEPSFacade = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'EPSFacade.class.php';
        $speedyEPSImplementation = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'soap' . DS . 'EPSSOAPInterfaceImpl.class.php';
        $speedyResultSite = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ResultSite.class.php';
        $speedyAddressNomen = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'AddrNomen.class.php';



        require_once $speedyUtil;
        require_once $speedyEPSFacade;
        require_once $speedyEPSImplementation;
        require_once $speedyResultSite;
        require_once $speedyAddressNomen;


        $user = Mage::getStoreConfig('carriers/speedyshippingmodule/username');
        $pass = Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/speedyshippingmodule/password'));

        if (!$user || !$pass) {
            return false;
        }

        try {

            $this->_speedyEPSInterfaceImplementaion =
                    new EPSSOAPInterfaceImpl(Mage::getStoreConfig('carriers/speedyshippingmodule/server'));

            $this->_speedyEPS = new EPSFacade($this->_speedyEPSInterfaceImplementaion, $user, $pass);
            $this->_speedySessionId = $this->_speedyEPS->getResultLogin();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'speedyLog.log');
            exit();
        }
    }

    public function validateAddress($address) {
        $paramAddress = new ParamAddress();

        $paramAddress->setSiteId($address['city_id']);
        $paramAddress->setSiteName($address['city']);
        $paramAddress->setPostCode($address['postcode']);
        $paramAddress->setFrnAddressLine1($address['address_1']);
        $paramAddress->setFrnAddressLine2($address['address_2']);
        $paramAddress->setCountryId($address['country_id']);
        $paramAddress->setStateId($address['state_id']);

        try {
            $valid = $this->_speedyEPS->validateAddress($paramAddress, 0);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Invalid post code for specified country') || strpos($e->getMessage(), 'Invalid post code for site') || strpos($e->getMessage(), 'VALUE_OUT_OF_RANGE_ADDRESS_FIELD')) {
                $valid = Mage::helper('speedyshippingmodule')->__("Please enter a valid postcode");
            } else {
                $valid = Mage::helper('speedyshippingmodule')->__("Please enter a valid address");
            }
        }

        return $valid;
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function restrictPaymentsCd($observer)
    {
        if ($observer->getEvent()->hasQuote()) {
            $activeCurrencyCode = Mage::getSingleton('checkout/session')->getSpeedyActiveCurrencyCode();
            $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            $shippingMethod = $observer->getEvent()->getQuote()->getShippingAddress()->getShippingMethod();
            $code = explode('_', $shippingMethod);

            if ($code[0] == 'speedyshippingmodule' && $paymentMethod == 'cashondelivery' && !$activeCurrencyCode) {
                $observer->getEvent()->getResult()->isAvailable = false;
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function collectTotals($observer)
    {
        if (Mage::getStoreConfigFlag('carriers/speedyshippingmodule/invoice_courier_sevice_as_text') && $observer->getEvent()->hasQuote()) {
            $shippingAddress = $observer->getEvent()->getQuote()->getShippingAddress();
            $code = explode('_', $shippingAddress->getShippingMethod());

            if ($code[0] == 'speedyshippingmodule') {
                $shipping_amount = $shippingAddress->getShippingAmount();
                $base_shipping_amount = $shippingAddress->getBaseShippingAmount();
                $grant_total = $shippingAddress->getGrandTotal();
                $base_grant_total = $shippingAddress->getBaseGrandTotal();
                $shipping_description = $shippingAddress->getShippingDescription();
                $shipping_description = $shippingAddress->getShippingDescription();

                $shippingAddress->setShippingDescription($shipping_description . ' (' . Mage::helper('core')->currency($shipping_amount, true, false) . ')');
                $shippingAddress->setGrandTotal($grant_total - $shipping_amount);
                $shippingAddress->setBaseGrandTotal($base_grant_total - $base_shipping_amount);
                $shippingAddress->setShippingInclTax(0.00);
                $shippingAddress->setBaseShippingInclTax(0.00);
                $shippingAddress->setShippingAmount(0.00);
                $shippingAddress->setBaseShippingAmount(0.00);
            }
        }
    }
}

?>
