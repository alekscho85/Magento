<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Adminhtml_Sales_Order_View_Tab_Loading
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_loading;
    protected $_econt_order_id;
    protected $_econt_order;

    public function __construct()
    {
        parent::__construct();
        $this->setId('extensa_econt');
        $this->setUseAjax(true);
        if ($this->getEcontOrder()) {
            if ($this->getLoading()) {
                $this->setTemplate('extensa/econt/sales/order/view/tab/loading_view.phtml');
            } else {
                $this->setTemplate('extensa/econt/sales/order/view/tab/loading_generate.phtml');
            }
        }
    }

    /**
     * Retrieve tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('extensa_econt')->__('Товарителница');
    }

    /**
     * Retrieve tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('extensa_econt')->__('Еконт Експрес товарителница');
    }

    /**
     * Check whether can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getEcontOrder();
    }

    /**
     * Check whether tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getEcontOrder()
    {
        if (!$this->_econt_order) {
            $econtOrder = Mage::getModel('extensa_econt/order')->load($this->getOrder()->getId(), 'order_id');

            if ($econtOrder->getId()) {
                $this->_econt_order_id = $econtOrder->getId();
                $this->_econt_order = unserialize($econtOrder->getData('data'));
            }
        }

        return $this->_econt_order;
    }

    public function getLoading()
    {
        if (!$this->_loading) {
            $loading = Mage::getModel('extensa_econt/loading')->load($this->getOrder()->getId(), 'order_id');

            if ($loading->getId()) {
                $this->_loading = $loading->getData();
                $this->_loading['error'] = false;

                if ($this->_loading['cd_send_sum'] && (strtotime($this->_loading['cd_send_time']) > 0)) {
                    $this->_loading['trackings'] = Mage::getModel('extensa_econt/loadingtracking')
                        ->getCollection()
                        ->setEcontLoadingId($this->_loading['econt_loading_id'])
                        ->getData();

                    $this->_loading['next_parcels'] = Mage::getModel('extensa_econt/loading')
                        ->getCollection()
                        ->setPrevParcelNum($this->_loading['loading_num'])
                        ->getData();

                    foreach ($this->_loading['next_parcels'] as $key => $next_parcel) {
                        $this->_loading['next_parcels'][$key]['trackings'] = Mage::getModel('extensa_econt/loadingtracking')
                            ->getCollection()
                            ->setEcontLoadingId($next_parcel['econt_loading_id'])
                            ->getData();
                    }
                } else {
                    $data = array(
                        'type' => 'shipments',
                        'xml'  => "<shipments full_tracking='ON'><num>" . $this->_loading['loading_num'] . '</num></shipments>'
                    );

                    $results = Mage::helper('extensa_econt')->serviceTool($data);

                    $this->_loading['trackings'] = array();
                    $this->_loading['next_parcels'] = array();

                    if ($results) {
                        if (isset($results->shipments->e->error)) {
                            $this->_loading['error'] = true;
                            $this->_loading['message'] = (string)$results->shipments->e->error;
                            //Mage::getSingleton('core/session')->addError((string)$results->shipments->e->error);
                        } elseif (isset($results->error)) {
                            $this->_loading['error'] = true;
                            $this->_loading['message'] = (string)$results->error->message;
                            //Mage::getSingleton('core/session')->addError((string)$results->error->message);
                        } elseif (isset($results->shipments->e)) {
                            $this->_loading['is_imported'] = $results->shipments->e->is_imported;
                            $this->_loading['storage'] = $results->shipments->e->storage;
                            $this->_loading['receiver_person'] = $results->shipments->e->receiver_person;
                            $this->_loading['receiver_person_phone'] = $results->shipments->e->receiver_person_phone;
                            $this->_loading['receiver_courier'] = $results->shipments->e->receiver_courier;
                            $this->_loading['receiver_courier_phone'] = $results->shipments->e->receiver_courier_phone;
                            $this->_loading['receiver_time'] = $results->shipments->e->receiver_time;
                            $this->_loading['cd_get_sum'] = $results->shipments->e->CD_get_sum;
                            $this->_loading['cd_get_time'] = $results->shipments->e->CD_get_time;
                            $this->_loading['cd_send_sum'] = $results->shipments->e->CD_send_sum;
                            $this->_loading['cd_send_time'] = $results->shipments->e->CD_send_time;
                            $this->_loading['total_sum'] = $results->shipments->e->total_sum;
                            $this->_loading['currency'] = $results->shipments->e->currency;
                            $this->_loading['sender_ammount_due'] = $results->shipments->e->sender_ammount_due;
                            $this->_loading['receiver_ammount_due'] = $results->shipments->e->receiver_ammount_due;
                            $this->_loading['other_ammount_due'] = $results->shipments->e->other_ammount_due;
                            $this->_loading['delivery_attempt_count'] = $results->shipments->e->delivery_attempt_count;
                            $this->_loading['blank_yes'] = $results->shipments->e->blank_yes;
                            $this->_loading['blank_no'] = $results->shipments->e->blank_no;

                            if (isset($results->shipments->e->tracking)) {
                                foreach ($results->shipments->e->tracking->row as $tracking) {
                                    $this->_loading['trackings'][] = array(
                                        'time'       => $tracking->time,
                                        'is_receipt' => $tracking->is_receipt,
                                        'event'      => (string)$tracking->event,
                                        'name'       => $tracking->name,
                                        'name_en'    => $tracking->name_en
                                    );
                                }
                            }

                            if (isset($results->shipments->e->next_parcels)) {
                                foreach ($results->shipments->e->next_parcels->e as $next_parcel) {
                                    $data_next_parcel = array(
                                        'type' => 'shipments',
                                        'xml'  => "<shipments full_tracking='ON'><num>" . $next_parcel->num . '</num></shipments>'
                                    );

                                    $results_next_parcel = Mage::helper('extensa_econt')->serviceTool($data_next_parcel);

                                    if ($results_next_parcel) {
                                        if (isset($results_next_parcel->shipments->e->error)) {
                                            $this->_loading['error'] = true;
                                            $this->_loading['message'] = (string)$results_next_parcel->shipments->e->error;
                                            //Mage::getSingleton('core/session')->addError((string)$results_next_parcel->shipments->e->error);
                                        } elseif (isset($results_next_parcel->error)) {
                                            $this->_loading['error'] = true;
                                            $this->_loading['message'] = (string)$results_next_parcel->error->message;
                                            //Mage::getSingleton('core/session')->addError((string)$results_next_parcel->error->message);
                                        } elseif (isset($results_next_parcel->shipments->e)) {
                                            $trackings_next_parcel = array();

                                            if (isset($results_next_parcel->shipments->e->tracking)) {
                                                foreach ($results_next_parcel->shipments->e->tracking->row as $tracking) {
                                                    $trackings_next_parcel[] = array(
                                                        'time'       => $tracking->time,
                                                        'is_receipt' => $tracking->is_receipt,
                                                        'event'      => (string)$tracking->event,
                                                        'name'       => $tracking->name,
                                                        'name_en'    => $tracking->name_en
                                                    );
                                                }
                                            }

                                            $this->_loading['next_parcels'][] = array(
                                                'loading_num'            => $results_next_parcel->shipments->e->loading_num,
                                                'is_imported'            => $results_next_parcel->shipments->e->is_imported,
                                                'storage'                => $results_next_parcel->shipments->e->storage,
                                                'receiver_person'        => $results_next_parcel->shipments->e->receiver_person,
                                                'receiver_person_phone'  => $results_next_parcel->shipments->e->receiver_person_phone,
                                                'receiver_courier'       => $results_next_parcel->shipments->e->receiver_courier,
                                                'receiver_courier_phone' => $results_next_parcel->shipments->e->receiver_courier_phone,
                                                'receiver_time'          => $results_next_parcel->shipments->e->receiver_time,
                                                'cd_get_sum'             => $results_next_parcel->shipments->e->CD_get_sum,
                                                'cd_get_time'            => $results_next_parcel->shipments->e->CD_get_time,
                                                'cd_send_sum'            => $results_next_parcel->shipments->e->CD_send_sum,
                                                'cd_send_time'           => $results_next_parcel->shipments->e->CD_send_time,
                                                'total_sum'              => $results_next_parcel->shipments->e->total_sum,
                                                'currency'               => $results_next_parcel->shipments->e->currency,
                                                'sender_ammount_due'     => $results_next_parcel->shipments->e->sender_ammount_due,
                                                'receiver_ammount_due'   => $results_next_parcel->shipments->e->receiver_ammount_due,
                                                'other_ammount_due'      => $results_next_parcel->shipments->e->other_ammount_due,
                                                'delivery_attempt_count' => $results_next_parcel->shipments->e->delivery_attempt_count,
                                                'blank_yes'              => $results_next_parcel->shipments->e->blank_yes,
                                                'blank_no'               => $results_next_parcel->shipments->e->blank_no,
                                                'pdf_url'                => $next_parcel->pdf_url,
                                                'reason'                 => $next_parcel->reason,
                                                'trackings'              => $trackings_next_parcel
                                            );
                                        }
                                    } else {
                                        $this->_loading['error'] = true;
                                        $this->_loading['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
                                        //Mage::getSingleton('core/session')->addError(Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!'));
                                    }
                                }
                            }

                            if (!$this->_loading['error']) {
                                Mage::getModel('extensa_econt/loading')->setData($this->_loading)->save();

                                if (isset($this->_loading['trackings'])) {
                                    foreach ($this->_loading['trackings'] as $tracking) {
                                        $tracking['econt_loading_id'] = $this->_loading['econt_loading_id'];
                                        $tracking['loading_num'] = $this->_loading['loading_num'];
                                        Mage::getModel('extensa_econt/loadingtracking')->setData($tracking)->save();
                                    }
                                }

                                if (isset($this->_loading['next_parcels'])) {
                                    foreach ($this->_loading['next_parcels'] as $next_parcel) {
                                        $next_parcel['loading_num'] = $this->_loading['loading_num'];
                                        $loading_next_id = Mage::getModel('extensa_econt/loading')->setData($next_parcel)->save()->getId();

                                        if (isset($next_parcel['trackings'])) {
                                            foreach ($next_parcel['trackings'] as $tracking) {
                                                $tracking['econt_loading_id'] = $loading_next_id;
                                                $tracking['loading_num'] = $next_parcel['loading_num'];
                                                Mage::getModel('extensa_econt/loadingtracking')->setData($tracking)->save();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $this->_loading['error'] = true;
                        $this->_loading['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
                        //Mage::getSingleton('core/session')->addError(Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!'));
                    }
                }

                $this->_loading['receiver_time'] = (strtotime($this->_loading['receiver_time']) > 0 ? Mage::helper('core')->formatDate($this->_loading['receiver_time'], 'medium', true) : '');
                $this->_loading['cd_get_time'] = (strtotime($this->_loading['cd_get_time']) > 0 ? Mage::helper('core')->formatDate($this->_loading['cd_get_time'], 'medium', true) : '');
                $this->_loading['cd_send_time'] = (strtotime($this->_loading['cd_send_time']) > 0 ? Mage::helper('core')->formatDate($this->_loading['cd_send_time'], 'medium', true) : '');

                foreach ($this->_loading['trackings'] as $key => $tracking) {
                    $this->_loading['trackings'][$key] = array(
                        'time'       => Mage::helper('core')->formatDate($tracking['time'], 'medium', true),
                        'is_receipt' => ((int)$tracking['is_receipt'] ? Mage::helper('extensa_econt')->__('Yes') : Mage::helper('extensa_econt')->__('No')),
                        'event'      => Mage::helper('extensa_econt')->getTrackingEvent($tracking['event']),
                        'name'       => (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $tracking['name'] : $tracking['name_en'])
                    );
                }

                foreach ($this->_loading['next_parcels'] as $key => $next_parcel) {
                    $this->_loading['next_parcels'][$key]['receiver_time'] = (strtotime($next_parcel['receiver_time']) > 0 ? Mage::helper('core')->formatDate($next_parcel['receiver_time'], 'medium', true) : '');
                    $this->_loading['next_parcels'][$key]['cd_get_time'] = (strtotime($next_parcel['cd_get_time']) > 0 ? Mage::helper('core')->formatDate($next_parcel['cd_get_time'], 'medium', true) : '');
                    $this->_loading['next_parcels'][$key]['cd_send_time'] = (strtotime($next_parcel['cd_send_time']) > 0 ? Mage::helper('core')->formatDate($next_parcel['cd_send_time'], 'medium', true) : '');

                    foreach ($next_parcel['trackings'] as $key2 => $tracking) {
                        $this->_loading['next_parcels'][$key]['trackings'][$key2] = array(
                            'time'       => Mage::helper('core')->formatDate($tracking['time'], 'medium', true),
                            'is_receipt' => ((int)$tracking['is_receipt'] ? Mage::helper('extensa_econt')->__('Yes') : Mage::helper('extensa_econt')->__('No')),
                            'event'      => Mage::helper('extensa_econt')->getTrackingEvent($tracking['event']),
                            'name'       => (Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $tracking['name'] : $tracking['name_en'])
                        );
                    }
                }
            }
        }

        return $this->_loading;
    }

    public function getEcontOrderId()
    {
        return $this->_econt_order_id;
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/generateloading', array('_current' => true));
    }

    public function getShippingFrom()
    {
        return Mage::helper('extensa_econt')->getStoreConfig('shipping_from');
    }

    public function getShippingTo()
    {
        if (isset($this->_econt_order['shipping_to'])) {
            return $this->_econt_order['shipping_to'];
        } elseif ($this->_econt_order['loadings']['row']['receiver']['office_code']) {
            return 'OFFICE';
        } else {
            return 'DOOR';
        }
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
        if ($this->_econt_order['loadings']['row']['receiver']['office_code']) {
            return Mage::getModel('extensa_econt/office')->load($this->_econt_order['loadings']['row']['receiver']['office_code'], 'office_code')->setAps(0);
        }
        return false;
    }

    public function getOffices()
    {
        if ($this->getOffice() && $this->getOffice()->getCityId()) {
            return Mage::getModel('extensa_econt/office')->getCollection()->setCityId($this->getOffice()->getCityId())->setDeliveryType('to_office')->setAps(0);
        } else {
            return array();
        }
    }

    public function getOfficesAps()
    {
        if ($this->getOffice() && $this->getOffice()->getCityId()) {
            return Mage::getModel('extensa_econt/office')->getCollection()->setCityId($this->getOffice()->getCityId())->setDeliveryType('to_office')->setAps(1);
        } else {
            return array();
        }
    }

    public function getReceiverCity()
    {
        $city = Mage::getModel('extensa_econt/city')
                    ->getCollection()
                    ->setNameFilter($this->_econt_order['loadings']['row']['receiver']['city'], false)
                    ->setPostcodeFilter($this->_econt_order['loadings']['row']['receiver']['post_code'])
                    ->getFirstItem();
        if ($city->getId()) {
            $city->setName(Mage::helper('extensa_econt')->getLanguage() == 'bg_BG' ? $city->getName() : $city->getNameEn());
            return $city;
        } else {
            return false;
        }
    }

    public function getReceiverPostcode()
    {
        return $this->_econt_order['loadings']['row']['receiver']['post_code'];
    }

    public function getReceiverQuarter()
    {
        return $this->_econt_order['loadings']['row']['receiver']['quarter'];
    }

    public function getReceiverStreet()
    {
        return $this->_econt_order['loadings']['row']['receiver']['street'];
    }

    public function getReceiverStreetNum()
    {
        return $this->_econt_order['loadings']['row']['receiver']['street_num'];
    }

    public function getReceiverOther()
    {
        return $this->_econt_order['loadings']['row']['receiver']['street_other'];
    }

    public function getSenderAddresses()
    {
        $addresses = unserialize(Mage::helper('extensa_econt')->getStoreConfig('address'));

        foreach ($addresses as $address_id => $address) {
            $name = $address['post_code'] . ', ' . $address['city'];

            if ($address['quarter']) {
                $name .= ', ' . $address['quarter'];
            }

            if ($address['street']) {
                $name .= ', ' . $address['street'];

                if ($address['street_num']) {
                    $name .= ' ' . $address['street_num'];
                }
            }

            if ($address['other']) {
                $name .= ', ' . $address['other'];
            }

            $addresses[$address_id]['name'] = $name;
        }

        return $addresses;
    }

    public function getSmsNo()
    {
        return $this->_econt_order['loadings']['row']['receiver']['sms_no'];
    }

    public function getInvoiceBeforeCd()
    {
        return $this->_econt_order['loadings']['row']['shipment']['invoice_before_pay_CD'];
    }

    public function getDc()
    {
        return $this->_econt_order['loadings']['row']['services']['dc'];
    }

    public function getDcCp()
    {
        return $this->_econt_order['loadings']['row']['services']['dc_cp'];
    }

    public function getPayAfterAccept()
    {
        return $this->_econt_order['loadings']['row']['shipment']['pay_after_accept'];
    }

    public function getPayAfterTest()
    {
        return $this->_econt_order['loadings']['row']['shipment']['pay_after_test'];
    }

    public function getPriorityTime()
    {
        $addresses = unserialize(Mage::helper('extensa_econt')->getStoreConfig('address'));

        foreach ($addresses as $address_id => $address) {
            if ($address['post_code'] == $this->_econt_order['loadings']['row']['receiver']['post_code']) {
                return $address_id;
            }
        }

        return false;
    }

    public function getPriorityTimeCb()
    {
        return ($this->_econt_order['loadings']['row']['services']['p']['type'] && $this->_econt_order['loadings']['row']['services']['p']['value']);
    }

    public function getPriorityTimeTypeId()
    {
        if ($this->_econt_order['loadings']['row']['services']['p']['type']) {
            return $this->_econt_order['loadings']['row']['services']['p']['type'];
        } else {
            return 'BEFORE';
        }
    }

    public function getPriorityTimeHourId()
    {
        if ($this->_econt_order['loadings']['row']['services']['p']['value']) {
            return $this->_econt_order['loadings']['row']['services']['p']['value'];
        } else {
            return '';
        }
    }

    public function getExpressCityCourier()
    {
        $addresses = unserialize(Mage::helper('extensa_econt')->getStoreConfig('address'));

        foreach ($addresses as $address_id => $address) {
            if ($address['post_code'] == $this->_econt_order['loadings']['row']['receiver']['post_code']) {
                return $address_id;
            }
        }

        return false;
    }

    public function getExpressCityCourierCb()
    {
        return ($this->_econt_order['loadings']['row']['services']['e1'] || $this->_econt_order['loadings']['row']['services']['e2'] || $this->_econt_order['loadings']['row']['services']['e3']);
    }

    public function getExpressCityCourierE()
    {
        if ($this->_econt_order['loadings']['row']['services']['e1']) {
            return 'e1';
        } elseif ($this->_econt_order['loadings']['row']['services']['e2']) {
            return 'e2';
        } elseif ($this->_econt_order['loadings']['row']['services']['e3']) {
            return 'e3';
        } else {
            return 'e1';
        }
    }

    public function getDeliveryDay()
    {
        return $this->_econt_order['loadings']['row']['shipment']['delivery_day'];
    }

    public function getDeliveryDays()
    {
        $results_data = array();
        $results_data['priority_date'] = '';
        $results_data['delivery_days'] = array();
        $results_data['error'] = false;

        $results_data['delivery_day_id'] = $this->_econt_order['loadings']['row']['shipment']['delivery_day'];

        return Mage::helper('extensa_econt')->getDeliveryDays($results_data);
    }

    public function getPackCount()
    {
        return 1; //$this->_econt_order['loadings']['row']['shipment']['pack_count'];
    }

    public function getPartialDelivery()
    {
        return Mage::helper('extensa_econt')->getStoreConfigFlag('partial_delivery');
    }

    public function getPartialDeliveryInstruction()
    {
        return Mage::helper('extensa_econt')->getStoreConfig('partial_delivery_instruction');
    }

    public function getPartialDeliveryInstructions()
    {
        return Mage::getModel('extensa_econt/system_config_source_partialdeliveryinstruction')->toOptionArray();
    }

    public function getInventory()
    {
        return Mage::helper('extensa_econt')->getStoreConfigFlag('inventory');
    }

    public function getInventoryType()
    {
        return Mage::helper('extensa_econt')->getStoreConfig('inventory_type');
    }

    public function getInventoryTypes()
    {
        return Mage::getModel('extensa_econt/system_config_source_inventorytype')->toOptionArray();
    }

    public function getProducts()
    {
        $products = array();
        $productsWeight = array();
        $productsNoWeight = array();
        $allItems = $this->getOrder()->getAllItems();

        foreach ($allItems as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if ($product->getId()) {
                $productsWeight[$product->getId()] = (float)$product->getWeight();
            }
        }

        foreach ($allItems as $item) {
            $single = true;
            if (!$item->getParentItemId()) {
                foreach ($allItems as $item2) {
                    if ($item2->getParentItemId() == $item->getItemId()) {
                        $single = false;
                        break;
                    }
                }
            }

            if ($single) {
                for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                    $products[] = array(
                        'product_id' => $item->getProductId(),
                        'name'       => $item->getName(),
                        'weight'     => (isset($productsWeight[$item->getProductId()]) ? $productsWeight[$item->getProductId()] : 0),
                        'price'      => ($item->getPrice() == 0 && $item->getParentItemId() ? $item->getParentItem()->getPrice() : $item->getPrice()),
                    );
                }
                if (empty($productsWeight[$item->getProductId()])) {
                    $productsNoWeight[] = array(
                        'text' => $item->getName(),
                        'href' => $this->getUrl('*/catalog_product/edit', array('id' => $item->getProductId())),
                    );
                }
            }
        }

        return array('products' => $products, 'productsNoWeight' => $productsNoWeight);
    }

    public function getProductsCount()
    {
        $count = 0;
        $allItems = $this->getOrder()->getAllItems();

        foreach ($allItems as $item) {
            $count += $item->getQtyOrdered();
        }

        return $count;
    }

    public function getInstruction()
    {
        return Mage::helper('extensa_econt')->getStoreConfigFlag('instruction');
    }

    public function getInstructions()
    {
        return unserialize(Mage::helper('extensa_econt')->getStoreConfig('instructions'));
    }

    public function getInstructionsTypes()
    {
        return Mage::getModel('extensa_econt/system_config_source_instruction')->toOptionArray();
    }

    public function getGetInstructionUrl()
    {
        return $this->getUrl('*/getclient');
    }

    public function getGetAddressUrl()
    {
        return $this->getUrl('*/getaddress');
    }
}
