<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Checkout_Shipping_Econt extends Mage_Core_Block_Template
{
    protected $_session;
    protected $_receiver_address;

    protected function _getSession($key = null)
    {
        if (!$this->_session) {
            if (Mage::getSingleton('checkout/session')->hasExtensaEcont()) {
                $this->_session = Mage::getSingleton('checkout/session')->getExtensaEcont();
            }
        }

        if ($this->_session && $key) {
            if (isset($this->_session[$key])) {
                return $this->_session[$key];
            } else {
                return null;
            }
        }

        return $this->_session;
    }

    public function getReceiverAddress()
    {
        if (!$this->_receiver_address) {
            if ($this->_getSession('receiver_address')) {
                $this->_receiver_address = $this->_getSession('receiver_address');
            } else {
                $this->_receiver_address['city'] = '';
                $this->_receiver_address['city_id'] = '';
                $this->_receiver_address['post_code'] = '';
                $this->_receiver_address['quarter'] = '';
                $this->_receiver_address['street'] = '';
                $this->_receiver_address['street_num'] = '';
                $this->_receiver_address['other'] = '';
                $this->_receiver_address['company'] = '';
                $this->_receiver_address['shipping_to'] = '';
            }
        }

        return $this->_receiver_address;
    }

    public function getSenderAddresses()
    {
        return unserialize(Mage::helper('extensa_econt')->getStoreConfig('address'));
    }

    public function getSenderAddress()
    {
        $addresses = $this->getSenderAddresses();
        reset($addresses);
        return current($addresses);
    }

    public function getSenderPostcode()
    {
        $address = $this->getSenderAddress();

        if ($address) {
            return $address['post_code'];
        } else {
            return '';
        }
    }

    public function getCd()
    {
        return (Mage::helper('extensa_econt')->getStoreConfigFlag('cd') && Mage::getStoreConfigFlag('payment/extensa_econt/active'));
    }

    public function getCdPayment()
    {
        if (!$this->getCd()) {
            return false;
        } elseif (isset($this->_session['cd_payment'])) {
            return $this->_getSession('cd_payment');
        } else {
            return true;
        }
    }

    public function getToOffice()
    {
        if (!Mage::helper('extensa_econt')->getStoreConfigFlag('to_office') && !$this->getToDoor()) {
            return true;
        } else {
            return Mage::helper('extensa_econt')->getStoreConfigFlag('to_office');
        }
    }

    public function getToDoor()
    {
        return Mage::helper('extensa_econt')->getStoreConfigFlag('to_door');
    }

    public function getToAps()
    {
        return Mage::helper('extensa_econt')->getStoreConfigFlag('to_aps');
    }

    public function getShippingTo()
    {
        if ($this->_getSession('shipping_to')) {
            $shipping_to = $this->_getSession('shipping_to');
        } elseif (!empty($this->_receiver_address['shipping_to'])) {
            $shipping_to = $this->_receiver_address['shipping_to'];
        }

        if (empty($shipping_to) || ($shipping_to == 'DOOR' && !$this->getToDoor())) {
            $shipping_to = 'OFFICE';
        }

        if ($shipping_to == 'OFFICE' && !$this->getToOffice() && $this->getToDoor()) {
            $shipping_to = 'DOOR';
        }

        if (($shipping_to == 'OFFICE' || $shipping_to == 'DOOR') && !$this->getToOffice() && !$this->getToDoor() && $this->getToAps()) {
            $shipping_to = 'APS';
        }

        if ($shipping_to == 'APS' && !$this->getToAps()) {
            if ($this->getToOffice()) {
                $shipping_to = 'OFFICE';
            } elseif ($this->getToDoor()) {
                $shipping_to = 'DOOR';
            }
        }

        return $shipping_to;
    }

    public function getCities()
    {
        return Mage::getModel('extensa_econt/city')->getCollection()->addOffices()->setDeliveryType('to_office')->setAps(0);
    }

    public function getCitiesAps()
    {
        return Mage::getModel('extensa_econt/city')->getCollection()->addOffices()->setDeliveryType('to_office')->setAps(1);
    }

    public function getOffice()
    {
        if ($this->_getSession('office_id')) {
            return Mage::getModel('extensa_econt/office')->load($this->_getSession('office_id'));
        } elseif (!empty($this->_receiver_address['office_id'])) {
            return Mage::getModel('extensa_econt/office')->load($this->_receiver_address['office_id']);
        } else {
            return false;
        }
    }

    public function getOfficeAps()
    {
        if ($this->_getSession('office_aps_id')) {
            return Mage::getModel('extensa_econt/office')->load($this->_getSession('office_aps_id'));
        } elseif (!empty($this->_receiver_address['office_aps_id'])) {
            return Mage::getModel('extensa_econt/office')->load($this->_receiver_address['office_aps_id']);
        } else {
            return false;
        }
    }

    public function getOffices()
    {
        if ($this->getOffice() && $this->getOffice()->getCityId()) {
            return Mage::getModel('extensa_econt/office')->getCollection()->setCityId($this->getOffice()->getCityId())->setDeliveryType('to_office')->setAps(0);
        } elseif (!empty($this->_receiver_address['office_city_id'])) {
            return Mage::getModel('extensa_econt/office')->getCollection()->setCityId($this->_receiver_address['office_city_id'])->setDeliveryType('to_office')->setAps(0);
        } elseif (!empty($this->_receiver_address['city_id'])) {
            return Mage::getModel('extensa_econt/office')->getCollection()->setCityId($this->_receiver_address['city_id'])->setDeliveryType('to_office')->setAps(0);
        } else {
            return array();
        }
    }

    public function getOfficesAps()
    {
        if ($this->getOfficeAps() && $this->getOfficeAps()->getCityId()) {
            return Mage::getModel('extensa_econt/office')->getCollection()->setCityId($this->getOfficeAps()->getCityId())->setDeliveryType('to_office')->setAps(1);
        } elseif (!empty($this->_receiver_address['office_city_aps_id'])) {
            return Mage::getModel('extensa_econt/office')->getCollection()->setCityId($this->_receiver_address['office_city_aps_id'])->setDeliveryType('to_office')->setAps(1);
        } elseif (!empty($this->_receiver_address['city_id'])) {
            return Mage::getModel('extensa_econt/office')->getCollection()->setCityId($this->_receiver_address['city_id'])->setDeliveryType('to_office')->setAps(1);
        } else {
            return array();
        }
    }

    public function getDc()
    {
        if ($this->getDcCp()) {
            return false;
        } else {
            return Mage::helper('extensa_econt')->getStoreConfigFlag('dc_cp');
        }
    }

    public function getDcCp()
    {
        return Mage::helper('extensa_econt')->getStoreConfigFlag('dc_cp');
    }

    public function getInvoiceBeforeCd()
    {
        return Mage::helper('extensa_econt')->getStoreConfigFlag('invoice_before_cd');
    }

    private function _getDisposition()
    {
        return unserialize(Mage::helper('extensa_econt')->getStoreConfig('disposition'));
    }

    public function getPayAfterAccept()
    {
        if ($this->getPayAfterTest()) {
            return false;
        } else {
            $disposition = $this->_getDisposition();
            return !empty($disposition['pay_after_accept']);
        }
    }

    public function getPayAfterTest()
    {
        $disposition = $this->_getDisposition();
        return !empty($disposition['pay_after_test']);
    }

    public function getPartialDelivery()
    {
        return (Mage::helper('extensa_econt')->getStoreConfigFlag('partial_delivery') && Mage::helper('checkout/cart')->getSummaryCount() > 1);
    }

    public function getPriorityTime()
    {
        return Mage::helper('extensa_econt')->getStoreConfigFlag('priority_time');
    }

    public function getPriorityTimeCb()
    {
        if (isset($this->_session['priority_time_cb'])) {
            return $this->_getSession('priority_time_cb');
        } else {
            return false;
        }
    }

    public function getPriorityTimeTypeId()
    {
        if ($this->_getSession('priority_time_type_id')) {
            return $this->_getSession('priority_time_type_id');
        } else {
            return 'BEFORE';
        }
    }

    public function getPriorityTimeHourId()
    {
        if ($this->_getSession('priority_time_hour_id')) {
            return $this->_getSession('priority_time_hour_id');
        } else {
            return '';
        }
    }

    public function getDeliveryDay()
    {
        return Mage::helper('extensa_econt')->getStoreConfigFlag('delivery_day');
    }

    public function getDeliveryDays()
    {
        $results_data = array();
        $results_data['priority_date'] = '';
        $results_data['delivery_days'] = array();
        $results_data['error'] = false;

        if ($this->_getSession('delivery_day_id')) {
            $results_data['delivery_day_id'] = $this->_getSession('delivery_day_id');
        } else {
            $results_data['delivery_day_id'] = '';
        }

        if ($this->getDeliveryDay()) {
            return Mage::helper('extensa_econt')->getDeliveryDays($results_data);
        } else {
            return $results_data;
        }
    }

    public function getExpressCityCourier()
    {
        if ((count($this->getSenderAddresses()) == 1) && ($this->getSenderPostcode() == $this->_receiver_address['post_code'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getExpressCityCourierCb()
    {
        if (isset($this->_session['express_city_courier_cb'])) {
            return $this->_getSession('express_city_courier_cb');
        } else {
            return false;
        }
    }

    public function getExpressCityCourierE()
    {
        if ($this->_getSession('express_city_courier_e')) {
            return $this->_getSession('express_city_courier_e');
        } else {
            return 'e1';
        }
    }

    public function getValidateUrl()
    {
        return Mage::getUrl('extensa_econt/validate', array('_secure' => true));
    }

    public function getError()
    {
        if ($this->_getSession('error')) {
            return $this->_getSession('error');
        } else {
            return false;
        }
    }
}
