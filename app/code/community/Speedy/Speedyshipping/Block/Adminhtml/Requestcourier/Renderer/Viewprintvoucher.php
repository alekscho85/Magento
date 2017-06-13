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
class Speedy_Speedyshipping_Block_Adminhtml_Requestcourier_Renderer_Viewprintvoucher 
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    //put your code here
    
    public function render(Varien_Object $row)
    {
        $hasPrinter = Mage::getStoreConfig('carriers/speedyshippingmodule/has_label_printer');
        $targetUrl = Mage::helper("adminhtml")->getUrl('speedyshipping/print/printReturnVoucher/',
                                         array(
                                             'order_id'=>(int)$row->getOrderId(),
                                             'has_printer'=>$hasPrinter));
        $funcName = "popWin('".$targetUrl."')";
        $buttonLabel = null;

        $returnVoucherRequested = Mage::helper('speedyshippingmodule')->checkReturnVoucherRequested($row->getBolId());

        if ($returnVoucherRequested) {
            $buttonLabel = $this->__("Print return voucher");

         return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => $buttonLabel,
                'onclick' => $funcName
            ))
            ->toHtml();
        }

        return;
    }
}

?>
