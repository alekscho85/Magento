<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Speedy_Speedyshipping_Block_Adminhtml_Requestcourier_Renderer_Datecreated
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_Requestcourier_Renderer_Datecreated 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
    {
    public function render(Varien_Object $row)
    {
        //if($row->getBolCreatedTime()){
        if($row->getBolDatetime()){
            return date('d-m-Y H:i:s', strtotime($row->getBolDatetime()));
        }else{
            return '';
        }
                    
       // }
    }
}
