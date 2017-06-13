<?php

class Speedy_Speedyshipping_Model_Carrier_Shippingmethod extends Mage_Shipping_Model_Carrier_Abstract {

    protected $_code = 'speedyshippingmodule';
    protected $_request = null;
    protected $_rawRequest = null;

    /**
     * A boolean property that indicates whether the current execution context 
     * is the admin area. This is used and set in various methods in this class
     * in order to detemine various request and session parameters.
     * @var type 
     */
    protected $_isAdminArea = false;

    /**
     * Rate result data
     *
     * @var Mage_Shipping_Model_Rate_Result|null
     */
    protected $_result = null;
    protected $_speedyRates = array();

    /**
     * A property that holds the picking data about particular Magento order, in
     * format that Speedy expects. This data is extracted mainly in setUpOrderData()
     * method and is assembled in setPickingData() method.
     * @var type 
     */
    protected $_pickingData;

    /**
     * A property that holds data about the receiver (the customer of the online
     * shop).
     * .This data is extracted either from the Quote object (if the order is created)
     * or from the database (if the order is currently being edited) .The data is 
     * extracted  in setUpOrderData() method and assembled in setReceiverData()
     * method.
     * @var type 
     */
    protected $_receiverData;
    protected $_orderData;
    protected $_checkoutSession;
    protected $_city_id;
    protected $_codAmount = null;
    protected $_speedyServiceInfo = array();
    protected $_CODPrices = array();
    protected $_doesRequestContainExactHour;

    /**
     * Errors placeholder
     *
     * @var array
     */
    protected $_errors = array();
    protected $_speedyEPSInterfaceImplementaion;
    protected $_speedyEPS;
    protected $_speedySessionId;
    protected $_speedyData = null;

    public function __construct() {
        parent::__construct();
        $this->_CODPrices = array();
        $this->_initSpeedyConnection();
        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {
            $this->_isAdminArea = true;
        }
    }

    /**
     * Setup connection to Speedy server
     * @return booleal
     * @throws ServerException
     */
    protected function _initSpeedyConnection() {
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


        $user = $this->getConfigData('username');
        $pass = Mage::helper('core')->decrypt($this->getConfigData('password'));

        if (!$user || !$pass) {
            return false;
        }
        if (!Mage::registry('speedyUser')) {
            Mage::register('speedyUser', $user);
        }
        if (!Mage::registry('speedyPass')) {
            Mage::register('speedyPass', $pass);
        }

        try {

            $this->_speedyEPSInterfaceImplementaion = new EPSSOAPInterfaceImpl(Mage::getStoreConfig('carriers/' . $this->_code . '/server'));

            $this->_speedyEPS = new EPSFacade($this->_speedyEPSInterfaceImplementaion, $user, $pass);

            $this->_speedySessionId = $this->_speedyEPS->getResultLogin();

            //Mage::log('connection established', null, 'speedyLog.log');
            return TRUE;
        } catch (Exception $e) {
            //throw new Exception('An error has occured while connecting Speedy');
            //echo 'An error has occured while connecting Speedy';
            Mage::log($e->getMessage(), null, 'speedyLog.log');
            return FALSE;
        }
    }

    /**
     * Returns the template file location of the form that will be displayed 
     * on the checkout page 
     * @return string
     */
    public function getFormName() {
        return 'speedyshippingmodule/onepage_pickupform';
    }

    /**
     * Collect shipping rates
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return boolean
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        // skip if not enabled

        if (!Mage::getStoreConfig('carriers/' . $this->_code . '/active') || (!Mage::getSingleton('checkout/session')->hasQuote() && !(Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml'))) {
            return false;
        }


        $currentUrlIs = Mage::helper('core/url')->getCurrentUrl();

        $quote = Mage::getModel('checkout/cart')->getQuote();

        if ($quote->getIsMultiShipping()) {

            return;
        }

        $this->setRequest($request);


        $this->_result = $this->_getQuotes();

        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    /**
     * This method shows to Magento, that this carrier has 
     * the ability to track shipments;
     * @return boolean
     */
    public function isTrackingAvailable() {
        return true;
    }

