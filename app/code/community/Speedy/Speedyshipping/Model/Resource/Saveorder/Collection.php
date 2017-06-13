<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class Speedy_SpeedyShipping_Model_Resource_Saveorder_Collection
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Resource_Saveorder_Collection 
            extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    //put your code here
    protected function _construct()
    {
            $this->_init('speedyshippingmodule/saveorder');
    }
}

?>
