<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PrintController
 *
 * @author killer
 */
class Speedy_Speedyshipping_Adminhtml_PrintController extends Mage_Adminhtml_Controller_Action {

    //put your code here

    protected $_speedyEPSInterfaceImplementaion;
    protected $_speedyEPS;
    protected $_speedySessionId;

    /**
     * This ID of the site to which the shipment will be delivered
     * @var type 
     */
    protected $_city_id;

    /**
     * A std class, that holds various pieces of receiver data( address, phone, etc.)
     * @var type 
     */
    protected $_receiverData;

    /*
     * A std class, that holds various pieces of data for the order, associated
     * with the current shipment
     */
    protected $_orderData;

    /**
     * A std class, that holds various pieces of data related to picking 
     * information, needed to create bill of lading and courier requests
     * @var type 
     */
    protected $_pickingData;

    /**
     * A Magento model, that holds Speedy specific data associated with the 
     * order to be shipped, like office id (if any), fixed hour of delivery,
     * payer type, etc.
     * @var type 
     */
    protected $_speedyData;

    /**
     * The number of packages and package characteristics (eg. height, width, depth)
     * that were created by the customer at bill of lading generation time
     * @var type 
     */
    protected $_packages;

    /**
     * The ID of the package choosen at bill of lading creation time.
     * @var type 
     */
    protected $_packId;

    /**
     * Whether the customer has declared, that she has a labels printer. Based 
     * on this value the custome will be able to print shipping labels instead
     * of regular bill of lading.
     * @var type 
     */
    protected $_hasPrinter;

    /**
     * A std class, that holds the address, which will be used to generate a 
     * bill of lading.
     * 
     * @var type 
     */
    protected $_shippingAddress;

    /**
     * The ID of the order, associated with the current shipment
     * @var type 
     */
    protected $_orderID;

    /**
     * A boolean flag, indicating whether the current shipment is free from
     * endcustomer point of view.
     * @var type 
     */
    protected $_isFreeShipping = false;

    /**
     * The amont of the insurance charge. Note that this will be available only
     * if the owner or the administrator of the store has enabled shipment 
     * insurance in the config.
     * @var type 
     */
    protected $_insuranceAmount;

    /**
     * A total of shipping amount. This includes shipping charge without taxed 
     * plus tax rate.
     * @var type 
     */
    protected $_shippingAmount;

    /**
     * The amount of cash on delivery charge.
     * @var type 
     */
    protected $_codAmount;

    /**
     * Any error that might  occur, while attempting to create bill of lading will be
     * stored in this variable and presented to the user for further investigation. 
     * @var type 
     */
    protected $_bolCreationError;

    /**
     * Any error that might occur, while trying to request a courier is stored
     * in this variable for logging and further processing.
     * @var type 
     */
    protected $_courierRequestError = null;

    /**
     * This map hold as keys the list of exception sthat Speedy API might return
     * as a result of various error conditions and their coresponding error 
     * messages that the customer will see. 
     * @var type 
     */
    protected $_exceptionMap = array();

    /**
     * The first available date for picking the current shipment.
     * @var type 
     */
    protected $_firstAvailableDate = null;
    protected $_magentoTime = null;

    /**
     * A list of the first ten available picking dates.
     * @var type 
     */
    protected $_takingTime = null;
    protected $_deferredDays = null;
    protected $_optionsBeforePayment = null;
    protected $_parcelsCount = null;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {

        $this->_initSpeedyService();
        $this->_magentoTime = Mage::getModel('core/date');
        //date_default_timezone_set(Util::SPEEDY_TIME_ZONE);

        parent::__construct($request, $response, $invokeArgs);
    }

    /**
     * This method displays the datagrid, that the customer sees when she
     * clicks on the link Speedy on the main menu in the backend.
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _isAllowed() {

      
        
        
        switch ($this->getRequest()->getActionName()) {
            case 'cancelBol':
            case 'checkDate':
            case 'createLabel':
            case 'index':
            case 'massRequest':
            case 'printLabel':
            case 'printReturnVoucher':
            case 'requestCourier':
                

                return Mage::getSingleton('admin/session')
                                ->isAllowed('speedyshippingmodule/print');
                break;
        }
    }

    /**
     * This method makes the actual call to Speedy API when a request for
     *  courier has been made. This method is called by
     *  Speedy_Speedyshipping_Adminhtml_PrintController::requestCourierAction()
     * and Speedy_Speedyshipping_Adminhtml_PrintController::massRequestAction()
     * @param array $bolIDS
     * @return boolean
     */
    protected function createOrderRequest(array $bolIDS) {
        $paramOrder = new ParamOrder();
        $paramOrder->setBillOfLadingsList(array_map('floatval', $bolIDS));
        $paramOrder->setBillOfLadingsToIncludeType(ParamOrder::ORDER_BOL_INCLUDE_TYPE_EXPLICIT);

        $phonenumber = Mage::getStoreConfig('carriers/speedyshippingmodule/contact_telephone');
        if ((int) $phonenumber) {
            $paramPhoneNumber = new ParamPhoneNumber();
            $paramPhoneNumber->setNumber($phonenumber);
        }
        $paramOrder->setPhoneNumber($paramPhoneNumber);

        $endOfWorkingTime = explode(',', Mage::getStoreConfig('carriers/speedyshippingmodule/end_of_workingtime'));
        $endOfWorkingTime = $endOfWorkingTime[0] . $endOfWorkingTime[1];
        $paramOrder->setWorkingEndTime($endOfWorkingTime);
        $contactName = Mage::getStoreConfig('carriers/speedyshippingmodule/contact_name');
        $paramOrder->setContactName($contactName);

        try {
            $result = $this->_speedyEPS->createOrder($paramOrder);

            return $result;
        } catch (ServerException $se) {
            Mage::log($se->getMessage(), null, 'speedyLog.log');
            $this->_courierRequestError = $se->getMessage();
            return FALSE;
        } catch (ClientException $ce) {
            Mage::log($ce->getMessage(), null, 'speedyLog.log');
            $this->_courierRequestError = $se->getMessage();
            return FALSE;
        }
    }

