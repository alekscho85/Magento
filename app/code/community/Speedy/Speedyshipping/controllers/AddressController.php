<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Speedy_SpeedyShipping_Checkout_OnepageController
 *
 * @author killer
 */
//require_once 'Mage/Checkout/controllers/OnepageController.php';

class Speedy_Speedyshipping_AddressController extends Mage_Core_Controller_Front_Action {

protected $_speedyEPSInterfaceImplementaion;
    protected $_speedyEPS;
    protected $_speedySessionId;
    protected $_city_id;
    protected $_addressModel;

    
    function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response) {
        parent::__construct($request, $response);
        $this->_addressModel = Mage::getModel('speedyshippingmodule/autocomplete_address');
    }

    protected function _getShippingMethodsHtml() {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

      public function getSiteAction() {
       $result = $this->_addressModel->getSite();
       
       if(isset($result)){
           //echo $result;
           $this->getResponse()->setBody($result);
       }
    }
    
    //TODO add response output
    public function getOfficesAction() {
       $result = $this->_addressModel->getOffices();
       
       if(isset($result)){
           //echo $result;
           $this->getResponse()->setBody($result);
       }
    }
    
        public function getQuarterAction() {
        $result = $this->_addressModel->getQuarter();
       
       if(isset($result)){
           //echo $result;
           $this->getResponse()->setBody($result);
       }
    }

    public function getStreetsAction() {
        $result = $this->_addressModel->getStreets();
       
       if(isset($result)){
           //echo $result;
           $this->getResponse()->setBody($result);
       }
    }

    public function getStatesAction() {
        $result = $this->_addressModel->getStates();
       
       if(isset($result)){
           $this->getResponse()->setBody($result);
       }
    }
    
    public function getCountriesAction() {
        $result = $this->_addressModel->getCountries();
       
       if(isset($result)){
           $this->getResponse()->setBody($result);
       }
    }
    
     public function getBlockAction() {
        $result = $this->_addressModel->getBlock();
       
       if(isset($result)){
           //echo $result;
           $this->getResponse()->setBody($result);
       }
    }
    

    
}

?>
