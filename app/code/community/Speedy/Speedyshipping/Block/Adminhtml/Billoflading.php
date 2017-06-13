<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author killer
 */
class Speedy_Speedyshipping_Block_Adminhtml_Billoflading extends Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging {


    /**
     * The URL, used to print a bill of lading
     * @var type 
     */
    protected $_printUrl = null;
    
        
    /**
     * The URL, used to print a return voucher
     * @var type 
     */
    protected $_printReturnVoucher = null;

    /**
     *The URL, used to create bill of laging
     * @var type 
     */
    protected $_createLabeUrl = null;
    
    /**
     * This URL is used to check the closeses available picking date
     * @var type 
     */
    protected $_checkDateUrl = null;
    
    /**
     * The ID of the associated to the shipment  order
     * @var type 
     */
    protected $_orderId = null;
    
    /**
     *This object holds the Speedy specific data, associated with the shipment
     * @var type 
     */
    protected $_speedyData = null;
    
    /**
     *A boolean flag, whether Speedy is the courier ot the currently viewed
     * shipment. If these evaluates to false, the user won't see any module
     * specific UI
     * @var type 
     */
    protected $_isSpeedyCarrier = false;

    protected $_isAbroad = false;
    
    /**
     *A boolean flag, indicating whether the currently viewed shipment has an
     * associated bill of lading 
     * 
     * @var type 
     */
    protected $_hasBOL = false;
    
    /**
     *A boolean flag, indicating whether a courier has been requested for the
     * associated with the shipment bill of lading
     * @var type 
     */
    protected $_isSendForShipping = false;
    
    
    protected $_deferredDays = null;
    
    
    protected $_doesUserHasPermission = true;
    
    
    protected $_optionsBeforePayment = null;

    protected $_parcelsCount = null;
    
    
    /**
     * The purpose of this constructor is to get various request params and 
     * assemble of the urls of various actions inside 
     * Speedy_Speedyshipping_Adminhtml_PrintController
     */
    public function __construct() {
        parent::__construct();
        
        
        if(! Mage::getSingleton('admin/session')
                                ->isAllowed('speedyshippingmodule/print')){
                    $this->_doesUserHasPermission = false;
                    
        }
        
        
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        $orderId = $shipment->getOrderId();
        $hasPrinter = Mage::getStoreConfig('carriers/speedyshippingmodule/has_label_printer');
        $this->_createLabeUrl = $this->getUrl('speedyshipping/print/createLabel', array(
            'order_id' => (int) $orderId,
            'shipment_id' => (int) $shipmentId));

        $this->_printUrl = $this->getUrl('speedyshipping/print/printLabel', array(
            'order_id' => (int) $orderId,
            'shipment_id' => (int) $shipmentId,
            'has_printer' => $hasPrinter));

        $this->_printReturnVoucher = $this->getUrl('speedyshipping/print/printReturnVoucher', array(
            'order_id' => (int) $orderId,
            'shipment_id' => (int) $shipmentId,
            'has_printer' => $hasPrinter));

        $this->_checkDateUrl = $this->getUrl('speedyshipping/print/checkDate', array(
            'order_id' => (int) $orderId,
            'shipment_id' => (int) $shipmentId));


        $this->_speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                ->getCollection()
                ->addFilter('order_id', $orderId, 'eq')
                ->load()
                ->getFirstItem();
        if ($this->_speedyData->getOrderId()) {
            $this->_isSpeedyCarrier = true;
        }
        if ($this->_speedyData->getBolId()) {
            $this->_hasBOL = true;
        }

        if ($this->_speedyData->getSendForShipping()) {
            $this->_isSendForShipping = 1;
        }
        
        
        if($this->_speedyData->getDeferredDeliveryWorkdays()){
            $this->_deferredDays = $this->_speedyData->getDeferredDeliveryWorkdays();
        }

        if ($this->_speedyData->getOptionsBeforePayment()) {
            $this->_optionsBeforePayment = $this->_speedyData->getOptionsBeforePayment();
        } else {
            $this->_optionsBeforePayment = Mage::getStoreConfig('carriers/speedyshippingmodule/options_before_payment');
        }

        if ($this->_speedyData->getParcelsCount()) {
            $this->_parcelsCount = $this->_speedyData->getParcelsCount();
        } else {
            $this->_parcelsCount = 1;
        }

        $order = Mage::getModel('sales/order')->load($orderId);
        $this->_shippingAddress = $order->getShippingAddress();

        if ($this->_shippingAddress->getCountryId() != 'BG') {
            $this->_isAbroad = true;
        }

        $this->setTemplate('speedy_speedyshipping/billoflading.phtml');
    }

