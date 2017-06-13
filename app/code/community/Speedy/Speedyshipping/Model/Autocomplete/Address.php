<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Address
 *
 * @author killer
 */
class Speedy_Speedyshipping_Model_Autocomplete_Address extends Mage_Core_Model_Abstract {

    //put your code here

    protected $_speedyEPSInterfaceImplementaion;
    protected $_speedyEPS;
    protected $_speedySessionId;
    protected $_city_id;
    protected $_request;

    function __construct() {
        $this->_request = Mage::app()->getRequest();
        $this->_initSpeedyService();
    }

    /**
     * This method loads information about particular site(s), based either on 
     * user input or when the customer is editing an existing address on siteID.
     * 
     * @param type $siteID
     * @return boolean
     */
    public function getSite($siteID = null) {
        $session = Mage::getSingleton('checkout/session');
        $cityName = $this->_request->getParam('term');
        $countryId = (int)$this->_request->getParam('countryid');
        $countryIso = $this->_request->getParam('countryiso');
        //$cityName = Mage::helper('speedyshippingmodule/transliterate')->transliterate($cityName);
        $lang = Mage::helper('speedyshippingmodule/transliterate')->getLanguage($cityName);
        if ($countryIso != 'BG') {
            $lang = 'EN';
        }
        //$city = strtoupper($address->getCity());
        try {
            //Customer is editing an existing address
            if (!is_null($siteID)) {
                $sites = $this->_speedyEPS->getSiteById($siteID);
            } else {
                require_once Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ParamFilterSite.class.php';
                $paramFilterSite = new ParamFilterSite();
                $paramFilterSite->setSearchString($cityName);

                if ($countryId) {
                    $paramFilterSite->setCountryId($countryId);
                }

                $sites = $this->_speedyEPS->listSitesEx($paramFilterSite, $lang);
                // $sites = $this->_speedyEPS->listSites(null, $cityName, $lang);
            }
        } catch (ServerException $se) {
            Mage::log($se->getMessage(),null,'speedyLog.log');
        }
        if (isset($sites)) {
            $tpl = array();

            if (count($sites) == 1 && is_null($siteID)) {
                if (empty($countryId) || (!empty($countryIso) && $countryIso == 'BG')) {
                    $label = $sites[0]->getSite()->getType() . ' ' . $sites[0]->getSite()->getName() .
                    ', общ. ' . $sites[0]->getSite()->getMunicipality() . ', обл. ' . $sites[0]->getSite()->getRegion();
                } else {
                    $label = $sites[0]->getSite()->getName();
                }

                $nomenclature = $sites[0]->getSite()->getAddrNomen()->getValue();
                $tpl[] = array('value' => $sites[0]->getSite()->getId(),
                    'label' => $label,
                    'post_code' => $sites[0]->getSite()->getPostCode(),
                    'region' => $sites[0]->getSite()->getRegion(),
                    'is_full_nomenclature' => $nomenclature,
                    'post_code' => $sites[0]->getSite()->getPostCode(),
                    'region' => $sites[0]->getSite()->getRegion());
            }else if(count($sites) == 1){
                $nomenclature = $sites->getAddrNomen()->getValue();
                $tpl[] = array('value' => $sites->getId(),
                    'label' => $sites->getType() . ' ' . $sites->getName() . ', ' .
                    ' общ. ' . $sites->getMunicipality() . ', ' . ' обл. ' . $sites->getRegion(),
                    'post_code' => $sites->getPostCode(),
                    'region' => $sites->getRegion(),
                    'is_full_nomenclature' => $nomenclature,
                    'post_code' => $sites->getPostCode(),
                    'region' => $sites->getRegion());
            }
            else {
                foreach ($sites as $site) {
                    if (empty($countryId) || (!empty($countryIso) && $countryIso == 'BG')) {
                        $label = $site->getSite()->getType() . ' ' . $site->getSite()->getName() .
                        ', общ. ' . $site->getSite()->getMunicipality() . ', обл. ' . $site->getSite()->getRegion();
                    } else {
                        $label = $site->getSite()->getName();
                    }

                    $nomenclature = $site->getSite()->getAddrNomen()->getValue();
                    $tpl[] = array('value' => $site->getSite()->getId(),
                        'label' => $label,
                        'post_code' => $site->getSite()->getPostCode(),
                        'region' => $site->getSite()->getRegion(),
                        'is_full_nomenclature' => $nomenclature,
                        'post_code' => $site->getSite()->getPostCode(),
                        'region' => $site->getSite()->getRegion());
                }
            }
            $jsonData = json_encode($tpl);
            //header("Content-Type: text/html; charset=UTF-8");
            return $jsonData;
        } else {
            return FALSE;
        }
    }

