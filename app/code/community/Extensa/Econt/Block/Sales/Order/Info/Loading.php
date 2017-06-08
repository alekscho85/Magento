<?php
/**
 * @author Extensa Web Development Ltd. <support@extensadev.com>
 */
class Extensa_Econt_Block_Sales_Order_Info_Loading extends Mage_Core_Block_Template
{
    protected $_loading;

    /*
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('extensa/econt/sales/order/info/loading.phtml');
    }
    */
    
    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }
    
    public function getLoading()
    {
        if (!$this->_loading) {
            $loading = Mage::getModel('extensa_econt/loading')->load($this->getOrder()->getId(), 'order_id');

            if ($loading->getId()) {
                $this->_loading = $loading->getData();
                $this->_loading['error'] = false;

                if (!$this->_loading['is_returned']) {
                    if (strtotime($this->_loading['receiver_time']) <= 0) {
                        $data = array(
                            'type' => 'shipments',
                            'xml'  => "<shipments full_tracking='ON'><num>" . $this->_loading['loading_num'] . '</num></shipments>'
                        );

                        $results = Mage::helper('extensa_econt')->serviceTool($data);

                        if ($results) {
                            if (isset($results->shipments->e->error)) {
                                $this->_loading['error'] = true;
                                $this->_loading['message'] = (string)$results->shipments->e->error;
                                //Mage::getSingleton('customer/session')->addError((string)$results->shipments->e->error);
                            } elseif (isset($results->error)) {
                                $this->_loading['error'] = true;
                                $this->_loading['message'] = (string)$results->error->message;
                                //Mage::getSingleton('customer/session')->addError((string)$results->error->message);
                            } elseif (isset($results->shipments->e)) {
                                if ($results->shipments->e->CD_send_sum && (strtotime($results->shipments->e->CD_send_time) > 0)) {
                                    $this->_loading['trackings'] = array();
                                    $this->_loading['next_parcels'] = array();

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
                                                    //Mage::getSingleton('customer/session')->addError((string)$results_next_parcel->shipments->e->error);
                                                } elseif (isset($results_next_parcel->error)) {
                                                    $this->_loading['error'] = true;
                                                    $this->_loading['message'] = (string)$results_next_parcel->error->message;
                                                    //Mage::getSingleton('customer/session')->addError((string)$results_next_parcel->error->message);
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
                                                        'reason'                 => $next_parcel->reason,
                                                        'trackings'              => $trackings_next_parcel
                                                    );
                                                }
                                            } else {
                                                $this->_loading['error'] = true;
                                                $this->_loading['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
                                                //Mage::getSingleton('customer/session')->addError(Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!'));
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
                            }
                        } else {
                            $this->_loading['error'] = true;
                            $this->_loading['message'] = Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!');
                            //Mage::getSingleton('customer/session')->addError(Mage::helper('extensa_econt')->__('Възникна грешка при връзката с Еконт! Моля, опитайте отново!'));
                        }
                    }
                }
            }
        }

        return $this->_loading;
    }

    public function getReturnLoadingUrl()
    {
        return Mage::getUrl('extensa_econt/returnloading', array('order_id' => $this->getOrder()->getId(), '_secure' => true));
    }
}
