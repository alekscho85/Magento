<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Available
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available {

    //put your code here
//TODO add documentation
    protected $_selectedMethod = null;
    protected $_isExactTimeChoosen = null;

    public function __construct() {
        $this->_initSpeedyRequestData();
        parent::__construct();
    }

    protected function _initSpeedyRequestData() {
        if ($this->getRequest()->isPost()) {
            $selectedMethod = $this->getRequest()->getPost('shipping_method', '');

            $code = explode('_', $selectedMethod);

            if ($code[0] == 'speedyshippingmodule') {
                $this->_selectedMethod = $selectedMethod;
                if ($this->getRequest()->getParam('speedy_exact_hour_speedyshippingmodule_' . (int) $code[1])) {
                    $this->_isExactTimeChoosen = true;
                }
            }
        } else {
            $session = Mage::getSingleton('checkout/session');
            $selectedMethod = $session
                    ->getQuote()
                    ->getShippingAddress()
                    ->getShippingMethod();

            $code = explode('_', $selectedMethod);

            if ($code[0] == 'speedyshippingmodule') {
                $this->_selectedMethod = $selectedMethod;
            }
            
           
        }
    }
/*
    public function getAddressShippingMethod() {


        $holder = 'speedy_exact_hour_speedyshippingmodule_';
        
        $isExactHourUsed = false;
        
        if($this->getRequest()->getParam('speedy_exact_hour') !==FALSE &&
                       strlen($this->getRequest()->getParam('speedy_exact_hour') !==FALSE) > 0 &&
                       $this->getRequest()->getParam('speedy_exact_minutes') !==FALSE && 
                       strlen($this->getRequest()->getParam('speedy_exact_minutes'))){
            $isExactHourUsed = true;
        }

        $selectedMethod = parent::getAddressShippingMethod();

        $rates = $this->_rates;
        $code = explode('_', $selectedMethod);

        $serviceMap = array(3=>36, 2=>26);
        $serviceToBeSelected = null;
        $request = $this->_request;

        if ($code[0] == 'speedyshippingmodule') {
            $serviceId = $code[1];
            
           if( ($serviceId == 3 || $serviceId == 2) && $isExactHourUsed){
               
               if(array_key_exists('speedyshippingmodule', $rates)){
                   
                   foreach($rates['speedyshippingmodule'] as $rate){
                       $newServiceTitle = str_replace('ФИКСИРАН ЧАС - ','', $rate->getMethodTitle());
                           $rate->setMethodTitle($newServiceTitle);
                       if($rate->getMethod() == $serviceMap[$serviceId]){
                           
                           $selectedMethod= 'speedyshippingmodule_'.$rate->getMethod();
                           $this->_selectedMethod =  $selectedMethod;
                           
                           
                           //method_title
                       }
                   }
               }
               
           }else if( ($serviceId == 36 || $serviceId == 26) && !$isExactHourUsed){
               if(array_key_exists('speedyshippingmodule', $rates)){
                   
                   foreach($rates['speedyshippingmodule'] as $rate){
                       $newServiceTitle = str_replace('ФИКСИРАН ЧАС - ','', $rate->getMethodTitle());
                       $rate->setMethodTitle($newServiceTitle);
                       if($rate->getMethod() == array_search($serviceId, $serviceMap) ){
                           
                           $selectedMethod= 'speedyshippingmodule_'.$rate->getMethod();
                           $this->_selectedMethod =  $selectedMethod;
                           
                       }
                   }
               }
           }
               return $selectedMethod;
        } else {
            return $selectedMethod;
        }
        return $selectedMethod;
    }
*/
}

?>