    /**
     * This method loads information about available Speedy office(s) (if any) in 
     * particular site. Please note that in order to retriver anything the
     * currently selected site must have either FULL nomenclature.
     * @return boolean
     */
    public function getOffices() {
        //Retrieve the currently selected site id from the request
        $cityId = (int) $this->_request->getParam('cityid', null);
        $officeName = $this->_request->getParam('term');

        //$officeName = Mage::helper('speedyshippingmodule/transliterate')->transliterate($officeName);
        $lang = Mage::helper('speedyshippingmodule/transliterate')->getLanguage($officeName);
       if($cityId){
        try {
            $offices = $this->_speedyEPS->listOfficesEx($officeName, $cityId, $lang);
        } catch (Exception $e) {
            
        }
       }
        if (isset($offices)) {
            $tpl = array();

            foreach ($offices as $office) {

                
                $label = '';

                $city = '';
                $address = '';
                $note = '';

                $label .= $office->getId() . ' ' . $office->getName();

                /*
                $city .= $office->getAddress()->getSiteType() . ' ' .
                        $office->getAddress()->getSiteName();
*/
                if ($office->getAddress()->getQuarterType()) {


                    $address .= $office->getAddress()->getQuarterType() . ' ';


                    if ($office->getAddress()->getQuarterName()) {
                        $address .= $office->getAddress()->getQuarterName();
                    }
                }



                if ($office->getAddress()->getStreetType()) {


                    $address .= ' ' . $office->getAddress()->getStreetType() . ' ';

                    if ($office->getAddress()->getStreetName()) {
                        $address .= $office->getAddress()->getStreetName();
                    } else if ($office->getAddress()->getStreetName()) {
                        $address .=' ' . $office->getAddress()->getStreetName();
                    }

                    if ($office->getAddress()->getStreetNo()) {
                        $address .= ' № ' . $office->getAddress()->getStreetNo();
                    }
                }

                if ($office->getAddress()->getBlockNo()) {
                    $address .= ' бл. ' . $office->getAddress()->getBlockNo();
                }

                if ($office->getAddress()->getFloorNo()) {
                    $address .= ' ет. ' . $office->getAddress()->getFloorNo();
                }

                if ($office->getAddress()->getApartmentNo()) {
                    $address .= ' ап. ' . $office->getAddress()->getApartmentNo();
                }




                if ($office->getAddress()->getAddressNote()) {

                    $note .= $office->getAddress()->getAddressNote();
                }
/*
                if ($city != '') {

                    $label = $label . ', ' . $city;
                }
*/
                if ($address != '') {
                    $label = $label . ', ' . $address;
                }
                if ($note != '') {
                    $label = $label . ', ' . $note;
                }

                $tpl[] = array('label' =>$office->getId().' '.$office->getName().', '. $office->getAddress()->getFullAddressString(),
                    'value' => $office->getId(),
                    'street_label'=>$label,
                    'site_id' => $office->getAddress()->getResultSite()->getId(),
                    'site_name'=> $office->getAddress()->getResultSite()->getType() . ' ' . $office->getAddress()->getResultSite()->getName() .
                        ', общ. ' . $office->getAddress()->getResultSite()->getMunicipality() . ', обл. ' . $office->getAddress()->getResultSite()->getRegion(),
                    'site_municipality'=>$office->getAddress()->getResultSite()->getMunicipality(),
                    'post_code'=>$office->getAddress()->getPostCode(),
                    'region'=>$office->getAddress()->getResultSite()->getRegion(),
                    'is_full_nomenclature' =>$office->getAddress()->getResultSite()->getAddrNomen()->getValue());
            }

            $jsonData = json_encode($tpl);
            return $jsonData;
        } else {
            return FALSE;
        }
    }

