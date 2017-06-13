<?php

class Trackingmore_Detrack_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    { 
        $modelData = Mage::getModel('detrack/track');
        $hashData = Mage::app()->getRequest()->getParam('h');
        if ($hashData) {
            $modelData->loadInfoByHash($hashData);
        }
        Mage::register('model', $modelData);
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Shipment status"));
        $secondDescription = $this->getLayout()->getBlock("breadcrumbs");
        $secondDescription->addCrumb("home", array(
            "label" => $this->__("Home Page"),
            "title" => $this->__("Home Page"),
            "link" => Mage::getBaseUrl()
        ));
        $secondDescription->addCrumb("shipment status", array(
            "label" => $this->__("Shipment status"),
            "title" => $this->__("Shipment status")
        ));
        $this->renderLayout();
    }

    public function popupAction()
    {   
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Shipment status"));
        $this->renderLayout();
    }
}