<?php
/**
 * Shipping method module adapter
 *
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Model_Shipping_Carrier_Econt extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * unique internal shipping method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'extensa_econt';
    protected $_code_office = 'econt_office';
    protected $_code_door = 'econt_door';
    protected $_code_aps = 'econt_aps';

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        // skip if not enabled
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /**
         * here we are retrieving shipping rates from external service
         * or using internal logic to calculate the rate from $request
         * you can see an example in Mage_Usa_Model_Shipping_Carrier_Ups::setRequest()
         */

        // get necessary configuration values
        ///$handling = $this->getConfigData('handling');

        // this object will be returned as result of this method
        // containing all the shipping rates of this method
        $result = Mage::getModel('shipping/rate_result');

        if ($this->getConfigData('to_office')) {
            $to_office = true;
        } else {
            $to_office = false;
        }

        if ($this->getConfigData('to_aps')) {
            $to_aps = true;
        } else {
            $to_aps = false;
        }

        if ($this->getConfigData('to_door') || (!$to_aps && !$to_office)) {
            $to_door = true;
        } else {
            $to_door = false;
        }

        if ($to_office) {
            // create new instance of method rate
            $method_office = Mage::getModel('shipping/rate_result_method');

            // record carrier information
            $method_office->setCarrier($this->_code);
            //$method_office->setCarrierTitle($this->getConfigData('title'));
            $method_office->setCarrierTitle(Mage::helper('extensa_econt')->__('Еконт Експрес'));

            // record method information
            $method_office->setMethod($this->_code_office);
            //$method_office->setMethodTitle($this->getConfigData('name_office'));
            $method_office->setMethodTitle(Mage::helper('extensa_econt')->__('Еконт Експрес - до офис'));

            // rate cost is optional property to record how much it costs to vendor to ship
            ///$method_office->setCost(0);

            $method_office->setPrice(0);

            // add this rate to the result
            $result->append($method_office);
        }
        if ($to_door) {
            // create new instance of method rate
            $method_door = Mage::getModel('shipping/rate_result_method');

            // record carrier information
            $method_door->setCarrier($this->_code);
            //$method_door->setCarrierTitle($this->getConfigData('title'));
            $method_door->setCarrierTitle(Mage::helper('extensa_econt')->__('Еконт Експрес'));

            // record method information
            $method_door->setMethod($this->_code_door);
            //$method_door->setMethodTitle($this->getConfigData('name_door'));
            $method_door->setMethodTitle(Mage::helper('extensa_econt')->__('Еконт Експрес - до врата'));

            // rate cost is optional property to record how much it costs to vendor to ship
            ///$method_door->setCost(0);

            $method_door->setPrice(0);

            // add this rate to the result
            $result->append($method_door);
        }
        if ($to_aps) {
            // create new instance of method rate
            $method_aps = Mage::getModel('shipping/rate_result_method');

            // record carrier information
            $method_aps->setCarrier($this->_code);
            //$method_aps->setCarrierTitle($this->getConfigData('title'));
            $method_aps->setCarrierTitle(Mage::helper('extensa_econt')->__('Еконт Експрес'));

            // record method information
            $method_aps->setMethod($this->_code_aps);
            //$method_aps->setMethodTitle($this->getConfigData('name_aps'));
            $method_aps->setMethodTitle(Mage::helper('extensa_econt')->__('Еконт Експрес - до АПС'));

            // rate cost is optional property to record how much it costs to vendor to ship
            ///$method_aps->setCost(0);

            $method_aps->setPrice(0);

            // add this rate to the result
            $result->append($method_aps);
        }

        $session = Mage::getSingleton('checkout/session')->getExtensaEcont();
        $session['error'] = array();
        $receiver_address = array();

        if (!empty($session['city_id']) || !empty($session['office_city_id']) || !empty($session['office_city_aps_id'])) {
            $receiver_address['post_code'] = $session['postcode'];
            $receiver_address['city'] = $session['city'];
            $receiver_address['city_id'] = $session['city_id'];
            $receiver_address['office_city_id'] = $session['office_city_id'];
            $receiver_address['office_id'] = $session['office_id'];
            $receiver_address['office_city_aps_id'] = $session['office_city_aps_id'];
            $receiver_address['office_aps_id'] = $session['office_aps_id'];
            $receiver_address['quarter'] = $session['quarter'];
            $receiver_address['street'] = $session['street'];
            $receiver_address['street_num'] = $session['street_num'];
            $receiver_address['other'] = $session['other'];
            $receiver_address['company'] = $session['company'];
            $receiver_address['shipping_to'] = $session['shipping_to'];
        } else {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $shipping_address = Mage::getModel('extensa_econt/customer')
                    ->load(Mage::getSingleton('customer/session')->getCustomer()->getId());
            }

            if (!empty($shipping_address) && $shipping_address->getId()) {
                $receiver_address['post_code'] = $shipping_address['postcode'];
                $receiver_address['city'] = $shipping_address['city'];
                $receiver_address['city_id'] = $shipping_address['city_id'];
                $receiver_address['office_id'] = $shipping_address['office_id'];
                $receiver_address['office_aps_id'] = $shipping_address['office_aps_id'];
                $receiver_address['quarter'] = $shipping_address['quarter'];
                $receiver_address['street'] = $shipping_address['street'];
                $receiver_address['street_num'] = $shipping_address['street_num'];
                $receiver_address['other'] = $shipping_address['other'];
                $receiver_address['company'] = $shipping_address['company'];
                $receiver_address['shipping_to'] = $shipping_address['shipping_to'];
            } else {
                //$shipping_address = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShippingAddress();
                $shipping_address = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();

                $receiver_address['post_code'] = $shipping_address->getPostcode();
                $receiver_address['city'] = $shipping_address->getCity();
                $receiver_address['company'] = $shipping_address->getCompany();
                $receiver_address['quarter'] = '';
                $receiver_address['street'] = '';
                $receiver_address['street_num'] = '';
                $receiver_address['other'] = '';
            }
        }

        if (empty($receiver_address['city_id'])) {
            if (!empty($receiver_address['office_city_id']) || !empty($receiver_address['office_id'])) {
                if (empty($receiver_address['office_city_id'])) {
                    $receiver_office = Mage::getModel('extensa_econt/office')->load($receiver_address['office_id']);
                    $receiver_address['office_city_id'] = $receiver_office->getCityId();
                }

                $city = Mage::getModel('extensa_econt/city')->load($receiver_address['office_city_id']);
                $receiver_address['post_code'] = $city->getPostCode();
                $receiver_address['city'] = (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $city->getName() : $city->getNameEn());
                $receiver_address['city_id'] = $city->getCityId();
            } elseif (!empty($receiver_address['office_city_aps_id']) || !empty($receiver_address['office_aps_id'])) {
                if (empty($receiver_address['office_city_aps_id'])) {
                    $receiver_office_aps = Mage::getModel('extensa_econt/office')->load($receiver_address['office_aps_id']);
                    $receiver_address['office_city_aps_id'] = $receiver_office_aps->getCityId();
                }

                $city_aps = Mage::getModel('extensa_econt/city')->load($receiver_address['office_city_aps_id']);
                $receiver_address['post_code'] = $city_aps->getPostCode();
                $receiver_address['city'] = (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $city_aps->getName() : $city_aps->getNameEn());
                $receiver_address['city_id'] = $city_aps->getCityId();
            } else {
                $cities = Mage::getModel('extensa_econt/city')->getCollection()->setNameFilter($receiver_address['city'], false);
                if (count($cities) > 1) {
                    foreach ($cities as $city) {
                        if (trim($city->getPostCode()) == trim($receiver_address['post_code'])) {
                            $receiver_address['post_code'] = $city->getPostCode();
                            $receiver_address['city_id'] = $city->getCityId();
                            break;
                        }
                    }
                } else {
                    $city = $cities->getFirstItem();
                    if ($city->getCityId()) {
                        $receiver_address['post_code'] = $city->getPostCode();
                        $receiver_address['city_id'] = $city->getCityId();
                    }
                }
            }
        }

        if (!empty($receiver_address['city_id'])) {
            $session['receiver_address'] = $receiver_address;

            $allItems = $request->getAllItems();
            $currencyRate = Mage::app()->getStore()->getBaseCurrency()->getRate(Mage::getModel('directory/currency')->load($this->getConfigData('currency')));
            $total = round(($request->getPackageValueWithDiscount() * $currencyRate), 2);
            $qty = $request->getPackageQty();

            $data = array();
            $data['system']['validate'] = 1;
            $data['system']['response_type'] = 'XML';
            $data['system']['only_calculate'] = 1;

            $data['client']['username'] = $this->getConfigData('username');
            $data['client']['password'] = $this->getConfigData('password');
            $data['client_software'] = 'ExtensaMagento';

            $row = array();
            $row2 = array();

            $sender_addresses = unserialize($this->getConfigData('address'));
            reset($sender_addresses);
            $sender_address = current($sender_addresses);

            $row['sender']['city'] = $sender_address['city'];
            $row['sender']['post_code'] = $sender_address['post_code'];

            $sender_office_code = '';

            if ($this->getConfigData('shipping_from') == 'OFFICE') {
                $sender_office_code = $this->getConfigData('office_code');
            } elseif ($this->getConfigData('shipping_from') == 'APS') {
                $sender_office_code = $this->getConfigData('office_aps_code');
            }

            $row['sender']['office_code'] = $sender_office_code;
            $row['sender']['name'] = $this->getConfigData('name_company');
            $row['sender']['name_person'] = $this->getConfigData('name_person');
            $row['sender']['quarter'] = $sender_address['quarter'];
            $row['sender']['street'] = $sender_address['street'];
            $row['sender']['street_num'] = $sender_address['street_num'];
            $row['sender']['street_bl'] = '';
            $row['sender']['street_vh'] = '';
            $row['sender']['street_et'] = '';
            $row['sender']['street_ap'] = '';
            $row['sender']['street_other'] = $sender_address['other'];
            $row['sender']['phone_num'] = $this->getConfigData('phone');

            $receiver_info = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
            $receiver_name_person = $receiver_info->getName();
            $receiver_email = $receiver_info->getEmail();
            $receiver_phone_num = $receiver_info->getTelephone();

            if (!empty($session['company'])) {
                $company = $session['company'];
            } else {
                $company = $receiver_name_person;
            }

            $row['receiver']['name'] = $company;
            $row['receiver']['name_person'] = $receiver_name_person;
            $row['receiver']['receiver_email'] = $receiver_email;
            $row['receiver']['street_bl'] = '';
            $row['receiver']['street_vh'] = '';
            $row['receiver']['street_et'] = '';
            $row['receiver']['street_ap'] = '';
            $row['receiver']['phone_num'] = $receiver_phone_num;

            if ($this->getStoreConfig('shipping_from') != 'APS' && $this->getConfigFlag('sms')) {
                $sms_no = $this->getConfigData('sms_no');
            } else {
                $sms_no = '';
            }

            $row['receiver']['sms_no'] = $sms_no;
            $row['shipment']['envelope_num'] = '';

            $weight = 0;
            $description = array();
            foreach ($allItems as $item) {
                if (!$item->getParentItemId()) {
                    $description[] = $item->getName();

                    $item_weight = (float)$item->getWeight();
                    if (!empty($item_weight)) {
                        $weight += ($item->getWeight() * $item->getQty());
                    } else {
                        $data['error']['weight'][$item->getProductId()] = array(
                            'name' => $item->getName(),
                            'qty'  => $item->getQty(),
                        );
                    }
                }
            }

            if (!empty($weight)) {
                $data['error']['no_weight'] = false;
            } else {
                $data['error']['no_weight'] = true;

                $weight = 1;
            }

            $row['shipment']['description'] = implode(', ', $description);
            $row['shipment']['pack_count'] = $qty;
            $row['shipment']['weight'] = $weight;

            $disposition = unserialize($this->getConfigData('disposition'));

            if ($this->getStoreConfig('shipping_from') != 'APS') {
                $invoice_before_cd = (int)$this->getConfigFlag('invoice_before_cd');
            } else {
                $invoice_before_cd = 0;
            }
            $row['shipment']['invoice_before_pay_CD'] = $invoice_before_cd;
            $row['shipment']['pay_after_accept'] = (!empty($disposition['pay_after_accept']) ? 1 : 0);
            $row['shipment']['pay_after_test'] = (!empty($disposition['pay_after_test']) ? 1 : 0);

            if ($this->getStoreConfig('shipping_from') != 'APS' && isset($session['delivery_day_id']) && $this->getConfigFlag('delivery_day')) {
                $delivery_day = $session['delivery_day_id'];
            } else {
                $delivery_day = '';
            }

            $row['shipment']['delivery_day'] = $delivery_day;

            $row['payment']['side'] = $this->getConfigData('side');
            $row['payment']['method'] = $this->getConfigData('payment_method');

            $receiver_share_sum_door = '';
            $receiver_share_sum_office = '';
            $receiver_share_sum_aps = '';

            if ((float)$this->getConfigData('total_for_free') && ($total >= $this->getConfigData('total_for_free')) || (float)$this->getConfigData('weight_for_free') && ($weight >= $this->getConfigData('weight_for_free')) || (int)$this->getConfigData('count_for_free') && ($qty >= $this->getConfigData('count_for_free'))) {
                $row['payment']['side'] = 'SENDER';
            } elseif ($this->getConfigData('shipping_payment')) {
                $shipping_payments = unserialize($this->getConfigData('shipping_payment'));
                $order_amount = 0;

                foreach ($shipping_payments as $shipping_payment) {
                    if ($total >= $shipping_payment['order_amount'] && $shipping_payment['order_amount'] >= $order_amount) {
                        $order_amount = $shipping_payment['order_amount'];
                        $receiver_share_sum_door = $shipping_payment['receiver_amount'];
                        $receiver_share_sum_office = $shipping_payment['receiver_amount_office'];
                        $$receiver_share_sum_aps = $shipping_payment['receiver_amount_office'];
                    }
                }
            }

            if ($row['payment']['method'] == 'CREDIT') {
                $key_word = $this->getConfigData('key_word');
            } else {
                $key_word = '';
            }

            $row['payment']['key_word'] = $key_word;

            $row['services']['e'] = '';

            if ($this->getConfigFlag('dc') && !$this->getConfigFlag('dc_cp')) {
                $dc = 'ON';
            } else {
                $dc = '';
            }

            $row['services']['dc'] = $dc;

            if ($this->getConfigFlag('dc_cp')) {
                $dc_cp = 'ON';
            } else {
                $dc_cp = '';
            }

            $row['services']['dc_cp'] = $dc_cp;
            $row['services']['dp'] = '';

            if ($this->getStoreConfig('shipping_from') != 'APS' && $this->getConfigFlag('oc') && ($total >= $this->getConfigData('total_for_oc'))) {
                $oc = $total;
                $oc_currency = $this->getConfigData('currency');
            } else {
                $oc = '';
                $oc_currency = '';
            }

            $row['services']['oc'] = $oc;
            $row['services']['oc_currency'] = $oc_currency;

            if ((!isset($session['cd_payment']) || $session['cd_payment']) && $this->getConfigFlag('cd') && Mage::getStoreConfigFlag('payment/extensa_econt/active')) {
                $cd_type = 'GET';
                $cd_value = $total;
                $cd_currency = $this->getConfigData('currency');

                if ($this->getConfigFlag('cd_agreement')) {
                    $cd_agreement_num = $this->getConfigData('cd_agreement_num');
                } else {
                    $cd_agreement_num = '';
                }
            } else {
                $cd_type = '';
                $cd_value = '';
                $cd_currency = '';
                $cd_agreement_num = '';
                $session['cd_payment'] = false;
            }

            $row['services']['cd'] = array('type' => $cd_type, 'value' => $cd_value);
            $row['services']['cd_currency'] = $cd_currency;
            $row['services']['cd_agreement_num'] = $cd_agreement_num;
            $row['services']['pack1'] = '';
            $row['services']['pack2'] = '';
            $row['services']['pack3'] = '';
            $row['services']['pack4'] = '';
            $row['services']['pack5'] = '';
            $row['services']['pack6'] = '';
            $row['services']['pack7'] = '';
            $row['services']['pack8'] = '';
            $row['services']['ref'] = '';

            $row3 = $row2 = $row;

            if ($to_office) {
                if ($receiver_share_sum_office) {
                    $row2['payment']['side'] = 'SENDER';
                }

                $row2['payment']['receiver_share_sum'] = $receiver_share_sum_office;
                $row2['payment']['share_percent'] = '';

                if ($row2['payment']['side'] == 'RECEIVER') {
                    $row2['payment']['method'] = 'CASH';
                    $row2['payment']['key_word'] = '';
                }

                $row2['receiver']['quarter'] = '';
                $row2['receiver']['street'] = '';
                $row2['receiver']['street_num'] = '';
                $row2['receiver']['street_other'] = '';

                if (!empty($receiver_address['office_id'])) {
                    $receiver_office = Mage::getModel('extensa_econt/office')->load($receiver_address['office_id']);
                    $row2['receiver']['office_code'] = $receiver_office->getOfficeCode();

                    $receiver_city = Mage::getModel('extensa_econt/city')->load($receiver_office->getCityId());
                    $row2['receiver']['city'] = (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $receiver_city->getName() : $receiver_city->getNameEn());
                    $row2['receiver']['post_code'] = $receiver_city->getPostCode();
                } elseif (!empty($receiver_address['office_city_id'])) {
                    $receiver_city = Mage::getModel('extensa_econt/city')->load($receiver_address['office_city_id']);
                    $row2['receiver']['city'] = (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $receiver_city->getName() : $receiver_city->getNameEn());
                    $row2['receiver']['post_code'] = $receiver_city->getPostCode();
                } else {
                    $offices = Mage::getModel('extensa_econt/office')->getCollection()->setCityId($receiver_address['city_id'])->setDeliveryType('to_office')->setAps(0);

                    if (count($offices)) {
                        $row2['receiver']['city'] = $receiver_address['city'];
                        $row2['receiver']['post_code'] = $receiver_address['post_code'];
                    } else {
                        $to_office = false;
                        $session['error']['office'] = true;
                    }
                }

                if ($this->getConfigData('shipping_from') != 'APS') {
                    $tariff_sub_code_preffix = $this->getConfigData('shipping_from');
                } else {
                    $tariff_sub_code_preffix = 'OFFICE';
                }

                $tariff_sub_code = $tariff_sub_code_preffix . '_OFFICE';

                $tariff_code = 0;

                if ($tariff_sub_code == 'OFFICE_OFFICE') {
                    $tariff_code = 2;
                } elseif ($tariff_sub_code == 'DOOR_OFFICE') {
                    $tariff_code = 3;
                }

                $row2['shipment']['tariff_code'] = $tariff_code;
                $row2['shipment']['tariff_sub_code'] = $tariff_sub_code;

                if ($weight >= 50) {
                    $row2['shipment']['shipment_type'] = 'CARGO';
                    $row2['shipment']['cargo_code'] = 81;
                } elseif ($weight <= 20 && $tariff_sub_code == 'OFFICE_OFFICE') {
                    $row2['shipment']['shipment_type'] = 'POST_PACK';
                } else {
                    $row2['shipment']['shipment_type'] = 'PACK';
                }

                $row2['services']['e1'] = '';
                $row2['services']['e2'] = '';
                $row2['services']['e3'] = '';

                $row2['services']['p'] = array('type' => '', 'value' => '');
            }

            if ($to_door) {
                if ($receiver_share_sum_door) {
                    $row['payment']['side'] = 'SENDER';
                }

                $row['payment']['receiver_share_sum'] = $receiver_share_sum_door;
                $row['payment']['share_percent'] = '';

                if ($row['payment']['side'] == 'RECEIVER') {
                    $row['payment']['method'] = 'CASH';
                    $row['payment']['key_word'] = '';
                }

                $row['receiver']['office_code'] = '';

                $row['receiver']['city'] = $receiver_address['city'];
                $row['receiver']['post_code'] = $receiver_address['post_code'];
                $row['receiver']['quarter'] = (isset($receiver_address['quarter']) ? $receiver_address['quarter'] : '');
                $row['receiver']['street'] = (isset($receiver_address['street']) ? $receiver_address['street'] : '');
                $row['receiver']['street_num'] = (isset($receiver_address['street_num']) ? $receiver_address['street_num'] : '');
                $row['receiver']['street_other'] = (isset($receiver_address['other']) ? $receiver_address['other'] : '');

                if ($this->getConfigData('shipping_from') != 'APS') {
                    $tariff_sub_code_preffix = $this->getConfigData('shipping_from');
                } else {
                    $tariff_sub_code_preffix = 'OFFICE';
                }

                $tariff_sub_code = $tariff_sub_code_preffix . '_DOOR';

                $tariff_code = 0;

                if (isset($session['express_city_courier_cb'])) {
                    $tariff_code = 1;
                } elseif ($tariff_sub_code == 'OFFICE_DOOR') {
                    $tariff_code = 3;
                } elseif ($tariff_sub_code == 'DOOR_DOOR') {
                    $tariff_code = 4;
                }

                $row['shipment']['tariff_code'] = $tariff_code;
                $row['shipment']['tariff_sub_code'] = $tariff_sub_code;

                if ($weight >= 50) {
                    $row['shipment']['shipment_type'] = 'CARGO';
                    $row['shipment']['cargo_code'] = 81;
                } elseif ($weight <= 20 && $tariff_sub_code == 'OFFICE_OFFICE') {
                    $row['shipment']['shipment_type'] = 'POST_PACK';
                } else {
                    $row['shipment']['shipment_type'] = 'PACK';
                }

                $city_courier_e1 = '';
                $city_courier_e2 = '';
                $city_courier_e3 = '';

                if (isset($session['express_city_courier_cb'])) {
                    if ($session['express_city_courier_e'] == 'e1') {
                        $city_courier_e1 = 'ON';
                    } elseif ($session['express_city_courier_e'] == 'e1') {
                        $city_courier_e2 = 'ON';
                    } elseif ($session['express_city_courier_e'] == 'e1') {
                        $city_courier_e3 = 'ON';
                    }
                }

                $row['services']['e1'] = $city_courier_e1;
                $row['services']['e2'] = $city_courier_e2;
                $row['services']['e3'] = $city_courier_e3;

                if (isset($session['priority_time_cb'])) {
                    $priority_time_type = $session['priority_time_type_id'];
                    $priority_time_value = $session['priority_time_hour_id'];
                } else {
                    $priority_time_type = '';
                    $priority_time_value = '';
                }

                $row['services']['p'] = array('type' => $priority_time_type, 'value' => $priority_time_value);
            }

            if ($to_aps) {
                if ($receiver_share_sum_aps) {
                    $row3['payment']['side'] = 'SENDER';
                }

                $row3['payment']['receiver_share_sum'] = $receiver_share_sum_aps;
                $row3['payment']['share_percent'] = '';

                if ($row3['payment']['side'] == 'RECEIVER') {
                    $row3['payment']['method'] = 'CASH';
                    $row3['payment']['key_word'] = '';
                }

                $row3['receiver']['quarter'] = '';
                $row3['receiver']['street'] = '';
                $row3['receiver']['street_num'] = '';
                $row3['receiver']['street_other'] = '';

                if (!empty($receiver_address['office_aps_id'])) {
                    $receiver_office_aps = Mage::getModel('extensa_econt/office')->load($receiver_address['office_aps_id']);
                    $row3['receiver']['office_code'] = $receiver_office_aps->getOfficeCode();

                    $receiver_city_aps = Mage::getModel('extensa_econt/city')->load($receiver_office_aps->getCityId());
                    $row3['receiver']['city'] = (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $receiver_city_aps->getName() : $receiver_city_aps->getNameEn());
                    $row3['receiver']['post_code'] = $receiver_city_aps->getPostCode();
                } elseif (!empty($receiver_address['office_city_aps_id'])) {
                    $receiver_city_aps = Mage::getModel('extensa_econt/city')->load($receiver_address['office_city_aps_id']);
                    $row3['receiver']['city'] = (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $receiver_city_aps->getName() : $receiver_city_aps->getNameEn());
                    $row3['receiver']['post_code'] = $receiver_city_aps->getPostCode();
                } else {
                    $offices_aps = Mage::getModel('extensa_econt/office')->getCollection()->setCityId($receiver_address['city_id'])->setDeliveryType('to_office')->setAps(1);

                    if (count($offices_aps)) {
                        $row3['receiver']['city'] = $receiver_address['city'];
                        $row3['receiver']['post_code'] = $receiver_address['post_code'];
                    } else {
                        $to_aps = false;
                        $session['error']['aps'] = true;
                    }
                }

                if ($this->getConfigData('shipping_from') != 'APS') {
                    $tariff_sub_code_preffix = $this->getConfigData('shipping_from');
                } else {
                    $tariff_sub_code_preffix = 'OFFICE';
                }

                $tariff_sub_code = $tariff_sub_code_preffix . '_OFFICE';

                $tariff_code = 0;

                if ($tariff_sub_code == 'OFFICE_OFFICE') {
                    $tariff_code = 2;
                } elseif ($tariff_sub_code == 'DOOR_OFFICE') {
                    $tariff_code = 3;
                }

                $row3['shipment']['tariff_code'] = $tariff_code;
                $row3['shipment']['tariff_sub_code'] = $tariff_sub_code;

                if ($weight >= 50) {
                    $row3['shipment']['shipment_type'] = 'CARGO';
                    $row3['shipment']['cargo_code'] = 81;
                } elseif ($weight <= 20 && $tariff_sub_code == 'OFFICE_OFFICE') {
                    $row3['shipment']['shipment_type'] = 'POST_PACK';
                } else {
                    $row3['shipment']['shipment_type'] = 'PACK';
                }

                if ($weight < 5) {
                    $row3['shipment']['aps_box_size'] = 'Small';
                } else if ($weight < 10) {
                    $row3['shipment']['aps_box_size'] = 'Medium';
                } else {
                    $row3['shipment']['aps_box_size'] = 'Large';
                }

                $row3['services']['e1'] = '';
                $row3['services']['e2'] = '';
                $row3['services']['e3'] = '';

                $row3['services']['p'] = array('type' => '', 'value' => '');

                $row3['services']['e'] = '';
                $row3['services']['dc'] = '';
                $row3['services']['dc_cp'] = '';
                $row3['services']['dp'] = '';
                $row3['services']['oc'] = '';
                $row3['services']['oc_currency'] = '';

                $row3['shipment']['invoice_before_pay_CD'] = 0;
                $row3['shipment']['pay_after_accept'] = 0;
                $row3['shipment']['pay_after_test'] = 0;
            }

            $rows_array = array();

            if ($to_door) {
                $data['loadings']['to_door']['row'] = $row;
                $rows_array[] = 'door';
            }
            if ($to_office) {
                $data['loadings']['to_office']['row'] = $row2;
                $rows_array[] = 'office';
            }
            if ($to_aps) {
                $data['loadings']['to_aps']['row'] = $row3;
                $rows_array[] = 'aps';
            }

            $results = Mage::helper('extensa_econt')->parcelImport($data);

            $key = 0;

            if ($results && !empty($results->result->e)) {
                foreach ($results->result->e as $e) {
                    if (!empty($e->error)) {
                        if (isset($rows_array[$key])) {
                            $session['error'][$rows_array[$key]] = true;
                        }
                        $session['error']['message'] = (string)$e->error;
                    } elseif (isset($e->loading_price->total)) {
                        $receiver_share_sum = 0;
                        if (isset($rows_array[$key]) && isset(${'receiver_share_sum_'.$rows_array[$key]})) {
                            $receiver_share_sum = ${'receiver_share_sum_'.$rows_array[$key]};
                        }

                        $data['error']['fixed'] = false;

                        if ((float)$this->getConfigData('total_for_free') && ($total >= $this->getConfigData('total_for_free')) || (float)$this->getConfigData('weight_for_free') && ($weight >= $this->getConfigData('weight_for_free')) || (int)$this->getConfigData('count_for_free') && ($qty >= $this->getConfigData('count_for_free')) || !$receiver_share_sum && $this->getConfigData('side') == 'SENDER') {
                            $data['error']['fixed'] = true;
                        } elseif (isset($data['error']['weight'])) {
                            $session['error']['fixed'] = true;
                            $session['error']['message'] = Mage::helper('extensa_econt')->__('Поръчката се обработва');
                        } elseif (!empty($receiver_share_sum)) {
                            $data['error']['fixed'] = true;

                            if (isset($rows_array[$key]) && isset(${'method_'.$rows_array[$key]})) {
                                ${'method_'.$rows_array[$key]}->setPrice((float)$receiver_share_sum / $currencyRate);
                            }
                        } else {
                            if (isset($rows_array[$key]) && isset(${'method_'.$rows_array[$key]})) {
                                ${'method_'.$rows_array[$key]}->setPrice((float)$e->loading_price->total / $currencyRate);
                            }
                        }
                    }

                    $key++;
                }
            } else {
                $session['error']['general'] = true;
                $session['error']['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
            }
        } else {
            $session['error']['general'] = true;
            //$session['error']['message'] = Mage::helper('extensa_econt')->__('Моля, въведете коректно всички данни!');
        }

        if (empty($session['error']['general'])) {
            $session['econt_order_id'] = Mage::getModel('extensa_econt/order')
                ->addData(array('data' => serialize($data)))
                ->save()
                ->getId();
        }

        Mage::getSingleton('checkout/session')->setExtensaEcont($session);

        return $result;
    }

    /**
     * This method is used when viewing / listing Shipping Methods with Codes programmatically
     */
    public function getAllowedMethods()
    {
        return array($this->_code_office => $this->getConfigData('name_office'), $this->_code_door => $this->getConfigData('name_door'), $this->_code_aps => $this->getConfigData('name_aps'));
    }
}
