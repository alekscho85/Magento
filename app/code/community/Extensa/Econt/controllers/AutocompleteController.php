<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_AutocompleteController extends Mage_Core_Controller_Front_Action
{
    public function cityAction()
    {
        if ($this->getRequest()->getPost('extensa_econt')) {
            $query = $this->getRequest()->getPost('extensa_econt');
        } else {
            $query = $this->getRequest()->getPost('groups');
            $query = current($query['extensa_econt']['fields']['address']['value']);
        }
        $query = $query['city'];
        $limit = $this->getRequest()->getPost('limit', 10);

        $items = Mage::getModel('extensa_econt/city')->getCollection()->setNameFilter($query)->setNameOrder()->setPageSize($limit)->getData();

        $block = $this->getLayout()->createBlock('core/template')
            ->setTemplate('extensa/econt/address/city.phtml')
            ->assign('items', $items);

        $this->getResponse()->setBody($block->toHtml());
    }

    public function quarterAction()
    {
        if ($this->getRequest()->getPost('extensa_econt')) {
            $query = $this->getRequest()->getPost('extensa_econt');
        } else {
            $query = $this->getRequest()->getPost('groups');
            $query = current($query['extensa_econt']['fields']['address']['value']);
        }
        $query = $query['quarter'];
        $city_id = $this->getRequest()->getPost('city_id');
        $limit = $this->getRequest()->getPost('limit', 10);

        $items = Mage::getModel('extensa_econt/quarter')->getCollection()->setNameFilter($query)->setCityFilter($city_id)->setNameOrder()->setPageSize($limit)->getData();

        $block = $this->getLayout()->createBlock('core/template')
            ->setTemplate('extensa/econt/address/quarter.phtml')
            ->assign('items', $items);

        $this->getResponse()->setBody($block->toHtml());
    }

    public function streetAction()
    {
        if ($this->getRequest()->getPost('extensa_econt')) {
            $query = $this->getRequest()->getPost('extensa_econt');
        } else {
            $query = $this->getRequest()->getPost('groups');
            $query = current($query['extensa_econt']['fields']['address']['value']);
        }
        $query = $query['street'];
        $city_id = $this->getRequest()->getPost('city_id');
        $limit = $this->getRequest()->getPost('limit', 10);

        $items = Mage::getModel('extensa_econt/street')->getCollection()->setNameFilter($query)->setCityFilter($city_id)->setNameOrder()->setPageSize($limit)->getData();

        $block = $this->getLayout()->createBlock('core/template')
            ->setTemplate('extensa/econt/address/street.phtml')
            ->assign('items', $items);

        $this->getResponse()->setBody($block->toHtml());
    }
}