    public function getCancelBolButton() {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $frontname = $this->getRequest()->getRouteName();
        $isPopUp = FALSE;
        if ($frontname == 'adminhtml') {

            $isPopUp = 1;
        }
        //If we have already requested a courier, we cannot cancel the bol
        if (!$this->_isSendForShipping) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            $orderId = $shipment->getOrderId();
            $targetUrl = $this->getUrl('speedyshipping/print/cancelBol', array(
                'order_id' => (int) $orderId,
                'shipment_id' => (int) $shipmentId,
                'is_popup' => $isPopUp));
            $funcName = "popWin('" . $targetUrl . "')";
            return $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                'label' => $this->__("Cancel Bill Of Lading"),
                                'onclick' => $funcName
                            ))
                            ->toHtml();
        } else {
            
            return $this->__("Courier has been requested");
        }
    }

    public function getPrintUrl() {
        return $this->_printUrl;
    }
    
    public function getPrintReturnVoucher() {
        return $this->_printReturnVoucher;
    }

    public function getCheckDateUrl(){
       return  $this->_checkDateUrl;
    }

    public function getCreateLabelUrl() {
        return $this->_createLabeUrl;
    }

    public function getShipment() {
        return Mage::registry('current_shipment');
    }

    public function getCreateShipmentButton() {

        //We already have BOL for this shipment
        if ($this->_speedyData->getBolId()) {
            return '';
        } else {

            $label = '';

            return $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                'label' => $this->__("Create Bill of lading"),
                                'onclick' => 'packaging.showWindow()'
                            ))
                            ->toHtml();
        }
    }

    public function getConfigDataJson() {
        $data = parent::getConfigDataJson();
        $newData = json_decode($data);
        $this->getPrintUrl();
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        $orderId = $shipment->getOrderId();

        $createLabelUrl = $this->getUrl('speedyshipping/print/createLabel', array(
            'order_id' => (int) $orderId,
            'shipment_id' => (int) $shipmentId));
        $newData->createLabelUrl = $createLabelUrl;

        return json_encode($newData);
    }

    public function getPrintButton() {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        $hasPrinter = Mage::getStoreConfig('carriers/speedyshippingmodule/has_label_printer');
        $this->_orderId = $shipment->getOrderId();

        if ($hasPrinter) {
            $label = $this->__("Print shipping labels");
        } else {
            $label = $this->__("Print Bill of lading");
        }

        $funcName = "popWin('" . $this->_printUrl . "')";

        return $this->getLayout()
                        ->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => $label,
                            'onclick' => $funcName
                        ))
                        ->toHtml();
    }

    public function getPrintReturnVoucherButton() {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        $this->_orderId = $shipment->getOrderId();

        $returnVoucherRequested = Mage::helper('speedyshippingmodule')->checkReturnVoucherRequested($this->_speedyData->getBolId());

        if ($returnVoucherRequested) {
            $label = $this->__('Print return voucher');

            $funcName = "popWin('" . $this->_printReturnVoucher . "')";

            return $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                'label' => $label,
                                'onclick' => $funcName
                            ))
                            ->toHtml();
        }

        return;
    }

    public function getHeaderText() {
        return $this->__("Print Bill of lading");
    }

    public function getHeaderCssClass() {
        return 'head-shipping-ladding';
    }

}

?>