    /**
     * This method is used when the user has requested a courier for just 
     * one bill of lading
     * @return type
     * @throws Exception
     */
    public function requestCourierAction() {

        $bolID = $this->getRequest()->getParam('bol_id');
        if (!(int) $bolID) {
            
        }

        $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                ->getCollection()
                ->addFilter('bol_id', $bolID, 'eq')
                ->load()
                ->getFirstItem();

        $speedyData->setSendForShipping(1);


        $resourceModelSpeedy = Mage::getResourceModel('speedyshippingmodule/saveorder');



        $errors = array();

        $succeededOrders = array();




        try {

            $resourceModelSpeedy->beginTransaction();

            $speedyData->save();


            $arrResultOrderPickingInfo = $this->createOrderRequest(array(0 => $bolID));

            if (!$arrResultOrderPickingInfo) {
                $resourceModelSpeedy->rollback();
                Mage::getSingleton('adminhtml/session')->addError($this->_courierRequestError);
                $this->_redirect('*/*/');
                return;
            }

            for ($i = 0; $i < count($arrResultOrderPickingInfo); ++$i) {

                $arrErrorDescriptions = $arrResultOrderPickingInfo[$i]->getErrorDescriptions();

                if (count($arrErrorDescriptions) > 0) {

                    $shouldFail = FALSE;

                    $errors[$i]['main_error'] = $this->__("An error has occured while requesting courier for bill of lading:") . $arrResultOrderPickingInfo[$i]->getBillOfLading() . '. ';

                    for ($j = 0; $j < count($arrErrorDescriptions); ++$j) {

                        $errors[$i][$j] = $this->__("Error details") . ($j + 1) . ': ' . $arrErrorDescriptions[$j];
                    }
                } else {

                    // Успешна заявка за куриер

                    $succeededOrders[$i] = $this->__("Bill of lading") . $arrResultOrderPickingInfo[$i]->getBillOfLading() . $this->__("was successfully requested");
                }
            }

            $errorString = '';
            if (count($errors) > 0) {

                foreach ($errors as $error) {
                    $errorString .= ' ' . $error['main_error'];
                    for ($i = 0; $i < count($error); $i++) {
                        $errorString .= $error[$i];
                    }
                    $errorString .= '<br />';
                }

                throw new Exception("Error while making remote call");
            }
            $successString = '';
            //If there are any error this section never get executed
            if (count($succeededOrders) > 0 && !$shouldFail) {

                foreach ($succeededOrders as $order) {
                    $successString .= $order . '<br />';
                }
            }


            $resourceModelSpeedy->commit();
        } catch (Exception $e) {

            Mage::log($e->getMessage(), null, 'speedyLog.log');
            $resourceModelSpeedy->rollback();
        }





        if (strlen($errorString)) {
            Mage::getSingleton('adminhtml/session')->addError($errorString);
        }
        if (strlen($successString)) {
            Mage::getSingleton('adminhtml/session')->addSuccess($successString);
        }
        $this->_redirect('*/*/');
    }