    /**
     * This method initializes various data sources needed for the shipping 
     * calculations
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return \Speedy_SpeedyShipping_Model_Carrier_Shippingmethod
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request) {

        $this->_request = $request;

        $this->_orderData = $this->setUpOrderData();

        $this->_pickingData = $this->setPickingData();

        $this->_receiverData = $this->setUpReceiverData();

        $this->_rawRequest = $request;

        return $this;
    }

    /**
     * This method initializes data, associated with the current order
     * This data can come either from the current quote object during
     * checkout or if you edit an order from the DB.
     * @return \Varien_Object
     */
    protected function setUpOrderData() {
        $orderData = new Varien_Object();
        $session = Mage::getSingleton('checkout/session');

        $orderId = Mage::app()->getRequest()->getParam('order_id');
        //$order = Mage::getModel('sales/order')->load($orderId);

        $currentAction = Mage::app()->getRequest()->getActionName();
        $currentController = Mage::app()->getRequest()->getControllerName();
        $currentRoute = Mage::app()->getRequest()->getRouteName();

        $isAdminArea = FALSE;

        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {

            $address = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getShippingAddress();

            $selectedMethod = Mage::app()->getRequest()->getPost('shipping_method');

            if (!$selectedMethod) {

                $selectedMethod = $address->getShippingMethod();
            }

            $selectedMethodParts = explode('_', $selectedMethod);

            $isAdminArea = TRUE;
        } else {

            $address = Mage::getModel('checkout/cart')->getQuote()->getShippingAddress();
        }


        if ($address->getCustomerAddressId()) {

            $customerAddress = Mage::getModel('customer/address')->load($address->getCustomerAddressId());
        }

        if ($currentController == 'sales_order_edit' && $currentAction == 'start' &&
                $isAdminArea && isset($customerAddress)) {

            $orderData->setReceiverCityId($customerAddress->getSpeedySiteId());
        } else {

            $orderData->setReceiverCityId($address->getSpeedySiteId());
        }

        if (isset($customerAddress)) {

            if (($address->getSpeedyOfficeId() && $customerAddress->getSpeedyOfficeId()) &&
                    ($address->getSpeedyOfficeId() === $customerAddress->getSpeedyOfficeId())) {

                $orderData->setOfficeId($address->getSpeedyOfficeId());
            } else if ($address->getSpeedyOfficeId()) {

                $address->unsSpeedyOfficeId();
            }
        } else {

            if ($address->getSpeedyOfficeId()) {

                $orderData->setOfficeId($address->getSpeedyOfficeId());
            }
        }


        if ($address->getSpeedyQuarterId()) {

            $orderData->setQuarterId($address->getSpeedyQuarterId());
        }

        if ($address->getSpeedyStreetId()) {

            $orderData->setStreetId($address->getSpeedyStreetId());
        }

        if ($address->getSpeedyStreetNumber()) {

            $orderData->setStreetNo($address->getSpeedyStreetNumber());
        }

        if ($address->getSpeedyBlockNumber()) {

            $orderData->setBlockId($address->getSpeedyBlockNumber());
        }

        if ($address->getSpeedyCountryId()) {

            $orderData->setSpeedyCountryId($address->getSpeedyCountryId());
        }

        if ($address->getSpeedyStateId()) {

            $orderData->setSpeedyStateId($address->getSpeedyStateId());
        }

        if ($address->getCountryId()) {

            $orderData->setCountryId($address->getCountryId());
        }

        if ($address->getPostcode()) {
            $orderData->setPostcode($address->getPostcode());
        }

        if ($currentAction == 'saveBilling') {
            $addressData = Mage::app()->getRequest()->getParam('billing');
            $addressId = Mage::app()->getRequest()->getParam('billing_address_id');
        } elseif ($currentAction == 'saveShipping') {
            $addressData = Mage::app()->getRequest()->getParam('shipping');
            $addressId = Mage::app()->getRequest()->getParam('shipping_address_id');
        }

        Mage::getSingleton('checkout/session')->unsSpeedyActiveCurrencyCode();
        if (empty($addressId) && !empty($addressData['active_currency_code'])) {
            $orderData->setSpeedyActiveCurrencyCode($addressData['active_currency_code']);
            Mage::getSingleton('checkout/session')->setSpeedyActiveCurrencyCode($addressData['active_currency_code']);
        } else {
            if (!empty($addressId)) {
                $address = Mage::getModel('customer/address')->load($addressId);
            }

            if ($address->getSpeedyCountryId() || $address->getCountryId()) {
                try {
                    require_once Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ParamFilterCountry.class.php';
                    $ParamFilterCountry = new ParamFilterCountry();
                    if ($address->getSpeedyCountryId()) {
                        $ParamFilterCountry->setCountryId($address->getSpeedyCountryId());
                    } else {
                        $ParamFilterCountry->setIsoAlpha2($address->getCountryId());
                    }
                    $countries = $this->_speedyEPS->listCountriesEx($ParamFilterCountry);
                } catch (ServerException $se) {
                    Mage::log($se->getMessage(),null,'speedyLog.log');
                }

                if (isset($countries) && count($countries) == 1) {
                    $orderData->setSpeedyActiveCurrencyCode($countries[0]->getActiveCurrencyCode());
                    Mage::getSingleton('checkout/session')->setSpeedyActiveCurrencyCode($countries[0]->getActiveCurrencyCode());
                }
            }
        }

        $speedyAdminExactTime = null;

        if ($isAdminArea && Mage::app()->getRequest()->getParam('speedy_exact_hour') !== FALSE &&
                strlen(Mage::app()->getRequest()->getParam('speedy_exact_hour') !== FALSE) > 0 &&
                Mage::app()->getRequest()->getParam('speedy_exact_minutes') !== FALSE &&
                strlen(Mage::app()->getRequest()->getParam('speedy_exact_minutes'))) {

            $speedyAdminExactTime = true;
        }


        if ($session->getSpeedyCurrentExactTimeMethod() && !$isAdminArea) {

            $orderData->setSpeedyCurrentExactTimeMethod($session->getSpeedyCurrentExactTimeMethod());

            if ($session->getSpeedyExactHour() !== FALSE && $session->getSpeedyExactMinutes() !== FALSE) {

                $orderData->setSpeedyExactHour($session->getSpeedyExactHour());
                $orderData->setSpeedyExactMinutes($session->getSpeedyExactMinutes());
            }
        } else if ($isAdminArea && $speedyAdminExactTime) {

            //TODO Check if this is necessary
            $orderData->setSpeedyCurrentExactTimeMethod('test');
            $orderData->setSpeedyExactHour(Mage::app()->getRequest()->getParam('speedy_exact_hour'));
            $orderData->setSpeedyExactMinutes(Mage::app()->getRequest()->getParam('speedy_exact_minutes'));

            $session->setSpeedyExactHour(Mage::app()->getRequest()->getParam('speedy_exact_hour'));
            $session->setSpeedyExactMinutes(Mage::app()->getRequest()->getParam('speedy_exact_minutes'));

            $this->_doesRequestContainExactHour = true;
        } else if ($isAdminArea && !$speedyAdminExactTime &&
                !Mage::app()->getRequest()->getParam('payment') && !$orderId) {


            if ($session->getSpeedyExactHour()) {

                $session->unsSpeedyExactHour();
            }

            if ($session->getSpeedyExactMinutes()) {

                $session->unsSpeedyExactMinutes();
            }
        } else if ($isAdminArea && !$speedyAdminExactTime &&
                Mage::app()->getRequest()->getParam('payment') && !$orderId) {

            if ($session->getSpeedyExactHour()) {

                $session->unsSpeedyExactHour();
            }

            if ($session->getSpeedyExactMinutes()) {

                $session->unsSpeedyExactMinutes();
            }
        } else if ($isAdminArea && $currentController == 'sales_order_edit' &&
                $currentAction == 'start' && $currentRoute == 'adminhtml' && $orderId) {

            //We are editing an order here.

            $speedyShipmentData = Mage::getModel('speedyshippingmodule/saveorder')
                    ->getCollection()
                    ->addFilter('order_id', $orderId, 'eq')
                    ->load()
                    ->getFirstItem();

            if ($speedyShipmentData->getFixedTime()) {
                //We need to split the time
                $time = $speedyShipmentData->getFixedTime();
                $hour = substr($time, 0, 2);
                $minutes = substr($time, 2);

                $orderData->setSpeedyCurrentExactTimeMethod('test');
                $orderData->setSpeedyExactHour($hour);
                $orderData->setSpeedyExactMinutes($minutes);

                $session->setSpeedyCurrentExactTimeMethod('test');
                $session->setSpeedyExactHour($hour);
                $session->setSpeedyExactMinutes($minutes);
                $this->_doesRequestContainExactHour = true;
            }

            if ($speedyShipmentData->getIsCod()) {

                $orderData->setIsCod(1);
            }
        }


        return $orderData;
    }

