<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Grid
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_Requestcourier_Requestcontainer extends
Mage_Adminhtml_Block_Widget_Grid_Container{
    //put your code here
    
    public function __construct() {
        parent::__construct();
        $this->_controller = 'adminhtml_requestcourier';
        $this->_blockGroup = 'speedyshippingmodule';
        $this->_headerText = $this->__("Speedy Bill of lading");
        //$this->_addButtonLabel = $this->__("Add bill of lading");
        //
        //Remove unnecessary button
        $this->removeButton('add');
        
        
    }
}

?>