    /**
     * This method is used when the user has requested a courier for multiple
     * bill of ladings simultaneously.
     * @return type
     * @throws Exception
     */
    public function massRequestAction() {

        //This should be a list
        $ids = $this->getRequest()->getPost('speedy_order_id');
        $bolList = array();
        if (!empty($ids)) {
            $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                    ->getCollection()
                    ->addFieldToFilter('speedy_order_id', array('in' => $ids))
                    ->addFieldToFilter('send_for_shipping', 0)
                    ->load();


            if ($speedyData) {

                foreach ($speedyData as $item) {
                    $bolList[] = $item->getBolId();
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError($this->__("There are not bols, that match your request"));
                $this->_redirect('*/*/');
                exit();
            }


            $errors = array();
            $errorString = null;
            $succeededOrders = array();

            $errorBolId = array();

            $successBolId = array();

            $shouldFail = FALSE;



            $writeConnection = $speedyData->getConnection();

            try {


                foreach ($speedyData as $item) {
                    $item->setSendForShipping(1);
                }




                $writeConnection->beginTransaction();



                //First possible exception

                $speedyData->save();



                //At this point the database server and connection are OK

                /**
                 * Attempt to create an order.
                 * If something goes wrong rollback the saved models in the
                 * catch clause
                 */
                //Second possible exception.
                $arrResultOrderPickingInfo = $this->createOrderRequest($bolList);


                if (!$arrResultOrderPickingInfo) {
                    $writeConnection->rollback();
                    Mage::getSingleton('adminhtml/session')->addError($this->_courierRequestError);
                    $this->_redirect('*/*/');
                    return;
                }


                for ($i = 0; $i < count($arrResultOrderPickingInfo); ++$i) {
                    $arrErrorDescriptions = $arrResultOrderPickingInfo[$i]->getErrorDescriptions();
                    if (count($arrErrorDescriptions) > 0) {

                        $shouldFail = TRUE;

                        $errorBolId[] = $arrResultOrderPickingInfo[$i]->getBillOfLading();
                        $errors[$i]['main_error'] = $this->__("An error has occured while requesting courier for bill of lading:") . $arrResultOrderPickingInfo[$i]->getBillOfLading() . '. ';
                        for ($j = 0; $j < count($arrErrorDescriptions); ++$j) {

                            $errors[$i][$j] = $this->__("Error details") . ($j + 1) . ': ' . $arrErrorDescriptions[$j];
                        }
                    } else {

                        // Успешна заявка за куриер

                        $successBolId[] = $arrResultOrderPickingInfo[$i]->getBillOfLading();
                        $succeededOrders[$i] = $this->__("Bill of lading") . $arrResultOrderPickingInfo[$i]->getBillOfLading() . $this->__("was successfully requested");
                    }
                }

                if (count($errors) > 0) {
                    $errorString = '';
                    foreach ($errors as $error) {
                        $errorString .= ' ' . $error['main_error'];
                        for ($i = 0; $i < count($error); $i++) {
                            $errorString .= $error[$i];
                        }
                        $errorString .= '<br />';
                    }
                    //Throw exception and rollback the DB transaction
                    throw new Exception("Error while making remote call");
                }

                //If there are any errors this section never gets executed
                if (count($succeededOrders) > 0 && !$shouldFail) {
                    $successString = '';
                    foreach ($succeededOrders as $order) {
                        $successString .= $order;
                    }
                }



                /*
                  if ($successBolId && !$shouldFail) {
                  $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                  ->getCollection()
                  ->addFieldToFilter('bol_id', array('in' => $successBolId))
                  ->load();

                  }

                 */


                $writeConnection->commit();
            } catch (Exception $e) {

                Mage::log($e->getMessage(), null, 'speedyLog.log');
                $writeConnection->rollback();
            }



            if (strlen($errorString)) {
                Mage::getSingleton('adminhtml/session')->addError($errorString);
            }
            if (strlen($successString)) {
                Mage::getSingleton('adminhtml/session')->addSuccess($successString);
            }
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    /**
     * This method is used to check the closes available date for picking. If
     * that day is not today the user will see a warning and a cofirmation 
     * dialog to choose if she really wants to create bill of lading for a 
     * future date.
     * @return type
     */
    public function checkDateAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        //$forceBolCreation = $this->getRequest()->getParam('forceBolCreation', false);
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        $order = Mage::getModel('sales/order')->load($orderId);
        $this->_shippingAddress = $order->getShippingAddress();
        $this->_orderID = $order->getIncrementId();
        $this->_packages = $this->getRequest()->getParam('packages');
        $this->_packId = 1;

        /**
         * Setup order data
         */
        $this->setUpOrderData($orderId);


        /*
         * Check if current date is supported for the choosen service type
         */

        $serviceId = $this->_orderData->getServiceTypeId();

        if (Mage::getStoreConfig('carriers/speedyshippingmodule/bring_to_office') && Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office')) {
            $senderSiteId = null;
            $senderOfficeId = Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office');
        } else {
            $resultClientData = $this->_speedyEPS->getClientById($this->_speedyEPS->getResultLogin()->getClientId());
            $senderSiteId = $resultClientData->getAddress()->getSiteId();
            $senderOfficeId = null;
        }

        $time = null;
        $numDays = (int) Mage::getStoreConfig('carriers/speedyshippingmodule/speedyTakingtimeOffset');

        if ($numDays) {

            if (Mage::getStoreConfig('carriers/speedyshippingmodule/speedyTakingtimeOffset') == 1) {
                $time = strtotime("+1 day");
            } else {
                $time = strtotime("+$numDays days");
            }
        } else {
            $time = time();
        }

        $takingTime = $this->_speedyEPS->getAllowedDaysForTaking($serviceId, $senderSiteId, $senderOfficeId, $time);

        if ($takingTime) {

            $this->_firstAvailableDate = $takingTime[0];






            $currentTime = getdate($this->_magentoTime->timestamp(time()));

            $firstAvailable = getdate($this->_magentoTime->timestamp(strtotime($this->_firstAvailableDate)));


            /* If the first available date for picking is not today show 
             * warning and a confirmation dialog to the user
             */
            if (($currentTime['mday'] != $firstAvailable['mday']) ||
                    ($currentTime['month'] != $firstAvailable['month']) ||
                    ($currentTime['year'] != $firstAvailable['year'])) {




                $this->_firstAvailableDate = $this->_magentoTime->date('d-m-Y', $this->_magentoTime->timestamp(strtotime($this->_firstAvailableDate)));

                $numDays = (int) Mage::getStoreConfig('carriers/speedyshippingmodule/speedyTakingtimeOffset');


                /* There is a global offset for taking a shipment, 
                 * configured by the administrator
                 */
                if ($numDays) {

                    if (strtotime($this->_firstAvailableDate) > $time) {
                        $this->getResponse()->setBody(json_encode(array('error' => 1,
                            'message' => $this->__('Bol creation date error') .
                            $this->_firstAvailableDate . '. ' . $this->__('Do you want to continue'))));
                    } else {
                        $this->getResponse()->setBody(json_encode(array('ok' => 1, 'message' => 'Taking time OK')));
                    }
                } else {

                    $this->getResponse()->setBody(json_encode(array('error' => 1,
                        'message' => $this->__('Bol creation date error') .
                        $this->_firstAvailableDate . '. ' . $this->__('Do you want to continue'))));
                }
                return;
            } else {
                //Proceed as normal
                $this->_firstAvailableDate = time();
                $this->getResponse()->setBody(json_encode(array('ok' => 1, 'message' => 'Taking time OK')));
                return;
            }
        }
    }

    /**
     * This method is used to put together various pieces of data, needed 
     * for bill of lading creation.
     * @return type
     */
    public function createLabelAction() {

        $orderId = $this->getRequest()->getParam('order_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        //$forceBolCreation = $this->getRequest()->getParam('forceBolCreation', false);
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        $order = Mage::getModel('sales/order')->load($orderId);
        $this->_shippingAddress = $order->getShippingAddress();
        $this->_orderID = $order->getIncrementId();
        $this->_packages = $this->getRequest()->getParam('packages');
        $this->_packId = 1;

        /**
         * Setup order data
         */
        $this->setUpOrderData($orderId);


        /*
         * Check if current date is supported for the choosen service type
         */

        $serviceId = $this->_orderData->getServiceTypeId();

        if (Mage::getStoreConfig('carriers/speedyshippingmodule/bring_to_office') && Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office')) {
            $senderSiteId = null;
            $senderOfficeId = Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office');
        } else {
            $resultClientData = $this->_speedyEPS->getClientById($this->_speedyEPS->getResultLogin()->getClientId());
            $senderSiteId = $resultClientData->getAddress()->getSiteId();
            $senderOfficeId = null;
        }

        $time = null;
        $numDays = (int) Mage::getStoreConfig('carriers/speedyshippingmodule/speedyTakingtimeOffset');

        if ($numDays) {

            if (Mage::getStoreConfig('carriers/speedyshippingmodule/speedyTakingtimeOffset') == 1) {
                $time = strtotime("+1 day");
            } else {
                $time = strtotime("+$numDays days");
            }
        } else {
            $time = time();
        }

        $takingTime = null;

        $takingTime = $this->_speedyEPS->getAllowedDaysForTaking($serviceId, $senderSiteId, $senderOfficeId, $time);

        if ($takingTime) {

            $this->_firstAvailableDate = $takingTime[0];





            $currentTime = getdate($this->_magentoTime->timestamp());

            $firstAvailable = getdate($this->_magentoTime->timestamp($this->_firstAvailableDate));


            if (($currentTime['mday'] != $firstAvailable['mday']) ||
                    ($currentTime['month'] != $firstAvailable['month']) ||
                    ($currentTime['year'] != $firstAvailable['year'])) {
                $this->_firstAvailableDate = $this->_magentoTime->timestamp(strtotime($this->_firstAvailableDate));
            } else {


                $this->_firstAvailableDate = time();
            }
        }



        //is free shipment
        if ($order->getShippingAmount() == 0.000) {
            $this->_isFreeShipping = TRUE;
        }

        $this->_shippingAmount = $order->getShippingAmount() + $order->getShippingTaxAmount();

        $orderItems = $order->getAllItems();
        //$totalWeight = 0;
        $sumOfVirtualProducts = 0;

        //Get the total sum of virtual products (if any)

        foreach ($orderItems as $item) {

            if ($item->getProduct()->isVirtual()) {
                $qty = $item->getQtyOrdered();
                $price = $item->getPriceInclTax();
                $sumOfVirtualProducts += $qty * $price;
            }
        }

        $this->_codAmount = $order->getBaseSubtotalInclTax() - abs($order->getBaseDiscountAmount());
        //Substract the amount of virtual products from insurance premium sum
        $this->_insuranceAmount = ($order->getBaseSubtotalInclTax() - abs($order->getBaseDiscountAmount())) - $sumOfVirtualProducts;
        
        $totalWeight = $this->getRealWeight();
        $receiverName = $this->_shippingAddress->getFirstname() . ' ' . $this->_shippingAddress->getLastname();
        $receiverPhone = $this->_shippingAddress->getTelephone();

        /**
         * Setup receiver data
         */
        $receiver = Mage::getModel('speedyshippingmodule/carrier_receiverdata_receiverdata');

        $receiver->setReceiverData($this->_orderData, $receiverPhone, $receiverName);

        $this->_receiverData = $receiver->getReceiverData();

        $this->_deferredDays = (int) $this->getRequest()->getParam('deferred_days');

        $this->_optionsBeforePayment = $this->getRequest()->getParam('options_before_payment');

        $this->_parcelsCount = $this->getRequest()->getParam('parcels_count');

        /**
         * Setup picking data
         */
        if (!$this->_orderData->getBolId()) {

            $bol = $this->_createBOL($totalWeight);
            if (!$bol) {
                //$this->_exceptionMap = Mage::helper('speedyshippingmodule/exceptionmap')->getErrorMap();
                $message = $this->_bolCreationError;
                $this->getResponse()->setBody(json_encode(array('error' => 1, 'message' => $message)));
                return;
            }


            $parcels = $bol->getGeneratedParcels();
            $bolID = $parcels[0]->getParcelId();


            $this->addTracking($shipmentId, $order, $bolID);

            $this->_speedyData->setBolId($bolID);

            $transactionSave = Mage::getModel('core/resource_transaction');

            /*
              $transactionSave->addObject($this->_speedyData);
              try {

              $transactionSave->save();
              } catch (Exception $e) {

              Mage::log($e->getMessage(), null, 'speedyLog.log');
              $transactionSave->rollback();
              }

             */

            $dateInfo = getdate($this->_firstAvailableDate);


            $timeInfo = getdate($this->_magentoTime->timestamp(strtotime("now")));

            $this->_speedyData->setBolCreatedDay($dateInfo['mday']);
            $this->_speedyData->setBolCreatedMonth($dateInfo['mon']);
            $this->_speedyData->setBolCreatedYear($dateInfo['year']);
            $this->_speedyData->setBolDatetime(date("Y-m-d H:i:s"));





            $this->_speedyData->setDeferredDeliveryWorkdays($this->_deferredDays);

            $this->_speedyData->setOptionsBeforePayment($this->_optionsBeforePayment);





            $currentDayOfTheYear = $timeInfo['yday'];

            $lastSundayOfOctomber = getdate(strtotime($timeInfo['year'] . '-11-00 last sunday'));
            $lastSundayOfOctomber = $lastSundayOfOctomber['yday'];

            $lastSundayOfMarch = getdate(strtotime($timeInfo['year'] . '-04-01 last sunday'));
            $lastSundayOfMarch = $lastSundayOfMarch['yday'];

            /**
             * This is neccessary, because of a bug in Magento time handling.
             * (http://magentomadness.wordpress.com/2011/07/10/more-magento-stupidity-dst-not-reflected-in-reports/)
             *  In
             * essence Magento doesn't know how to handle DST(Daylight Savings Time),
             * so it is neccessary to check if the current day of the year is in
             * the range between last Sunday of Octomber and last Sunday of 
             * March, and if it is we need to substract one hour.
             */
            if ($currentDayOfTheYear >= $lastSundayOfOctomber ||
                    $currentDayOfTheYear <= $lastSundayOfMarch) {

                //Substract one hour
                $currentHour = $this->_magentoTime->timestamp(strtotime("now")) - 3600;
            } else {
                $currentHour = $this->_magentoTime->timestamp(strtotime("now"));
            }

            $this->_speedyData->setBolDatetime(date("Y-m-d H:i:s", $currentHour));
            $currentHour = date('G:i:s', $currentHour);


            $this->_speedyData->setBolCreatedTime($currentHour);
            /*
              $timeParts = $timeInfo ['hours'] .
              ':' .
              $timeInfo ['minutes'] .
              ':' .
              $timeInfo ['seconds'];
             */
            /*
              $this->_speedyData->setBolCreatedTime(
              $this->_speedyData->setBolCreatedTime(
              $timeInfo ['hours'] .
              ':' .
              $timeInfo ['minutes'] .
              ':' .
              $timeInfo ['seconds']);
             */
            //$transactionSave = Mage::getModel('core/resource_transaction');
            $transactionSave->addObject($this->_speedyData);

            try {
                $transactionSave->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'speedyLog.log');
                $transactionSave->rollback();
                $this->getResponse()->setBody(json_encode(array('error' => 1, 'message' => $e->getMessage())));
                return;
            }



            $this->getResponse()->setBody(json_encode(array('ok' => 1, 'message' => 'labelCreated')));
            return;
        } else {

            $this->getResponse()->setBody(json_encode(array('error' => 1, 'message' => $this->__('Label already created'))));
            return;
        }
    }

    /**
     * This method is used to print PDF version of either bill of lading or 
     * shipping label, given that the customer has a special printer for labels.
     * @return type
     */
    public function printLabelAction() {
        require_once Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ParamPDF.class.php';



        $orderId = $this->getRequest()->getParam('order_id');

        $this->_hasPrinter = $this->getRequest()->getParam('has_printer');
        $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                ->getCollection()
                ->addFilter('order_id', $orderId, 'eq')
                ->load()
                ->getFirstItem();
        if (!$speedyData->getBolId()) {
            return;
        }

        $paramPDF = new ParamPDF();



        //If customer has configured that she has a printer, print a label
        if ($this->_hasPrinter) {
            $pickingParcels = $this->_speedyEPS->getPickingParcels((float)$speedyData->getBolId());

            $ids = array();

            foreach ($pickingParcels as $parcel) {
                $ids[] = $parcel->getParcelId();
            }
            $paramPDF->setIds($ids);
            $paramPDF->setType(ParamPDF::PARAM_PDF_TYPE_LBL);
        }
        //Otherwise print a regular bill of lading
        else {
            $paramPDF->setIds((float)$speedyData->getBolId());
            $paramPDF->setType(ParamPDF::PARAM_PDF_TYPE_BOL);
        }
        $paramPDF->setIncludeAutoPrintJS(TRUE);

        $resultPDF = null;
        try {
            $resultPDF = $this->_speedyEPS->createPDF($paramPDF);

            if (is_null($resultPDF)) {
                throw new Exception($this->__("An error occured, while trying to create PDF"));
            }

            //Send output to browser

            $this->getResponse()->setHeader("Content-type", "application/pdf");
            $this->getResponse()->setBody($resultPDF);
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
    }

    public function printReturnVoucherAction() {
        require_once Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ParamPDF.class.php';

        $orderId = $this->getRequest()->getParam('order_id');

        $this->_hasPrinter = $this->getRequest()->getParam('has_printer');
        $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                ->getCollection()
                ->addFilter('order_id', $orderId, 'eq')
                ->load()
                ->getFirstItem();
        if (!$speedyData->getBolId()) {
            return;
        }

        $paramPDF = new ParamPDF();

        //If customer has configured that she has a printer, print a label
        if ($this->_hasPrinter) {
            $pickingParcels = $this->_speedyEPS->getPickingParcels((float)$speedyData->getBolId());

            $ids = array();

            foreach ($pickingParcels as $parcel) {
                $ids[] = $parcel->getParcelId();
            }

            $paramPDF->setIds($ids);
            $paramPDF->setType(ParamPDF::PARAM_PDF_TYPE_LBL);
        }
        //Otherwise print a regular bill of lading
        else {
            $paramPDF->setIds((float)$speedyData->getBolId());
            $paramPDF->setType(30); // ParamPDF::PARAM_PDF_TYPE_VOUCHER
        }
        $paramPDF->setIncludeAutoPrintJS(TRUE);

        $resultPDF = null;
        try {
            $resultPDF = $this->_speedyEPS->createPDF($paramPDF);

            if (is_null($resultPDF)) {
                throw new Exception($this->__("An error occured, while trying to create PDF"));
            }

            //Send output to browser

            $this->getResponse()->setHeader("Content-type", "application/pdf");
            $this->getResponse()->setBody($resultPDF);
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
    }

    /**
     * This method is used to cancel bill of lading if that is possible. For
     * example if a courier has been request that would not be possible.
     * @return type
     */
    public function cancelBolAction() {


        $isBolCanceled = FALSE;

        $orderId = $this->getRequest()->getParam('order_id');
        $isPopUp = $this->getRequest()->getParam('is_popup');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        } else {
            $shipment = Mage::getModel('sales/order_shipment')
                    ->getCollection()
                    ->addFieldToFilter('order_id', $orderId)
                    ->load()
                    ->getFirstItem();
        }
        $order = Mage::getModel('sales/order')->load($orderId);

        $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                ->getCollection()
                ->addFilter('order_id', $orderId, 'eq')
                ->load()
                ->getFirstItem();

        if (!$speedyData) {
            $this->getResponse()->setBody($this->__('No matching bill of lading'));
            return;
        }
        $resourceModelSpeedy = Mage::getResourceModel('speedyshippingmodule/saveorder');

        $bolID = $speedyData->getBolId();

        if ($bolID) {

            $resourceModelSpeedy->beginTransaction();

            try {

                //Reset bill of lading specific data
                $speedyData->setBolId(null);
                $speedyData->setBolCreatedDay(null);
                $speedyData->setBolCreatedMonth(null);
                $speedyData->setBolCreatedYear(null);
                $speedyData->setBolCreatedTime(null);
                $speedyData->setBolDatetime(null);
                $speedyData->save();

                $this->_speedyEPS->invalidatePicking((float)$bolID);

                $isBolCanceled = TRUE;

                $resourceModelSpeedy->commit();
            } catch (ServerException $se) {

                Mage::log($se->getMessage(), null, 'speedyLog.log');

                $resourceModelSpeedy->rollback();

                if ($isPopUp) {

                    $this->getResponse()->setBody($this->__("Bol with ID:") . htmlentities($bolID, 'UTF-8', ENT_QUOTES) . $this->__("cannot be cancelled"));

                    return;
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__("An error has occured trying to cancel bol") . $bolID);
                    $this->_redirect('*/*/');
                    return;
                }
            } catch (ClientException $ce) {
                Mage::log($ce->getMessage(), null, 'speedyLog.log');

                $resourceModelSpeedy->rollback();

                if ($isPopUp) {
                    $this->getResponse()->setBody($ce->getMessage());
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__("An error has occured trying to cancel bol") . $bolID);
                    $this->_redirect('*/*/');
                }
                return;
            }
        }

        if ($isBolCanceled) {

            //delete tracking numbers associated with the current BOL
            $trackings = Mage::getResourceModel('sales/order_shipment_track_collection')
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('parent_id', $shipment->getId());

            foreach ($trackings as $tracking) {
                $tracking->delete();
            }
        }

        if ($isPopUp) {

            $this->getResponse()->setBody($this->__("Bol with ID:") . $bolID . $this->__("was successfully cancelled"));
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__("Bol with ID:") . $bolID . $this->__("was successfully cancelled"));
            $this->_redirect('*/*/');
        }
    }