    public function getStates() {
        $session = Mage::getSingleton('checkout/session');
        $stateName = $this->_request->getParam('term');
        $countryId = (int)$this->_request->getParam('countryid');
        //$stateName = Mage::helper('speedyshippingmodule/transliterate')->transliterate($stateName);
        $lang = Mage::helper('speedyshippingmodule/transliterate')->getLanguage($stateName);
        $countryIso = $this->_request->getParam('countryiso');
        if ($countryIso != 'BG') {
            $lang = 'EN';
        }
        //$city = strtoupper($address->getCity());
        try {
            if ($countryId) {
               $states = $this->_speedyEPS->listStates($countryId, $stateName);
            }
        } catch (ServerException $se) {
            Mage::log($se->getMessage(),null,'speedyLog.log');
        }
        if (isset($states)) {
            $tpl = array();

            if ($states) {
                foreach ($states as $state) {
                    $tpl[] = array(
                        'value' => $state->getStateId(),
                        'label' => $state->getName(),
                        'code' => $state->getStateAlpha(),
                        'country_id' => $state->getCountryId()
                    );
                }
            }

            $jsonData = json_encode($tpl);
            //header("Content-Type: text/html; charset=UTF-8");
            return $jsonData;
        } else {
            return FALSE;
        }
    }

    public function getCountries() {
        $countryName = $this->_request->getParam('term');
        $countryIso = $this->_request->getParam('countryiso');
        $lang = Mage::helper('speedyshippingmodule/transliterate')->getLanguage($countryName);
        if ($countryIso != 'BG') {
            $lang = 'EN';
        }
        try {
            require_once Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ParamFilterCountry.class.php';
            $ParamFilterCountry = new ParamFilterCountry();
            if ($countryIso) {
                $ParamFilterCountry->setIsoAlpha2($countryIso);
            }

            $countries = $this->_speedyEPS->listCountriesEx($ParamFilterCountry, $lang);
        } catch (ServerException $se) {
            Mage::log($se->getMessage(),null,'speedyLog.log');
        }

        if (isset($countries)) {
            $tpl = array();

            foreach ($countries as $country) {
                $tpl[$country->getIsoAlpha2()] = array(
                    'id'                   => $country->getCountryId(),
                    'name'                 => $country->getName(),
                    'label'                => $country->getName(),
                    'iso_code_2'           => $country->getIsoAlpha2(),
                    'iso_code_3'           => $country->getIsoAlpha3(),
                    'nomenclature'         => ($country->getSiteNomen()) ? 'FULL' : '',
                    'required_state'       => (int)$country->isRequireState(),
                    'required_postcode'    => (int)$country->isRequirePostCode(),
                    'active_currency_code' => $country->getActiveCurrencyCode(),
                    'is_full_nomenclature' => (int)$country->getSiteNomen(),
                );
            }

            $jsonData = json_encode($tpl);
            //header("Content-Type: text/html; charset=UTF-8");
            return $jsonData;
        } else {
            return FALSE;
        }
    }

