<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PickupForm
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Onepage_Pickupform extends
Mage_Checkout_Block_Onepage_Shipping_Method_Available {

    //protected $_message = null;
    
    
    /**
     * This property holds the user submitted hour (if any)
     * @var type 
     */
    protected $_hour = null;
    
    /**
     * This property holds the user submitted minutes (if any)
     * @var type 
     */
    protected $_minutes = null;
    
    /**
     * This property holds the ID(eg. 112,113 etc.) of the currently selected shipping method 
     * if Speedy has been choose as a courier
     * @var type 
     */
    protected $_selectedMethod = null;
    
    /**
     * This is a boolean property, that indicates whether or not exact hour 
     * has been used or not
     * @var type 
     */
    protected $_isExactHourUsed = null;

    //put your code here
    public function __construct() {



        $currentAction = Mage::app()->getRequest()->getActionName();
        $currentController = Mage::app()->getRequest()->getControllerName();
        $currentRoute = Mage::app()->getRequest()->getRouteName();
        
        $isAdminArea = FALSE;
        
        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml'){
            $isAdminArea = TRUE;
        }

        //We are editing an order here
        if ($isAdminArea && $currentController == 'sales_order_edit' &&
                $currentAction == 'index' && $currentRoute == 'adminhtml') {
            
            $session = Mage::getSingleton('checkout/session');
            $selectedMethod = $session->getQuote()->getShippingAddress()->getShippingMethod();
            $code = explode('_', $selectedMethod);
            
            if ($session->getSpeedyExactHour() && $session->getSpeedyExactMinutes()){
                $this->_hour = $session->getSpeedyExactHour();
                $this->_minutes = $session->getSpeedyExactMinutes();
                $this->_isExactHourUsed= true;
            }
            
            
            
        } else {

            $selectedMethod = $this->getRequest()->getPost('shipping_method', '');
            $session = Mage::getSingleton('checkout/session');
            /*
            if(!$selectedMethod){
                $selectedMethod = $session->getQuote()->getShippingAddress()->getShippingMethod();
            }
            */
            
            
            $code = explode('_', $selectedMethod);

            
            if ($code[0] == 'speedyshippingmodule') {
                
                if ($this->getRequest()->getParam('speedy_exact_hour_speedyshippingmodule_' . (int) $code[1])) {
                    $this->_selectedMethod = $code[1];
                    
                    

                    if ($this->getRequest()->getParam('speedy_exact_hour') !== FALSE) {
                        $this->_hour = (int) $this->getRequest()->getParam('speedy_exact_hour');

                        if (strlen($this->_hour) == 1) {

                            $this->_hour = sprintf('%02d', $this->_hour);
                        }
                        
                    }

                    if ($this->getRequest()->getParam('speedy_exact_minutes') !== FALSE) {
                        $this->_minutes = (int) $this->getRequest()->getParam('speedy_exact_minutes');

                        if (strlen($this->_minutes) == 1) {
                            $this->_minutes = sprintf('%02d', $this->_minutes);
                        }
                    }
                    

                } else if ($session->getSpeedyExactHour() && $session->getSpeedyExactMinutes()) {
                    //$orderData->setSpeedyCurrentExactTimeMethod($session->getSpeedyCurrentExactTimeMethod());
       
                    if ($session->getSpeedyExactHour() && $session->getSpeedyExactMinutes()) {
                        $this->_hour = $session->getSpeedyExactHour();
                        $this->_minutes = $session->getSpeedyExactMinutes();
                        $this->_isExactHourUsed= true;
                        // $session->unsSpeedyExactHour();
                        //$session->unsSpeedyExactMinutes();
                    }
                }
            }
        }

        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {
            $this->setData('area', 'adminhtml');
            $this->setTemplate('speedy_speedyshipping/pickupform.phtml');
        } else {
            $this->setTemplate('speedy_speedyshipping/checkout/onepage/shipping_method/pickupform.phtml');
        }
        
        //$this->isExactHourUsed();
    }
    
    
    
    protected function isExactHourUsed(){
        $currentAction = Mage::app()->getRequest()->getActionName();
        $currentController = Mage::app()->getRequest()->getControllerName();
        $currentRoute = Mage::app()->getRequest()->getRouteName();
        $request = $this->getRequest();

        

        if ($request->getParam('speedy_exact_hour') !== FALSE &&
                strlen($request->getParam('speedy_exact_hour') !== FALSE) > 0 &&
                $request->getParam('speedy_exact_minutes') !== FALSE &&
                strlen($request->getParam('speedy_exact_minutes'))) {
            $this->_isExactHourUsed= true;
        }
    }

}

?>