    /**
     * This method assigns the unique bol ID as a tracking number in Magento. 
     * This value can be used to track shipment status in the backend as well as
     * in the frontend
     * @param type $shipmentId
     * @param type $order
     * @param type $trackNo
     */
    protected function addTracking($shipmentId, $order, $trackNo) {
        $shipment = Mage::getModel('sales/order_shipment');
        $shipment->load($shipmentId);

        if ($shipment->getId()) {
            $track = Mage::getModel('sales/order_shipment_track')
                    ->setShipment($shipment)
                    ->setData('title', 'Speedy Tracking Number')
                    ->setData('number', $trackNo)
                    ->setData('carrier_code', 'speedyshippingmodule')
                    ->setData('order_id', $shipment->getData('order_id'))
                    ->save();
        }
    }

    /**
     * This method creates Bill of lading
     * @param type $totalWeight
     * @return type
     */
    protected function _createBOL($totalWeight) {

        require_once Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ParamCalculation.class.php';

        $sender = new ParamClientData();
        $sender->setClientId($this->_speedySessionId->getClientId());

        $phonenumber = Mage::getStoreConfig('carriers/speedyshippingmodule/contact_telephone');

        if ($phonenumber) {
            $senderPhone = new ParamPhoneNumber();
            $senderPhone->setNumber($phonenumber);
            $sender->setPhones(array(0 => $senderPhone));
        }

        $receiverAddress = new ParamAddress();

        if (!empty($this->_receiverData->address->siteID)) {
            $receiverAddress->setSiteId($this->_receiverData->address->siteID);
        } else {
            $receiverAddress->setSiteName($this->_receiverData->address->city);
        }

        if ($this->_receiverData->address->quarterName) {
            $receiverAddress->setQuarterName($this->_receiverData->address->quarterName);
        }

        if ($this->_receiverData->address->quarter) {
            $receiverAddress->setQuarterId($this->_receiverData->address->quarter);
        }
        if ($this->_receiverData->address->streetName) {
            $receiverAddress->setStreetName($this->_receiverData->address->streetName);
        }

        if ($this->_receiverData->address->street) {
            $receiverAddress->setStreetId($this->_receiverData->address->street);
        }
        if ($this->_receiverData->address->streetNo) {
            $receiverAddress->setStreetNo($this->_receiverData->address->streetNo);
        }
        if ($this->_receiverData->address->blockNo) {
            $receiverAddress->setBlockNo($this->_receiverData->address->blockNo);
        }

        if ($this->_receiverData->address->speedyEntrance) {
            $receiverAddress->setEntranceNo($this->_receiverData->address->speedyEntrance);
        }

        if ($this->_receiverData->address->speedyFloor) {
            $receiverAddress->setFloorNo($this->_receiverData->address->speedyFloor);
        }

        if ($this->_receiverData->address->speedyApartment) {

            $receiverAddress->setApartmentNo($this->_receiverData->address->speedyApartment);
        }

        if ($this->_receiverData->address->speedyAddressNote) {
            $receiverAddress->setAddressNote($this->_receiverData->address->speedyAddressNote);
        }

        if ($this->_receiverData->address->speedyStateId) {
            $receiverAddress->setStateId($this->_receiverData->address->speedyStateId);
        }

        if (!empty($this->_receiverData->address->speedyCountryId)) {
            $receiverAddress->setPostCode($this->_receiverData->address->postcode);
            $receiverAddress->setFrnAddressLine1($this->_receiverData->address->street1);
            $receiverAddress->setFrnAddressLine2($this->_receiverData->address->street2);
            $receiverAddress->setCountryId($this->_receiverData->address->speedyCountryId);
        }

        $receiver = new ParamClientData();

        $receiver->setPartnerName($this->_receiverData->partnerName);
        $receiverPhone = new ParamPhoneNumber();
        $receiverPhone->setNumber($this->_receiverData->contactPhone);
        $receiver->setPhones(array(0 => $receiverPhone));
        
        if($this->_shippingAddress->getEmail()){
            $receiver->setEmail($this->_shippingAddress->getEmail());
        }

        $picking = new ParamPicking();

        //DO NOT CHANGE THIS LINE
        $picking->setClientSystemId(1307306213);
        $picking->setRef1($this->_orderID);

        $size = $this->getParcelSizes();

        if ($size) {
            $picking->setSize($size);
        }


        if ($this->_orderData->getFixedTime()) {
            $picking->setFixedTimeDelivery($this->_orderData->getFixedTime());
        }

        if ($this->_orderData->getServiceTypeId()) {
            $picking->setServiceTypeId($this->_orderData->getServiceTypeId());
        }

        if ($this->_orderData->getOfficeId()) {
            $picking->setOfficeToBeCalledId($this->_orderData->getOfficeId());
        } else {
            $receiver->setAddress($receiverAddress);
        }
        $picking->setBackDocumentsRequest(Mage::getStoreConfig('carriers/speedyshippingmodule/back_documents'));
        $picking->setBackReceiptRequest(Mage::getStoreConfig('carriers/speedyshippingmodule/back_receipt'));

        if (Mage::getStoreConfig('carriers/speedyshippingmodule/bring_to_office') &&
                Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office')) {
            $officeid = Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office');
            $picking->setWillBringToOffice(1);
            $picking->setWillBringToOfficeId(Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office'));
        } else {
            $picking->setWillBringToOffice(null); // Офис, в който подателя ще достави пратката. Ако е null, куриер ще я вземе от адреса на подателя
        }

        $picking->setParcelsCount($this->_parcelsCount);
        $picking->setWeightDeclared($totalWeight);
        $picking->setContents('поръчка: ' . $this->_orderID);

        /*
          if(Mage::getStoreConfig('carriers/speedyshippingmodule/deferredDays')){
          $picking->setDeferredDeliveryWorkDays((int)Mage::getStoreConfig('carriers/speedyshippingmodule/deferredDays'));
          }
         */



        if (Mage::getStoreConfig('carriers/speedyshippingmodule/default_packing') &&
                strlen(Mage::getStoreConfig('carriers/speedyshippingmodule/default_packing')) > 1) {
            $picking->setPacking(Mage::getStoreConfig('carriers/speedyshippingmodule/default_packing'));
        } else {
            $picking->setPacking('.');
        }

        $picking->setDocuments(Mage::getStoreConfig('carriers/speedyshippingmodule/isDocuments'));
        $picking->setPalletized(FALSE);
        $picking->setPackId('.');


        if (Mage::getStoreConfig('carriers/speedyshippingmodule/add_insurance')) {
            if (Mage::getStoreConfig('carriers/speedyshippingmodule/is_fragile')) {
                $picking->setFragile(1);
            } else {
                $picking->setFragile(0);
            }
            $picking->setAmountInsuranceBase($this->_insuranceAmount);
            $picking->setPayerTypeInsurance($this->_orderData->getPayerType());
        } else {
            $picking->setFragile(0);
        }

        $picking->setSender($sender);
        $picking->setReceiver($receiver);


        $picking->setPayerType($this->_orderData->getPayerType());

        $part = getdate($this->_firstAvailableDate);


        $picking->setTakingDate($this->_firstAvailableDate);

        if ($this->_orderData->getMessage()) {
            $picking->setNoteClient($this->_orderData->getMessage());
        }

        $picking->setDeferredDeliveryWorkDays($this->_deferredDays);

        $optionBeforePayment = new ParamOptionsBeforePayment();

        if ($this->_orderData->getIsCod()) {
            if ($this->_optionsBeforePayment == 'open') {
                $optionBeforePayment->setOpen(true);
            } elseif ($this->_optionsBeforePayment == 'test') {
                $optionBeforePayment->setTest(true);
            }

            $isFixed = Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable');
            if ($isFixed == 2) {

                $fixedPrice = Mage::getStoreConfig('carriers/speedyshippingmodule/fixedPrice');
                if ($this->_isFreeShipping) {
                    $totalAmount = $this->_codAmount;
                } else {
                    $taxCalculator = Mage::helper('tax');
                    $totalAmount = $this->_codAmount + $this->_shippingAmount;
                }
            } else if ($isFixed == 3) {
                if ($this->_isFreeShipping) {
                    $totalAmount = $this->_codAmount;
                } else {
                    $taxCalculator = Mage::helper('tax');
                    $chargeAmount = Mage::getStoreConfig('carriers/speedyshippingmodule/handlingCharge');
                    $chargeWithTaxApplied = $taxCalculator->getShippingPrice($chargeAmount, true);
                    $totalAmount = $this->_codAmount + $chargeWithTaxApplied;
                }
            } else if ($isFixed == 4) {
                if ($this->_isFreeShipping) {
                    $totalAmount = $this->_codAmount;
                } else {
                    $tablerates = Mage::getModel('speedyshippingmodule/carrier_tablerate')->getCollection()->setServiceIdFilter($this->_orderData->getServiceTypeId())->setTakeFromOfficeFilter($this->_orderData->getPickFromOffice())->setWeightFilter($totalWeight)->setTotalFilter($this->_codAmount)->setFixedTimeDeliveryFilter($this->_orderData->getFixedTime() ? 1 : 0)->setOrderField('weight')->setOrderField('order_total')->getData();
                    if ($tablerates && isset($tablerates[0])) {
                        $taxCalculator = Mage::helper('tax');
                        $shippingPrice = $taxCalculator->getShippingPrice((float)$tablerates[0]['price_without_vat'], true);
                    } else {
                        $shippingPrice = $this->_shippingAmount;
                    }

                    $totalAmount = $this->_codAmount + $shippingPrice;
                }
            } else {
                $totalAmount = $this->_codAmount;
            }
        } else {
            $totalAmount = 0;
        }

        $picking->setOptionsBeforePayment($optionBeforePayment);

        if ($this->_orderData->getSpeedyActiveCurrencyCode()) {
            $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
            $rates = Mage::getModel('directory/currency')->getCurrencyRates(Mage::app()->getBaseCurrencyCode(), array_values($allowedCurrencies));
            if (isset($rates[$this->_orderData->getSpeedyActiveCurrencyCode()])) {
                $picking->setAmountCodBase(Mage::helper('directory')->currencyConvert($totalAmount, Mage::app()->getStore()->getBaseCurrencyCode(), $this->_orderData->getSpeedyActiveCurrencyCode()));
            }
        }

        if ($this->_orderData->getIsCod() && (Mage::getStoreConfigFlag('carriers/speedyshippingmodule/money_transfer') && $this->_orderData->getCountryId() == 'BG')) {
            $picking->setRetMoneyTransferReqAmount($totalAmount);
            $picking->setAmountCodBase(0);
        }

        // if abroad a fixed_pricing_enable == calculator || calculator_fixed
        if ($this->_orderData->getCountryId() && $this->_orderData->getCountryId() != 'BG' && $this->_orderData->getIsCod() && (Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable') == 1 || $this->config->get('speedy_pricing') == 3)) {
            $picking->setIncludeShippingPriceInCod(true);
        }

        if (Mage::getStoreConfig('carriers/speedyshippingmodule/return_voucher')) {
            $returnVoucher = new ParamReturnVoucher();
            $returnVoucher->setServiceTypeId($this->_getReturnVoucherServiceTypeId($picking));
            $returnVoucher->setPayerType(Mage::getStoreConfig('carriers/speedyshippingmodule/return_voucher_payer_type'));

            $picking->setReturnVoucher($returnVoucher);
        }

        $resultBOL = null;
        try {
            $resultBOL = $this->_speedyEPS->createBillOfLading($picking);
        } catch (ServerException $se) {
            Mage::log($se->getMessage(), null, 'speedyLog.log');
            $this->_bolCreationError = $se->getMessage();
        }
        if (isset($resultBOL)) {

            return $resultBOL;
        } else {
            return;
        }
    }

    /**
     * This method extract various parameters(shipping address, fixed hour option, payer
     * type, payment method etc.) from the database and assembles them into
     * datastructure, that is needed on bill of lading creation time.
     * @param type $orderId
     */
    protected function setUpOrderData($orderId) {
        $orderData = new Varien_Object();

        $speedyData = Mage::getModel('speedyshippingmodule/saveorder')
                ->getCollection()
                ->addFilter('order_id', $orderId, 'eq')
                ->load()
                ->getFirstItem();



        $this->_speedyData = $speedyData;
        $orderData->setReceiverCityId($this->_shippingAddress->getSpeedySiteId());

        if ($this->_shippingAddress->getSpeedyQuarterName()) {
            $orderData->setSpeedyQuarterName($this->_shippingAddress->getSpeedyQuarterName());
        }

        if ($this->_shippingAddress->getSpeedyQuarterId()) {
            $orderData->setQuarterId($this->_shippingAddress->getSpeedyQuarterId());
        }

        if ($this->_shippingAddress->getSpeedyStreetName()) {
            $orderData->setSpeedyStreetName($this->_shippingAddress->getSpeedyStreetName());
        }

        if ($this->_shippingAddress->getSpeedyStreetId()) {
            $orderData->setStreetId($this->_shippingAddress->getSpeedyStreetId());
        }
        if ($this->_shippingAddress->getSpeedyStreetNumber()) {
            $orderData->setStreetNo($this->_shippingAddress->getSpeedyStreetNumber());
        }
        if ($this->_shippingAddress->getSpeedyBlockNumber()) {
            $orderData->setBlockId($this->_shippingAddress->getSpeedyBlockNumber());
        }

        if ($this->_shippingAddress->getSpeedyEntrance()) {
            $orderData->setSpeedyEntrance($this->_shippingAddress->getSpeedyEntrance());
        }

        if ($this->_shippingAddress->getSpeedyFloor()) {
            $orderData->setSpeedyFloor($this->_shippingAddress->getSpeedyFloor());
        }

        if ($this->_shippingAddress->getSpeedyApartment()) {
            $orderData->setSpeedyApartment($this->_shippingAddress->getSpeedyApartment());
        }

        if ($this->_shippingAddress->getSpeedyAddressNote()) {
            $orderData->setSpeedyAddressNote($this->_shippingAddress->getSpeedyAddressNote());
        }

        if ($this->_shippingAddress->getSpeedyCountryId()) {
            $orderData->setSpeedyCountryId($this->_shippingAddress->getSpeedyCountryId());
        }

        if ($this->_shippingAddress->getSpeedyStateId()) {
            $orderData->setSpeedyStateId($this->_shippingAddress->getSpeedyStateId());
        }

        if ($this->_shippingAddress->getCountryId()) {
            $orderData->setCountryId($this->_shippingAddress->getCountryId());
        }

        if ($this->_shippingAddress->getCity()) {
            $orderData->setCity($this->_shippingAddress->getCity());
        }

        if ($this->_shippingAddress->getPostcode()) {
            $orderData->setPostcode($this->_shippingAddress->getPostcode());
        }

        if ($this->_shippingAddress->getStreet()) {
            $streets = $this->_shippingAddress->getStreet();
            if ($streets && isset($streets[0])) {
                $orderData->setStreet1($streets[0]);
            } else {
                $orderData->setStreet1('');
            }

            if ($streets && isset($streets[1])) {
                $orderData->setStreet2($streets[1]);
            } else {
                $orderData->setStreet2('');
            }
        }

        if ($speedyData->getFixedTime()) {
            $orderData->setFixedTime($speedyData->getFixedTime());
        }

        if ($speedyData->getBolId()) {
            $orderData->setBolId($speedyData->getBolId());
        }

        if ($speedyData->getMessage()) {
            $orderData->setMessage($speedyData->getMessage());
        }
        if ($speedyData->getSpeedyServicetypeId()) {
            $orderData->setServiceTypeId($speedyData->getSpeedyServicetypeId());
        }
        if ($this->_shippingAddress->getSpeedyOfficeId()) {
            $orderData->setPickFromOffice(1);
            $orderData->setOfficeId($this->_shippingAddress->getSpeedyOfficeId());
        }

        if ($speedyData->getIsCod()) {
            $orderData->setIsCod(1);
        }

        if ($this->_shippingAddress->getSpeedyCountryId() || $this->_shippingAddress->getCountryId()) {
            try {
                require_once Mage::getBaseDir('lib') . DS . 'SpeedyEPS' . DS . 'ver01' . DS . 'ParamFilterCountry.class.php';
                $ParamFilterCountry = new ParamFilterCountry();
                if ($this->_shippingAddress->getSpeedyCountryId()) {
                    $ParamFilterCountry->setCountryId($this->_shippingAddress->getSpeedyCountryId());
                } else {
                    $ParamFilterCountry->setIsoAlpha2($this->_shippingAddress->getCountryId());
                }
                $countries = $this->_speedyEPS->listCountriesEx($ParamFilterCountry);
            } catch (ServerException $se) {
                Mage::log($se->getMessage(),null,'speedyLog.log');
            }

            if (isset($countries) && count($countries) == 1) {
                $orderData->setSpeedyActiveCurrencyCode($countries[0]->getActiveCurrencyCode());
            }
        }

        //Is fixed prices enabled
        $isFixed = Mage::getStoreConfig('carriers/speedyshippingmodule/fixed_pricing_enable');

        if ($isFixed == 2 || $isFixed == 4 || !$orderData->getSpeedyActiveCurrencyCode()) {

            $orderData->setPayerType(ParamCalculation::PAYER_TYPE_SENDER);
        } else if ($speedyData->getPayerType() == 0) {

            $orderData->setPayerType(ParamCalculation::PAYER_TYPE_SENDER);
        } else {

            $orderData->setPayerType(ParamCalculation::PAYER_TYPE_RECEIVER);
        }

        $allowed_pricings = array(
            1 => 'calculator',
            3 => 'calculator_fixed'
        );

        if ($this->_shippingAddress->getCountryId() != 'BG' ||
            (Mage::getStoreConfigFlag('carriers/speedyshippingmodule/invoice_courier_sevice_as_text') && 
            isset($allowed_pricings[$isFixed]) &&
            !$orderData->getIsCod())
        ) {
            $orderData->setPayerType(ParamCalculation::PAYER_TYPE_SENDER);
        }

        $this->_orderData = $orderData;
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
            $this->_speedyEPSInterfaceImplementaion = new EPSSOAPInterfaceImpl(Mage::getStoreConfig('carriers/speedyshippingmodule/server'));

            $this->_speedyEPS = new EPSFacade($this->_speedyEPSInterfaceImplementaion, $user, $pass);
            $this->_speedySessionId = $this->_speedyEPS->getResultLogin();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'speedyLog.log');
            exit();
        }
    }

