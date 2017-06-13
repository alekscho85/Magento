<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CheckcredentialsController
 *
 * @author killer
 */
class Speedy_Speedyshipping_Adminhtml_CheckcredentialsController extends Mage_Adminhtml_Controller_Action {

    protected $_speedyEPSInterfaceImplementaion;
    protected $_speedyEPS;
    protected $_speedySessionId;
    protected $_userName;
    protected $_password;
    protected $_exception = null;

    public function preDispatch() {
        if ($this->getRequest()->isPost()) {
            $this->_userName = $this->getRequest()->getPost('username', null);
            $this->_password = $this->getRequest()->getPost('password', null);
            $this->_initSpeedyService();
        }
    }

    public function checkCredentialsAction() {


        if ($this->getRequest()->isPost()) {

           

            if ($this->_exception) {
                $response = json_encode(array('error' => TRUE));
                Mage::app()->getResponse()->setBody($response);
            } else {

                $response = json_encode(array('ok' => TRUE));
                Mage::app()->getResponse()->setBody($response);
            }
        }
    }

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




        if (!$this->_userName || !$this->_password) {
            return false;
        }

        try {

            $this->_speedyEPSInterfaceImplementaion = new EPSSOAPInterfaceImpl(Mage::getStoreConfig('carriers/speedyshippingmodule/server'));

            $this->_speedyEPS = new EPSFacade($this->_speedyEPSInterfaceImplementaion, $this->_userName, $this->_password);
        
            $this->_speedyEPS->getResultLogin();
        } catch (Exception $e) {

            $this->_exception = $e->getMessage();
            //throw new Exception('An error has occured while connecting Speedy');
        }
    }

}
