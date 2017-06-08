<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_OfficeController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $results = Mage::getModel('extensa_econt/office')
            ->load($this->getRequest()->getPost('office_id'));

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($results)
        );
    }

    public function bycodeAction()
    {
        $results = Mage::getModel('extensa_econt/office')
            ->load($this->getRequest()->getPost('office_code'), 'office_code');

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($results)
        );
    }

    public function listAction()
    {
        $results = Mage::getModel('extensa_econt/office')
            ->getCollection()
            ->setCityId($this->getRequest()->getPost('city_id'))
            ->setDeliveryType($this->getRequest()->getPost('delivery_type'))
            ->setAps($this->getRequest()->getPost('aps'))
            ->getData();

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($results)
        );
    }
}