    /**
     * This method is necessary, because some product in the shipment
     * might not have explicit weight, so we need to extract the 
     * weight from the request
     * @return type
     */
    protected function getRealWeight() {
        if (isset($this->_packages)) {
            $weight = 0;

            foreach ($this->_packages as $package) {
                $weight += $package['params']['weight'];
            }
        }

        if ($weight !== FALSE) {
            return $weight;
        }
    }

    protected function getParcelSizes() {
        if (isset($this->_packages)) {
            $maxWidth = 0;
            $maxHeight = 0;
            $maxDepth = 0;
            foreach ($this->_packages as $package) {


                if ($package['params']['width'] || $package['params']['height'] || $package['params']['length']) {



                    //Check for max width 

                    if ($package['params']['width']) {

                        if ($package['params']['width'] > $maxWidth) {
                            $maxWidth = $package['params']['width'];
                        }
                    }

                    //Check for max height
                    if ($package['params']['height'] > $maxHeight) {

                        $maxHeight = $package['params']['height'];
                    }

                    if ($package['params']['length'] > $maxDepth) {
                        $maxDepth = $package['params']['length'];
                    }
                }
            }


            if ($maxDepth || $maxHeight || $maxWidth) {
                $size = new Size();

                if ($maxDepth) {
                    $size->setDepth($maxDepth);
                }

                if ($maxHeight) {
                    $size->setHeight($maxHeight);
                }

                if ($maxWidth) {
                    $size->setWidth($maxWidth);
                }

                return $size;
            } else {
                return false;
            }
        }
    }

