<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Speedy_SpeedyShiping_Model_Resource_Saveorder
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Resource_Saveorder extends
Mage_Core_Model_Mysql4_Abstract   
{
    //put your code here
    
    public function _construct() {
        $this->_init('speedyshippingmodule/saveorder', 'speedy_order_id');
    }
  
}

?>