    /**
     * This method loads information about living quarters (if any) in 
     * particular site. Please note that in order to retriver anything the
     * currently selected site must have either FULL or PARTIAL nomenclature.
     * @return boolean
     */
    public function getQuarter() {
        // $address = $this->getOnepage()->getQuote()->getShippingAddress();
        $session = Mage::getSingleton('checkout/session');
        $cityId = (int) $this->_request->getParam('cityid');
        $quarterName = $this->_request->getParam('term');
        //$quarterName = Mage::helper('speedyshippingmodule/transliterate')->transliterate($quarterName);
        $lang = Mage::helper('speedyshippingmodule/transliterate')->getLanguage($quarterName);
        $currentSpeedyAddress = $session->getSpeedyAddress();
        $countryIso = $this->_request->getParam('countryiso');
        if ($countryIso != 'BG') {
            $lang = 'EN';
        }
        //$city = strtoupper($address->getCity());
        try {
            $quarters = $this->_speedyEPS->listQuarters($quarterName, $cityId, $lang);
        } catch (ServerException $se) {
            Mage::log($se->getMessage(),null,'speedyLog.log');
        }
        if ($quarters) {
            $tpl = array();

            foreach ($quarters as $quarter) {
                $label = '';
                if ($quarter->getType()) {
                    $label .= $quarter->getType() . ' ';
                }
                if ($quarter->getName()) {
                    $label .= $quarter->getName();
                }

                $tpl[] = array('value' => $quarter->getId(), 'label' => $label);
            }

            $jsonData = json_encode($tpl);
            return $jsonData;
        } else {
            return FALSE;
        }
    }

    
    /**
     * This method loads information about streets (if any) in 
     * particular site. Please note that in order to retriver anything the
     * currently selected site must have either FULL or PARTIAL nomenclature.
     * @return boolean
     */
    public function getStreets() {
        $cityId = (int) $this->_request->getParam('cityid');
        $streetName = $this->_request->getParam('term');
        //$streetName = Mage::helper('speedyshippingmodule/transliterate')->transliterate($streetName);
        $lang = Mage::helper('speedyshippingmodule/transliterate')->getLanguage($streetName);
        $countryIso = $this->_request->getParam('countryiso');
        if ($countryIso != 'BG') {
            $lang = 'EN';
        }
        //Initialize empty array
        $streets = array();
        try {
            $streets = $this->_speedyEPS->listStreets($streetName, $cityId, $lang);
        } catch (Exception $e) {
            
        }
        if ($streets) {
            $tpl = array();

            foreach ($streets as $street) {

                $tpl[] = array('label' => $street->getType() . ' ' . $street->getName(), 'value' => $street->getId());
            }

            $jsonData = json_encode($tpl);
            return $jsonData;
        } else {
            return FALSE;
        }
    }

    /**
     * This method loads information about living blocks (if any) in 
     * particular site. Please note that in order to retriver anything the
     * currently selected site must have either FULL or PARTIAL nomenclature.
     * @return boolean
     */
    public function getBlock() {
        $cityId = (int) $this->_request->getParam('cityid');
        $blockName = $this->_request->getParam('term');
        $lang = Mage::helper('speedyshippingmodule/transliterate')->getLanguage($blockName);
        $countryIso = $this->_request->getParam('countryiso');
        if ($countryIso != 'BG') {
            $lang = 'EN';
        }
        // $streetName = Mage::helper('speedyshippingmodule/transliterate')->transliterate($streetName);
        try {
            $blocks = $this->_speedyEPS->listBlocks($blockName, $cityId, $lang);
        } catch (Exception $e) {
            
        }
        if (isset($blocks)) {
            $tpl = array();

            foreach ($blocks as $block) {

                $tpl[] = array('label' => $block, 'value' => $block);
            }

            $jsonData = json_encode($tpl);
            return $jsonData;
        } else {
            return FALSE;
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


        $user = Mage::getStoreConfig('carriers/speedyshippingmodule/username');
        $pass = Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/speedyshippingmodule/password'));

        if (!$user || !$pass) {
            return false;
        }

        try {

            $this->_speedyEPSInterfaceImplementaion =
                    new EPSSOAPInterfaceImpl(Mage::getStoreConfig('carriers/speedyshippingmodule/server'));

            $this->_speedyEPS = new EPSFacade($this->_speedyEPSInterfaceImplementaion, $user, $pass);
        } catch (ServerException $se) {
            Mage::log($se->getMessage(),null,'speedyLog.log');
        }
    }

}

?>