    public function checkReturnVoucherRequested($bol_id) {
        $voucherRequested = false;

        try {
            $pickingExtendedInfo = $this->_speedyEPS->getPickingExtendedInfo((float)$bol_id);

            if (!is_null($pickingExtendedInfo->getReturnVoucher()) && ($pickingExtendedInfo->getReturnVoucher() instanceof ResultReturnVoucher)) {
                $voucherRequested = true;
            }
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }

        return $voucherRequested;
    }

    protected function _getReturnVoucherServiceTypeId($picking) {
        $services = array();
        $returnVoucherServiceTypeId = null;

        $sender = $picking->getSender();
        $receiver = $picking->getReceiver();

        try {
            if (Mage::getStoreConfig('carriers/speedyshippingmodule/bring_to_office') && Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office')) {
                $senderSiteId = null;
                $senderOfficeId = Mage::getStoreConfig('carriers/speedyshippingmodule/choose_office');
            } else {
                $resultClientData = $this->_speedyEPS->getClientById($sender->getClientId());
                $senderSiteId = $resultClientData->getAddress()->getSiteId();
                $senderOfficeId = null;
            }

            if ($receiver->getAddress()) {
                $receiverSiteId = $receiver->getAddress()->getSiteId();
                $receiverOfficeId = null;
            } else {
                $receiverSiteId = null;
                $receiverOfficeId = $picking->getOfficeToBeCalledId();
            }

            // Reverse sender and receiver data
            $listServices = $this->_speedyEPS->listServicesForSites(time(), $receiverSiteId, $senderSiteId, null, null, null, null, null, null, null, $receiverOfficeId, $senderOfficeId);

            foreach($listServices as $listService) {
                $services[] = $listService->getTypeId();
            }

            if (in_array(Mage::getStoreConfig('carriers/speedyshippingmodule/return_voucher_city_service_id'), $services)) {
                $returnVoucherServiceTypeId = Mage::getStoreConfig('carriers/speedyshippingmodule/return_voucher_city_service_id');
            } elseif (in_array(Mage::getStoreConfig('carriers/speedyshippingmodule/return_voucher_intercity_service_id'), $services)) {
                $returnVoucherServiceTypeId = Mage::getStoreConfig('carriers/speedyshippingmodule/return_voucher_intercity_service_id');
            }

        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }

        return $returnVoucherServiceTypeId;
    }

}

?>
