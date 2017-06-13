<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Button
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_Requestcourier_Renderer_Cancelbutton 
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    //put your code here
    
     public function render(Varien_Object $row)
    {
        //We have not made a courier request, so the bol can still be canceled
        if(!$row->getSendForShipping()){ 
         
        $targetUrl = $this->getUrl('speedyshipping/print/cancelBol',
                                         array(
                                             'order_id'=>(int)$row->getOrderId()));
        $funcName = "setLocation('".$targetUrl."');this.setAttribute('disabled','disabled');this.addClassName('disabled')";
         return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => $this->__("Cancel Bill Of Lading"),
                'onclick' => $funcName
            ))
            ->toHtml();
        }else{
           return $this->__("Courier has been requested"); 
        }
    }
}

?>
