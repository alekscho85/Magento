<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Vieworder
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_Requestcourier_Renderer_Vieworder 
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    //put your code here
    
    public function render(Varien_Object $row)
    {
        
        $targetUrl = Mage::helper("adminhtml")->getUrl('adminhtml/sales_order/view',
                                         array(
                                             'order_id'=>(int)$row->getOrderId()));
        
        $windowName = 'view_related_order';
        $params = 'scrollbars=1';
        $funcName = "popWin('".$targetUrl."','".$windowName."','".$params."')";
        
         return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => $this->__("View order"),
                'onclick' => $funcName
            ))
            ->toHtml();
    }
}

?>