    /**
     * This method prepares the data structure that Speedy expects
     * @return \StdClass
     */
    protected function setPickingData() {

        require_once Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ParamCalculation.class.php';


        $currentAction = Mage::app()->getRequest()->getActionName();
        $currentController = Mage::app()->getRequest()->getControllerName();
        $currentRoute = Mage::app()->getRequest()->getRouteName();

        $isAdminArea = FALSE;

        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {

            $isAdminArea = true;
        }

        $totalWeight = 0;
        $totalItems = 0;

        //We need this because this should be substracted from insurance base
        $sumOfVirtualProducts = 0;

        $allProducts = $this->_request->getAllItems();

        $totalItems = $this->_request->getPackageQty();

        if (Mage::getStoreConfig('carriers/speedyshippingmodule/isDocuments')) {
            $totalWeight = 0;
        } else {

            if ($isAdminArea && $currentController == 'sales_order_edit' &&
                    $currentAction == 'start' && $currentRoute == 'adminhtml') {

                foreach ($allProducts as $item) {

                    if ($item->getProduct()->isVirtual()) {
                        $sumOfVirtualProducts +=$item->getQty() * $item->getPriceInclTax();
                        
                        continue;
                    }


                    //Children weight we calculate for parent

                    if ($item->getParentItem()) {

                        continue;
                    }


                    if ($item->getHasChildren() && $item->getProductType() == 'configurable') {


                        foreach ($item->getChildren() as $child) {
                            $childProductId = $child->getProductId();
                            $_product = Mage::getModel('catalog/product')->load($childProductId);
                            if (!(float)$_product->getWeight()) {
                                $totalWeight += Mage::getStoreConfig('carriers/speedyshippingmodule/default_weight') * $item->getQty();
                                $noWeightProductList .= $item->getName();
                            } else {
                                $productWeight = $_product->getWeight();
                                $totalWeight += $productWeight * $item->getQty();
                            }
                        }
                    } else if ($item->getHasChildren() && $item->getProductType() == 'bundle') {


//                        if ($mainProduct->getWeightType()) {
//                            $totalWeight = $mainProduct->getWeight() * $item->getQty();
//                        } else {

                            foreach ($item->getChildren() as $child) {
                                $childProductId = $child->getProductId();
                                $_product = Mage::getModel('catalog/product')->load($childProductId);
                                if (!(float)$_product->getWeight()) {
                                    $totalWeight += Mage::getStoreConfig('carriers/speedyshippingmodule/default_weight') * $item->getQty();
                                    $noWeightProductList .= $item->getName();
                                } else {
                                    $productWeight = $_product->getWeight();
                                    $totalWeight += $productWeight * $item->getQty();
                                }
                            }
                        //}
                    } else {
                        $totalWeight += $item->getWeight() * $item->getQty();
                    }
                }
            } else {

                //Check if all products have declared weight
                $noWeightProductList = null;
                foreach ($allProducts as $item) {

                    if ($item->getProduct()->isVirtual()) {
                        $sumOfVirtualProducts +=$item->getQty() * $item->getPriceInclTax();
                        continue;
                    }


                    //Children weight we calculate for parent

                    if ($item->getParentItem()) {

                        continue;
                    }

                    if ($item->getHasChildren() && $item->getProductType() == 'configurable') {



                        foreach ($item->getChildren() as $child) {
                            $childProductId = $child->getProductId();
                            $_product = Mage::getModel('catalog/product')->load($childProductId);

                            if (!(float)$_product->getWeight() && !$_product->isVirtual()) {
                                $totalWeight += Mage::getStoreConfig('carriers/speedyshippingmodule/default_weight') * $item->getQty();
                                $noWeightProductList .= $item->getName();
                            } else {
                                $productWeight = $_product->getWeight();
                                $totalWeight += $productWeight * $item->getQty();
                            }
                        }
                    } else if ($item->getHasChildren() && $item->getProductType() == 'bundle') {

                        //$mainProduct = $item->getProduct();

//                        if ($mainProduct->getWeightType()) {
//                            $totalWeight = $mainProduct->getWeight() * $item->getQty();
//                        } else {

                            foreach ($item->getChildren() as $child) {
                                $childProductId = $child->getProductId();
                                $_product = Mage::getModel('catalog/product')->load($childProductId);
                                if (!(float)$_product->getWeight() && !$_product->isVirtual()) {
                                    $totalWeight += Mage::getStoreConfig('carriers/speedyshippingmodule/default_weight') * $item->getQty();
                                    $noWeightProductList .= $item->getName();
                                } else {
                                    $productWeight = $_product->getWeight();
                                    $totalWeight += $productWeight * $item->getQty();
                                }
                            }
                        //}
                    } else {
                        if (!(float)$item->getWeight()) {
                            $totalWeight += Mage::getStoreConfig('carriers/speedyshippingmodule/default_weight') * $item->getQty();
                            $noWeightProductList .= $item->getName();
                        } else {

                            $totalWeight += $item->getWeight() * $item->getQty();
                        }
                    }
                }

                if (!is_null($noWeightProductList)) {

                    if ($isAdminArea) {

                        Mage::getSingleton('adminhtml/session')->setSpeedyError($noWeightProductList);
                    } else if ($currentAction == 'saveBilling' && $currentController == 'onepage' &&
                            $currentRoute == 'checkout') {

                        Mage::getSingleton('checkout/session')->setSpeedyError($noWeightProductList);
                    }
                }
            }
        }
        $pickingData = new StdClass();

        $pickingData->weightDeclared = $totalWeight; // Декларирано тегло (например 5.25 кг)

        if (Mage::getStoreConfig('carriers/speedyshippingmodule/bring_to_office') &&
                Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office')) {

            $pickingData->bringToOfficeId = Mage::getStoreConfig('carriers/speedyshippingmodule/bring_to_office');
        } else {
            $pickingData->bringToOfficeId = null; // Офис, в който подателя ще достави пратката. Ако е null, куриер ще я вземе от адреса на подателя
        }

        if ($this->_orderData->getOfficeId()) {

            $pickingData->takeFromOfficeId = $this->_orderData->getOfficeId(); // Офис, от който получателя ще вземе пратката. Ако е null, куриер ще я достави до адреса на получателя
        } else {

            $pickingData->takeFromOfficeId = null;
        }

        $pickingData->parcelsCount = 1; // Брой пакети
        $pickingData->documents = Mage::getStoreConfig('carriers/speedyshippingmodule/isDocuments');
        $pickingData->palletized = false; // Флаг дали пратката се състои от палети


        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {

            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } else {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        $totals = $quote->getTotals();
        $discount = null;
        if (!is_null($totals) && array_key_exists('discount', $totals)) {
            $discount = $totals["discount"]->getValue();
        }

        if (Mage::getStoreConfig('carriers/speedyshippingmodule/add_insurance')) {


            if (Mage::getStoreConfig('carriers/speedyshippingmodule/is_fragile')) {

                $pickingData->fragile = TRUE;
            } else {

                $pickingData->fragile = FALSE;
            }





            $pickingData->amountInsuranceBase = ($this->_request->getBaseSubtotalInclTax() - abs($discount)) - $sumOfVirtualProducts;


            $pickingData->payerTypeInsurance = ParamCalculation::PAYER_TYPE_RECEIVER;
        } else {

            $pickingData->fragile = false;
        }

        if ($this->_orderData->getSpeedyCurrentExactTimeMethod() !== FALSE) {

            $hour = $this->_orderData->getSpeedyExactHour();
            $minutes = $this->_orderData->getSpeedyExactMinutes();

            if (strlen($hour) == 1) {

                $hour = sprintf('%02d', $hour);
            }
            if (strlen($minutes) == 1) {

                $minutes = sprintf('%02d', $minutes);
            }


            if (isset($hour) && $hour !== FALSE && isset($minutes) && $minutes !== FALSE) {


                $pickingData->fixedTimeDelivery = $hour .
                        $minutes;
            }
        }


        //change this if we need to substract virtual goods from cod base
        // $pickingData->amountCODBase = $this->_request->getBaseSubtotalInclTax() - $sumOfVirtualProducts;



        if (!is_null($discount)) {
            $pickingData->amountCODBase = $this->_request->getBaseSubtotalInclTax() - abs($discount);
        } else {
            $pickingData->amountCODBase = $this->_request->getBaseSubtotalInclTax();
        }
        $isFixed = $isFixed = Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable');

        //Speedy calculator plus fixed handling charge
        if ($isFixed == 3) {
            $taxCalculator = Mage::helper('tax');
            $chargeAmount = Mage::getStoreConfig('carriers/speedyshippingmodule/handlingCharge');
            $chargeWithTaxApplied = $taxCalculator->getShippingPrice($chargeAmount, true);
            $pickingData->amountCODBase += $chargeWithTaxApplied;
        }

        $isEnabled = Mage::getStoreConfig('carriers/speedyshippingmodule/free_shipping_enable');


        $freeMethodSubtotal = Mage::getStoreConfig('carriers/speedyshippingmodule/free_shipping_subtotal');

        /*
          if ($isEnabled && ($this->_request->getBaseSubtotalInclTax() >= $freeMethodSubtotal)) {

          $pickingData->payerType = ParamCalculation::PAYER_TYPE_SENDER;
          } else {
         */
        // $pickingData->payerType = ParamCalculation::PAYER_TYPE_RECEIVER;
        // }

        if ($isFixed == 2 || $isFixed == 4 || !$this->_orderData->getSpeedyActiveCurrencyCode() || $this->_request->getDestCountryId() != 'BG') {
            $pickingData->payerType = ParamCalculation::PAYER_TYPE_SENDER;
        } else {
            $pickingData->payerType = ParamCalculation::PAYER_TYPE_RECEIVER;
        }

        $pickingData->backDocumentReq = Mage::getStoreConfig('carriers/speedyshippingmodule/back_documents'); // Заявка за обратни документи
        $pickingData->backReceiptReq = Mage::getStoreConfig('carriers/speedyshippingmodule/back_receipt'); // Заявка за обратна разписка
        // $pickingData->contents = '.'; // Съдържание на пратката
        $pickingData->packing = '.'; // Опаковка на пратката
        $pickingData->takingDate = time();

        return $pickingData;
    }

    /**
     * This method initializes the receiver data
     * @return \StdClass
     */
    protected function setUpReceiverData() {

        $receiverData = new StdClass();
        $receiverData->address = new StdClass();

        if ($this->_orderData->getInSessionOnly()) {

            $receiverData->address->siteID = $this->_orderData->getReceiverCityId();
        } else {

            $receiverData->address->siteID = $this->_orderData->getReceiverCityId();


            $receiverData->address->quarter = $this->_orderData->getQuarterId();


            $receiverData->address->blockNo = $this->_orderData->getBlockId();


            $receiverData->address->street = $this->_orderData->getStreetId();


            $receiverData->address->streetNo = $this->_orderData->getStreetNo();

            $receiverData->address->speedyCountryId = $this->_orderData->getSpeedyCountryId();
            $receiverData->address->speedyStateId = $this->_orderData->getSpeedyStateId();
            $receiverData->address->postcode = $this->_orderData->getPostcode();
            $receiverData->address->countryId = $this->_orderData->getCountryId();
            $receiverData->address->activeCurrencyCode = $this->_orderData->getSpeedyActiveCurrencyCode();

            $receiverData->partnerName = $this->_request->getRecipientContactPersonName();
            $receiverData->contactName = $this->_request->getRecipientContactPersonName();
            $receiverData->contactPhone = $this->_request->getRecipientContactPhoneNumber();
        }
        return $receiverData;
    }

    public function getResult() {

        return $this->_result;
    }

    protected function _getQuotes() {

        return $this->_doRequest();
    }

    protected function _getValidShippingMethods($methods) {
        /*
         * The following call returns a list of valid shipping service 
         * according to current module configuration, weight and destination
         * 
         */
        //$allowedMethods = $this->_filterShippingMethods($methods);
        $allowedMethods = $methods;
        if (isset($allowedMethods)) {

            $priceForMethod = $this->_calculatePrices($allowedMethods);
        }


        if (isset($priceForMethod)) {

            $this->_mapMethods($priceForMethod);
        }
    }

    protected function _doRequest() {


        $isAdmin = FALSE;

        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {
            $isAdmin = TRUE;
        }

        $result = Mage::getModel('shipping/rate_result');
       
        if ($isAdmin && $this->_orderData->getSpeedyActiveCurrencyCode()) {
            $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
            $rates = Mage::getModel('directory/currency')->getCurrencyRates(Mage::app()->getBaseCurrencyCode(), array_values($allowedCurrencies));
            if (!isset($rates[$this->_orderData->getSpeedyActiveCurrencyCode()])) {
                $error = Mage::getModel('shipping/rate_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage(Mage::helper('speedyshippingmodule')->__('The currency %s is missing or invalid.', $this->_orderData->getSpeedyActiveCurrencyCode()));
                $result->append($error);
            }
        }


        $methods = explode(',', $this->getConfigData('allowed_methods'));

        $request = Mage::app()->getRequest();

        $isFixedHourAllowed = Mage::getStoreConfig('carriers/speedyshippingmodule/add_fixed_hour');

        if ($isFixedHourAllowed) {

            if ($isAdmin) {

                if ($request->getParam('speedy_exact_hour') !== FALSE &&
                        strlen($request->getParam('speedy_exact_hour') !== FALSE) > 0 &&
                        $request->getParam('speedy_exact_minutes') !== FALSE &&
                        strlen($request->getParam('speedy_exact_minutes'))) {

                    $this->_doesRequestContainExactHour = TRUE;
                }


                if (isset($doesRequestContainExactHour) && $doesRequestContainExactHour != FALSE) {
                    $oldFixedHour = $this->_pickingData->fixedTimeDelivery;

                    $this->_pickingData->fixedTimeDelivery = null;


                    $this->_getValidShippingMethods($methods);

                    $this->_pickingData->fixedTimeDelivery = $oldFixedHour;
                } else {
                    $this->_pickingData->fixedTimeDelivery = null;

                    $this->_getValidShippingMethods($methods);

                    $this->_pickingData->fixedTimeDelivery = 1200;
                    $this->_getValidShippingMethods($methods);
                }
            } else {

                $this->_getValidShippingMethods($methods);


                if (!isset($this->_pickingData->fixedTimeDelivery)) {

                    //This is just a dummy hour, needed for correct calculation of
                    //fixed hour prices
                    $this->_pickingData->fixedTimeDelivery = (int) 1200;
                    $this->_getValidShippingMethods($methods);
                }
            }
        } else {
            $this->_getValidShippingMethods($methods);
        }

        $currentAction = Mage::app()->getRequest()->getActionName();
        $currentController = Mage::app()->getRequest()->getControllerName();
        $currentRoute = Mage::app()->getRequest()->getRouteName();

        if ($isAdmin && $currentController == 'sales_order_edit' &&
                $currentAction == 'start' && $currentRoute == 'adminhtml') {
            Mage::getSingleton('adminhtml/session_quote')->setSpeedyFixedHourPrices($this->_speedyRates);
        } else if ($isAdmin && $currentController == 'sales_order_create' &&
                $currentAction == 'reorder' && $currentRoute == 'adminhtml') {
            Mage::getSingleton('adminhtml/session_quote')->setSpeedyFixedHourPrices($this->_speedyRates);
        }

        $finalMethods = $this->_speedyRates;

        if (!empty($finalMethods)) {
            foreach ($finalMethods as $method) {
                $Rmethod = Mage::getModel('shipping/rate_result_method');

                $Rmethod->setCarrier($this->_code);

                $Rmethod->setCarrierTitle($this->getConfigData('title'));
                $Rmethod->setMethod($method['code']);
                $Rmethod->setMethodTitle($method['title']);

                $Rmethod->setCost($method['amount']);

                $Rmethod->setPrice($method['amount']);


                if (array_key_exists(
                                'fixedhour_amount_with_tax', $method)) {

                    $Rmethod->setSpeedyFixedHourEnabled(1);
                    $Rmethod->setSpeedyAmountFixedHourWithTax($method['fixedhour_amount_with_tax']);
                }

                if (array_key_exists(
                                'fixedhour_amount_without_tax', $method)) {
                    $Rmethod->setSpeedyFixedHourEnabled(1);
                    $Rmethod->setSpeedyAmountFixedHourWithoutTax($method['fixedhour_amount_without_tax']);
                }
                if (isset($this->_doesRequestContainExactHour) && $this->_doesRequestContainExactHour != FALSE) {
                    $Rmethod->setRequestContainsExactHour(1);
                }




                if (array_key_exists('cod_allowed', $method)) {
                    $Rmethod->setSpeedyCodAllowed(1);
                } else if (array_key_exists('cod_required', $method)) {
                    $Rmethod->setSpeedyCodRequired(1);
                }


                $result->append($Rmethod);
            }
        } else {
            if ($this->getConfigData('allowed_methods')) {
                if (Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable') == 4) {
                    $message = Mage::helper('speedyshippingmodule')->__('For this order can not be calculated price. Please contact the administrators of the store!');
                } else {
                    $message = Mage::helper('speedyshippingmodule')->__('Please select another office or change your shipping address.');
                }

                $error = Mage::getModel('shipping/rate_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($message);
                $result->append($error);
            }
        }

        return $result;
    }

    /**
     * This method determines the services available for a particular shipment
     * based on the weight, Magento configuration and sender and receiver
     * site id's
     * @return boolean
     */
    protected function _filterShippingMethods($arrAvailableServices) {


        //GET CONFIG OPTIONS

        $isFixedHourAllowed = Mage::getStoreConfig('carriers/speedyshippingmodule/add_fixed_hour');
        $isBackDocumentsAllowed = Mage::getStoreConfig('carriers/speedyshippingmodule/back_documents');
        $isBackReceiptAllowed = Mage::getStoreConfig('carriers/speedyshippingmodule/back_receipt');
        $isInsuranceAllowed = Mage::getStoreConfig('carriers/speedyshippingmodule/add_insurance');


        //CHECK IF DELIVERY ADDRESS IS AN OFFICE
        $isCurrentAddressOffice = $this->_pickingData->takeFromOfficeId;


        //Check if Speedy COD is available as a payment method
        $isSpeedyCODAvailable = null;
        $_speedy_payment_code = 'cashondelivery';

        $allActivePaymentMethods = Mage::getModel('payment/config')->getActiveMethods();

        foreach ($allActivePaymentMethods as $method) {

            if ($method->getId() == $_speedy_payment_code) {

                $isSpeedyCODAvailable = true;
                break;
            }
        }



        $counter = 0;

        foreach ($arrAvailableServices as $service) {

            if ($service->getErrorDescription()) {
                unset($arrAvailableServices[$counter]);
                $counter++;
                continue;
            } else {
                $counter++;
            }
        }
        /*
          $serviceId = $service->getTypeId();
          $this->_speedyServiceInfo[$serviceId] = array();


          $this->_speedyServiceInfo[$serviceId]['fixed_hour'] =
          $service->getAllowanceFixedTimeDelivery()->getValue();

          $this->_speedyServiceInfo[$serviceId]['cod'] =
          $service->getAllowanceCashOnDelivery()->getValue();

          $this->_speedyServiceInfo[$serviceId]['insurance'] =
          $service->getAllowanceInsurance()->getValue();

          $this->_speedyServiceInfo[$serviceId]['to_be_called'] =
          $service->getAllowanceToBeCalled()->getValue();


          $this->_speedyServiceInfo[$serviceId]['back_receipt'] =
          $service->getAllowanceBackReceiptRequest();

          $this->_speedyServiceInfo[$serviceId]['back_documents'] =
          $service->getAllowanceBackDocumentsRequest();


          $removeServiceFromList = FALSE;

          if ((!$isFixedHourAllowed || !isset($this->_pickingData->fixedTimeDelivery)) &&
          ($this->_speedyServiceInfo[$serviceId]['fixed_hour'] == 'REQUIRED')) {


          $removeServiceFromList = TRUE;
          }

          if (!empty($this->_pickingData->fixedTimeDelivery) &&
          $this->_speedyServiceInfo[$serviceId]['fixed_hour'] == 'BANNED') {

          $removeServiceFromList = TRUE;
          }

          if ($isBackDocumentsAllowed &&
          ($this->_speedyServiceInfo[$serviceId]['back_documents'] == 'BANNED')) {

          $removeServiceFromList = TRUE;
          } else if (!$isBackDocumentsAllowed &&
          ($this->_speedyServiceInfo[$serviceId]['back_documents'] == 'REQUIRED')) {

          $removeServiceFromList = TRUE;
          }


          if ($isBackReceiptAllowed &&
          $this->_speedyServiceInfo[$serviceId]['back_documents'] == 'BANNED') {

          $removeServiceFromList = TRUE;
          } else if (!$isBackReceiptAllowed &&
          $this->_speedyServiceInfo[$serviceId]['back_documents'] == 'REQUIRED') {

          $removeServiceFromList = TRUE;
          }


          if (!$isInsuranceAllowed &&
          ($this->_speedyServiceInfo[$serviceId]['insurance'] == 'REQUIRED')) {

          $removeServiceFromList = TRUE;
          } else if ($isInsuranceAllowed &&
          ($this->_speedyServiceInfo[$serviceId]['insurance'] == 'BANNED')) {

          $removeServiceFromList = TRUE;
          }

          if (!$isSpeedyCODAvailable &&
          ($this->_speedyServiceInfo[$serviceId]['cod'] == 'REQUIRED')) {

          $removeServiceFromList = TRUE;
          }

          if (!$this->_pickingData->takeFromOfficeId &&
          ($this->_speedyServiceInfo[$serviceId]['to_be_called'] == 'REQUIRED')) {

          $removeServiceFromList = TRUE;
          } else if ($this->_pickingData->takeFromOfficeId &&
          ($this->_speedyServiceInfo[$serviceId]['to_be_called'] == 'BANNED')) {
          $removeServiceFromList = TRUE;
          }


          if ($removeServiceFromList) {
          unset($arrAvailableServices[$counter]);
          }
          $counter++;
          }
         */

        //reindex $arrAvailableServices

        $arrAvailableServices = array_values($arrAvailableServices);

        return $arrAvailableServices;



        // Определеляне на сечението между възможните услуги и конфигурираните за клиента услуги (списъка с услуги, с които клиента работи)
        //$arrSelectedServices = Util::serviceIntersection($arrAvailableServices, $methods);

        /*
          $availableDates = array();
          //Retrieve the list of available days for picking
          foreach ($arrSelectedServices as $service) {
          $availableDates[$service] = $this->_speedyEPS->getAllowedDaysForTaking(
          $service, !isset($this->_pickingData->bringToOfficeId) ? $this->_senderData->address->siteID : null, $this->_pickingData->bringToOfficeId, null
          );
          }
         */
        //Get the first available date for picking
        //$firstAvailableDate = array_shift($availableDates);
        //$this->_pickingData->takingDate = $firstAvailableDate[0];
        // Филтриране на списъка от възможни услуги според възможните стойности за тегло
    }

    /**
     * This method calculates the prices of the available Speedy services
     * based on the provided picking data
     * @param type $methods
     * @return type
     */
    protected function _calculatePrices($methods) {

        require_once Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ParamCalculation.class.php';

        // Параметри за калкулация
        $paramCalculation = new ParamCalculation();

        $paramCalculation->setSenderId($this->_speedySessionId->getClientId());

        $paramCalculation->setBroughtToOffice($this->_pickingData->bringToOfficeId);


        $takingDate = null;
        $numDays = (int) Mage::getStoreConfig('carriers/speedyshippingmodule/speedyTakingtimeOffset');

        if ($numDays) {

            if (Mage::getStoreConfig('carriers/speedyshippingmodule/speedyTakingtimeOffset') == 1) {
                $takingDate = strtotime("+1 day");
            } else {
                $takingDate = strtotime("+$numDays days");
            }
        } else {
            $takingDate = time();
        }

        $paramCalculation->setTakingDate($takingDate);

        $paramCalculation->setAutoAdjustTakingDate(1);

        /*
          if (Mage::getStoreConfig('carriers/speedyshippingmodule/deferredDays')) {
          $paramCalculation->setDeferredDeliveryWorkDays(Mage::getStoreConfig('carriers/speedyshippingmodule/deferredDays'));
          }
         */

        $paramCalculation->setToBeCalled(isset($this->_pickingData->takeFromOfficeId));

        $paramCalculation->setParcelsCount($this->_pickingData->parcelsCount);

        $paramCalculation->setWeightDeclared($this->_pickingData->weightDeclared);

        $paramCalculation->setDocuments($this->_pickingData->documents);

        $paramCalculation->setPalletized($this->_pickingData->palletized);

        if (isset($this->_pickingData->fixedTimeDelivery)) {

            $paramCalculation->setFixedTimeDelivery($this->_pickingData->fixedTimeDelivery);
        } else {

            $paramCalculation->setFixedTimeDelivery(null);
        }
        if ($this->_pickingData->fragile && $this->_pickingData->amountInsuranceBase &&
                $this->_pickingData->payerTypeInsurance) {

            $paramCalculation->setFragile($this->_pickingData->fragile);

            $paramCalculation->setAmountInsuranceBase($this->_pickingData->amountInsuranceBase);

            $paramCalculation->setPayerTypeInsurance($this->_pickingData->payerTypeInsurance);
        } else {

            $paramCalculation->setFragile($this->_pickingData->fragile);
        }

        if (!($this->_pickingData->takeFromOfficeId)) {
            $paramCalculation->setReceiverSiteId($this->_receiverData->address->siteID);
        }

        $paramCalculation->setPayerType($this->_pickingData->payerType);

        if ($this->_receiverData->address->activeCurrencyCode) {
            $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
            $rates = Mage::getModel('directory/currency')->getCurrencyRates(Mage::app()->getBaseCurrencyCode(), array_values($allowedCurrencies));
            if (isset($rates[$this->_receiverData->address->activeCurrencyCode])) {
                $paramCalculation->setAmountCodBase(Mage::helper('directory')->currencyConvert($this->_pickingData->amountCODBase, Mage::app()->getStore()->getBaseCurrencyCode(), $this->_receiverData->address->activeCurrencyCode));
            }
        }

        $paramCalculation->setTakingDate($this->_pickingData->takingDate);

        if (Mage::getStoreConfig('carriers/speedyshippingmodule/bring_to_office') && Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office')) {
            $paramCalculation->setWillBringToOfficeId(Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office'));
        }

        if ($this->_pickingData->takeFromOfficeId) {
            $paramCalculation->setOfficeToBeCalledId($this->_pickingData->takeFromOfficeId);
        } else { 
            $paramCalculation->setOfficeToBeCalledId(null);
        }

        if (!$this->_pickingData->takeFromOfficeId && !empty($this->_receiverData->address->speedyCountryId)) {
            $paramCalculation->setReceiverCountryId($this->_receiverData->address->speedyCountryId);
             if (!empty($this->_receiverData->address->postcode)) {
                $paramCalculation->setReceiverPostCode($this->_receiverData->address->postcode);
            }
        }

        // if abroad a fixed_pricing_enable == calculator || calculator_fixed
        if (!empty($this->_receiverData->address->countryId) && $this->_receiverData->address->countryId != 'BG' && (Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable') == 1 || Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable') == 3)) {
            $paramCalculation->setIncludeShippingPriceInCod(true);
        }

        /*
          if (count($methods) == 1) {

          $paramCalculation->setServiceTypeId($methods[0]);

          try {

          $resultCalculation = $this->_speedyEPS->calculate($paramCalculation);
          } catch (ServerException $se) {
          Mage::log($se->getMessage());
          }
          } else {
         */
        try {

            $resultCalculation = $this->_speedyEPS->calculateMultipleServices($paramCalculation, $methods);
            $resultCalculation = $this->_filterShippingMethods($resultCalculation);
        } catch (ServerException $se) {

            Mage::log($se->getMessage(), null, 'speedyLog.log');
        }
        //}

        if (isset($resultCalculation)) {

            return $resultCalculation;
        }
    }

    /**
     * This method converts the results from Speedy result set to Magento rate result 
     * objects
     * 
     * @param type $finalMethods
     * @return boolean
     */
    protected function _mapMethods($finalMethods) {

        $methods = array();

        $configMethods = $this->getCode('method');

        $shippingTax = Mage::getSingleton('checkout/session')
                        ->getQuote()->getShippingAddress()->getAppliedTaxes();

        if ($finalMethods) {

            if (is_array($finalMethods)) {


                /*
                  $handlingType =
                  Mage::getStoreConfig('carriers/speedyshippingmodule/handling_type');

                  if ($handlingType == 'F') {
                  $handlingAmount = Mage::getStoreConfig('carriers/speedyshippingmodule/handling_fee');
                  } elseif ($handlingType = 'P') {
                  $handlingPercent = Mage::getStoreConfig('carriers/speedyshippingmodule/handling_fee');
                  $handlingAmount = ($handlingPercent / 100) * $this->_pickingData->amountCODBase;
                  }
                 */

                /**
                 * Check if we have shipping rate in checkout session.
                 * 
                 * 
                 */
                $carrierMethod = Mage::getSingleton('checkout/session')
                        ->getQuote()
                        ->getShippingAddress()
                        ->getShippingMethod();

                if ($carrierMethod) {

                    $carrierNameParts = explode('_', $carrierMethod);

                    if ($carrierNameParts[0] == 'speedyshippingmodule') {

                        $carrierCode = $carrierNameParts[1];
                    }
                }

                $request = Mage::app()->getRequest();

                //We have an existing order to edit
                $orderId = (int) $request->getParam('order_id');
                $existingOrderPaymentMethod = $this->_orderData->getIsCod();

                foreach ($finalMethods as $method) {
                    $total = 0;

                    //Check if method is valid
                    if (!$method->getErrorDescription()) {

                        $title = $configMethods[$method->getServiceTypeId()];

                        $code = $method->getServiceTypeId();

                        $removeCOD = false;


                        $actionName = $request->getActionName();

                        /* If this evaluates to true, the whole process of calculating the services
                         * availability and cost has been restarted, so the current value of 
                         * COD amount should be erased
                         * 
                         */
                        if ($actionName == 'saveBilling' || $actionName == 'saveShipping' || $actionName == 'saveShippingMethod') {

                            if (Mage::getSingleton('checkout/session')->getSpeedyCOD()) {

                                Mage::getSingleton('checkout/session')->unsSpeedyCOD();
                            }

                            $removeCOD = true;
                        }

                        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {

                            $paymentMethod = $request->getParam('payment');

                            $paymentMethod = $paymentMethod['method'];


                            if (!isset($paymentMethod)) {

                                $paymentMethod = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getPayment()->getMethod();
                            }
                        } else {

                            $paymentMethod = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethod();
                        }

                        if ((!$paymentMethod || !($paymentMethod == 'cashondelivery') || $removeCOD ) && !$existingOrderPaymentMethod) {


                            /**
                             * The customer has not selected a payment method(e.g she as at billing,
                             * shipping or recalculate action.We remove vat and cod amounts from
                             * total
                             */
                            $total = $method->getResultInfo()->getAmounts()->getTotal() -
                                    ($method->getResultInfo()->getAmounts()->getVat() + $method->getResultInfo()->getAmounts()->getCodPremium());

                            // $total += $handlingAmount;
                        } else {

                            $total = $method->getResultInfo()->getAmounts()->getTotal() -
                                    ($method->getResultInfo()->getAmounts()->getVat() + $method->getResultInfo()->getAmounts()->getCodPremium());

                            $cod = $method->getResultInfo()->getAmounts()->getCodPremium();

                            $total += $cod;
                        }

                        $this->_CODPrices[$code] = $method->getResultInfo()->getAmounts()->getCodPremium();


                        $taxCalculator = Mage::helper('tax');


                        $isFixed = Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable');

                        if ($isFixed == 2) {

                            $fixedPrice = Mage::getStoreConfig('carriers/speedyshippingmodule/fixedPrice');


                            $total = $fixedPrice;
                        } else if ($isFixed == 3) {
                            $handlingAmount = Mage::getStoreConfig('carriers/speedyshippingmodule/handlingCharge');
                            $total = $total + $taxCalculator->getShippingPrice($handlingAmount, FALSE);
                        } else if ($isFixed == 4) {
                            $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
                            $tablerates = Mage::getModel('speedyshippingmodule/carrier_tablerate')->getCollection()->setServiceIdFilter($method->getServiceTypeId())->setTakeFromOfficeFilter($this->_pickingData->takeFromOfficeId ? 1 : 0)->setWeightFilter($this->_pickingData->weightDeclared)->setTotalFilter($totals['subtotal']->getValue())->setFixedTimeDeliveryFilter($this->_pickingData->fixedTimeDelivery ? 1 : 0)->setOrderField('weight')->setOrderField('order_total')->getData();
                            if ($tablerates && isset($tablerates[0])) {
                                $total = $tablerates[0]['price_without_vat'];
                            } else {
                                continue;
                            }
                        }

                        //Is fixed hour allowed for this particular service
                        if (array_key_exists($method->getServiceTypeId(), $this->_speedyServiceInfo)) {
                            $_fixedHour = $this->_speedyServiceInfo[$method->getServiceTypeId()]['fixed_hour'];
                            //Is cash on delivery allowed for this particular service
                            $_cod = $this->_speedyServiceInfo[$method->getServiceTypeId()]['cod'];
                        }



                        if (!isset($this->_pickingData->fixedTimeDelivery)) {

                            $methodData = array(
                                'title' => $title,
                                'code' => $code,
                                'amount' => $total
                            );
                        } else if ($this->_pickingData->fixedTimeDelivery) {
                            $currentAction = Mage::app()->getRequest()->getActionName();
                            if ($currentAction == 'saveShippingMethod' && !array_key_exists('speedy_service_' . $code, $this->_speedyRates)) {
                                $methodData = array(
                                    'title' => $title,
                                    'code' => $code,
                                    'amount' => $total
                                );
                            } else if (!array_key_exists('speedy_service_' . $code, $this->_speedyRates)) {
                                $methodData = array(
                                    'title' => $title,
                                    'code' => $code,
                                    'amount' => $total
                                );
                            } else {
                                $methodData = array();
                            }



                            $fixed_time_amount = $method->getResultInfo()->getAmounts()->getFixedTimeDelivery();

                            // $methodData['fixed_time_amount_withtax'] = $method->getResultInfo()->getAmounts()->getFixedTimeDelivery();

                            if ($taxCalculator->getShippingPrice($fixed_time_amount, true)) {
                                $methodData['fixedhour_amount_with_tax'] = number_format($taxCalculator->getShippingPrice($fixed_time_amount, true), 2);
                            }

                            if ($taxCalculator->getShippingPrice($fixed_time_amount, FALSE)) {
                                $methodData['fixedhour_amount_without_tax'] = number_format($taxCalculator->getShippingPrice($fixed_time_amount, FALSE), 2);
                            }
                        }

                        if (isset($this->_doesRequestContainExactHour)) {
                            $this->_speedyRates['speedy_service_' . $code]['amount'] = $total;
                        }

                        $serviceId = 'speedy_service_' . $code;

                        if (array_key_exists($serviceId, $this->_speedyRates)) {
                            $this->_speedyRates[$serviceId] = array_merge($this->_speedyRates[$serviceId], $methodData);
                        } else {
                            $this->_speedyRates[$serviceId] = $methodData;
                        }
                    } else {

                        Mage::log($method->getErrorDescription() . '---' . $configMethods[$method->getServiceTypeId()], null, 'speedyLog.log');
                    }
                }


                if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {

                    Mage::getSingleton('adminhtml/session_quote')->setSpeedyCOD($this->_CODPrices);
                } else {

                    Mage::getSingleton('checkout/session')->setSpeedyCOD($this->_CODPrices);
                }
            }
        }
    }

    protected function _updateFreeMethodQuote($request) {

        $isEnabled = Mage::getStoreConfig('carriers/speedyshippingmodule/free_shipping_enable');

        if (!(boolean) $isEnabled && !$request->getFreeShipping()) {

            return;
        }
        $isFixed = Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable');

        $isCityService = FALSE;

        $freeCityMethod = Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_city');

        $freeInterCityMethod = Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_intercity');

        $freeInternationalMethod = explode(',', Mage::getStoreConfig('carriers/speedyshippingmodule/free_method_international'));

        if ($isFixed == 2) {

            $freeMethod = 'speedy_fixed_price';
        }

        if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {

            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } else {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        $totals = $quote->getTotals();
        $discount = null;
        if (!is_null($totals) && array_key_exists('discount', $totals)) {
            $discount = $totals["discount"]->getValue();
        }

        $freeMethodSubtotal = Mage::getStoreConfig('carriers/speedyshippingmodule/free_shipping_subtotal');

        if (!is_null($discount)) {
            $orderValue = $request->getBaseSubtotalInclTax() - abs($discount);
        } else {
            $orderValue = $request->getBaseSubtotalInclTax();
        }


        if ($orderValue >= $freeMethodSubtotal || $request->getFreeShipping()) {

            $request->setFreeShipping(1);
        }
        $freeRateIds = array();

        if (is_object($this->_result)) {

            foreach ($this->_result->getAllRates() as $i => $item) {

                if ($item->getMethod() == $freeCityMethod) {

                    $freeRateIds[] = $i;
                } else if ($item->getMethod() == $freeInterCityMethod) {

                    $freeRateIds[] = $i;
                } else if (in_array($item->getMethod(), $freeInternationalMethod)) {

                    $freeRateIds[] = $i;
                }
            }
        }

        if (empty($freeRateIds)) {

            return;
        }
        $price = null;

        if (!$request->getFreeShipping()) {

            $result = $this->_result;
            if ($result && ($rates = $result->getAllRates()) && count($rates) > 0) {
                if ((count($rates) == 1) && ($rates[0] instanceof Mage_Shipping_Model_Rate_Result_Method)) {
                    $price = $rates[0]->getPrice();
                }
                if (count($rates) > 1) {
                    foreach ($rates as $rate) {
                        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Method && $rate->getMethod() == $freeMethod
                        ) {
                            $price = $rate->getPrice();
                        }
                    }
                }
            }
        } else {
            /**
             * if we can apply free shipping for all order we should force price
             * to $0.00 for shipping with out sending second request to carrier
             */
            $price = 0;
        }

        /**
         * if we did not get our free shipping method in response we must use its old price
         */
        if (!is_null($price)) {
            foreach ($freeRateIds as $freeRateId) {
                $this->_result->getRateById($freeRateId)->setIsFree(1);
                $this->_result->getRateById($freeRateId)->setPrice($price);
            }

            $isAdmin = FALSE;

            if (Mage::app()->getStore()->isAdmin() && Mage::getDesign()->getArea() == 'adminhtml') {
                $isAdmin = TRUE;
            }


            if ($isAdmin) {

                $currentAction = Mage::app()->getRequest()->getActionName();
                $currentController = Mage::app()->getRequest()->getControllerName();
                $currentRoute = Mage::app()->getRequest()->getRouteName();

                if (($isAdmin && $currentController == 'sales_order_edit' &&
                        $currentAction == 'start' && $currentRoute == 'adminhtml') || ($isAdmin && $currentController == 'sales_order_create' &&
                        $currentAction == 'reorder' && $currentRoute == 'adminhtml')) {

                    foreach ($freeRateIds as $freeRateId) {
                        $rate = $this->_result->getRateById($freeRateId);
                        if (array_key_exists('speedy_service_' . $rate->getMethod(), $this->_speedyRates)) {
                            $this->_speedyRates['speedy_service_' . $rate->getMethod()]['is_free'] = 1;
                        }
                    }
                    Mage::getSingleton('adminhtml/session_quote')->unsSpeedyFixedHourPrices();
                    Mage::getSingleton('adminhtml/session_quote')->setSpeedyFixedHourPrices($this->_speedyRates);
                }
            }
        }
    }

    protected function _setFreeMethodRequest($freeMethod) {
        $r = $this->_rawRequest;

        $weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
        $weight = $this->_getCorrectWeight($weight);
        $r->setWeight($weight);
        $r->setAction($this->getCode('action', 'single'));
        $r->setProduct($freeMethod);
    }

    public function getTracking($trackings) {
        $data = $trackings;
    }

    public function getTrackingInfo($trackings) {

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }
        $this->speedyTracking($trackings);

        return $this->_result;
    }

    protected function speedyTracking($bol) {
        if ($bol) {
            try {
                $bolID = (float) $bol[0];
                $result = $this->_speedyEPS->trackPickingEx($bolID, null);
            } catch (ServerException $se) {
                Mage::log($se->getMessage(), null, 'speedyLog.log');
            }
        }

        if ($result) {
            $resultArray = array();


            foreach ($result as $track) {
                $tempArray = array();
                $tempArray['activity'] = (string) $track->getOperationDescription();
                $timestamp = strtotime((string) $track->getMoment());
                if ($timestamp) {
                    $tempArray['deliverydate'] = date('Y-m-d', $timestamp);
                    $tempArray['deliverytime'] = date('H:i:s', $timestamp);
                }

                $tempArray['deliverylocation'] = strtolower($track->getSiteType()) . $track->getSiteName();
                $packageProgress[] = $tempArray;
            }


            $resultArray['progressdetail'] = $packageProgress;



            if (!$this->_result) {
                $this->_result = Mage::getModel('shipping/tracking_result');
            }

            if (isset($resultArray)) {
                $tracking = Mage::getModel('shipping/tracking_result_status');
                $tracking->setCarrier('Speedy');
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($bol[0]);
                $tracking->setDeliveryLocation($resultArray['delivery_location']);
                $tracking->addData($resultArray);
                $this->_result = $tracking;
            } else {
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier('fedex');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($trackingValue);
                $error->setErrorMessage($errorTitle ? $errorTitle : Mage::helper('usa')->__('Unable to retrieve tracking'));
                $this->_result->append($error);
            }
        }
    }

    /**
     * Get tracking response
     *
     * @return string
     */
    public function getResponse() {
        $statuses = '';
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result) {
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking) {
                    if ($data = $tracking->getAllData()) {
                        if (isset($data['status'])) {
                            $statuses .= Mage::helper('usa')->__($data['status']) . "\n<br/>";
                        } else {
                            $statuses .= Mage::helper('usa')->__($data['error_message']) . "\n<br/>";
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = Mage::helper('usa')->__('Empty response');
        }
        return $statuses;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return boolean
     */
    public function isShippingLabelsAvailable() {
        return true;
    }

    public function getContainerTypes(Varien_Object $params = null) {
        return $this->getCode('packaging');
    }

    public function getOffices() {

        if (isset($this->_speedySessionId)) {
            try {
                $offices = $this->_speedyEPS->listOffices();
                if ($offices) {
                    $officeList = array();
                    foreach ($offices as $office) {
                        $officeList[$office->getId()] = $office->getId() . ' - ' . $office->getName();
                    }
                    return $officeList;
                }
            } catch (ServerException $se) {
                Mage::log($se->getMessage(), null, 'speedyLog.log');
            }
        }
    }

    public function getCode($type, $code = '') {
        $codes = array(
            'packaging' => array(
                '1' => 'Speedy Box',
                '2' => 'Speedy Pack',
            )
        );

        if ($this->_speedySessionId) {
            try {
                $services = $this->_speedyEPS->listServices(time());

                if ($services) {
                    foreach ($services as $service) {

                        if ($service->getTypeId() == 26 || $service->getTypeId() == 36) {
                            continue;
                        }

                        // Remove pallet services
                        if ($service->getCargoType() == 2) {
                            continue;
                        }

                        $codes['method'][$service->getTypeId()] = $service->getName();
                    }
                }
            } catch (ServerException $se) {
                Mage::log($se->getMessage(), null, 'speedyLog.log');
            }
        }


        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

}
