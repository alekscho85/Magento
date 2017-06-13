<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Data
 *
 * @author killer
 */
class Speedy_Speedyshipping_Helper_Data extends Mage_Core_Helper_Abstract {
    //put your code here

    protected $_speedyEPS = null;

    protected $_speedyEPSInterfaceImplementaion = null;

    protected $_speedySessionId = null;

     protected function _initSpeedyService() {
        $speedyUtil = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'util' . DS . 'Util.class.php';
        $speedyEPSFacade = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'EPSFacade.class.php';
        $speedyEPSImplementation = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'soap' . DS . 'EPSSOAPInterfaceImpl.class.php';
        $speedyResultSite = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ResultSite.class.php';
        $speedyAddressNomen = Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'AddrNomen.class.php';


        require_once $speedyUtil;
        require_once $speedyEPSFacade;
        require_once $speedyEPSImplementation;
        require_once $speedyResultSite;
        require_once $speedyAddressNomen;


        $user = Mage::getStoreConfig('carriers/speedyshippingmodule/username');
        $pass = Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/speedyshippingmodule/password'));

        if (!$user || !$pass) {
            return false;
        }

        try {

            $this->_speedyEPSInterfaceImplementaion =
                    new EPSSOAPInterfaceImpl(Mage::getStoreConfig('carriers/speedyshippingmodule/server'));

            $this->_speedyEPS = new EPSFacade($this->_speedyEPSInterfaceImplementaion, $user, $pass);
            $this->_speedySessionId = $this->_speedyEPS->getResultLogin();
        } catch (ServerException $se) {
            throw new Exception($se->getMessage());
        }
    }

    public function checkReturnVoucherRequested($bol_id) {
        $this->_initSpeedyService();
        $voucherRequested = false;

        try {
            $pickingExtendedInfo = $this->_speedyEPS->getPickingExtendedInfo((float)$bol_id);

            if (!is_null($pickingExtendedInfo->getReturnVoucher()) && ($pickingExtendedInfo->getReturnVoucher() instanceof ResultReturnVoucher)) {
                $voucherRequested = true;
            }
        } catch (Exception $e) {
            throw new Exception($se->getMessage());
        }

        return $voucherRequested;
    }
}

?>
