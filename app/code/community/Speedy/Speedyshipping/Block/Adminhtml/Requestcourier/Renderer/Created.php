<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Created
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_Requestcourier_Renderer_Created
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    //put your code here
    
    public function render(Varien_Object $row)
    {
        //if($row->getBolCreatedTime()){
            $generatedTimestamp = mktime(0, 0, 0, $row->getBolCreatedMonth(), $row->getBolCreatedDay(), $row->getBolCreatedYear());
            return date('d-m-Y', $generatedTimestamp );
       // }
    }
}

?>
