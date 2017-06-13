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
class Speedy_Speedyshipping_Block_Adminhtml_Sales_Order_Create_Speedy_Form extends
Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract{
    //put your code here
    
    
    /**
     * This variable holds 
     * @var type 
     */
    protected $_speedyData = null;
    
    
    
    public function __construct() {
        parent::__construct();
        $this->_initSpeedyData();
        $this->setTemplate('speedy_speedyshipping/sales/order/create/speedy_form/form.phtml');
    }

    
     
    
    
/*
    public function getForm() {
        
        $test_form = parent::getForm();
        return parent::getForm();
    }
*/
    
//TODO add function description
    /**
     * 
     */
    protected function _initSpeedyData(){
        
        $addressId = (int)$this->getRequest()->getParam('id');
        
        if($addressId){
        
        $speedyAddressModel = Mage::getModel('speedyshippingmodule/saveaddress')
                ->getCollection()
                ->addFilter('magento_address_id', $addressId, 'eq')
                ->load()
                ->getFirstItem();
        }
        if($speedyAddressModel){
            $this->_speedyData = $speedyAddressModel;
        }else{
            $speedyAddressModel = Mage::getModel('speedyshippingmodule/saveaddress');
            $this->_speedyData = $speedyAddressModel;
        }
    }
    

    public function _prepareForm() {
        return '';
    }
}

?>
